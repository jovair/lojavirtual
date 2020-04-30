<?php

// aqui é informado o namespace do caminho onde está a classe Category
namespace Hcode\Model;

// para executar esta classe, precisamos dos dados que estão no Banco de Dados e para acessar
// o banco, precisamos da classe Sql; a barra antes do Hcode é importante
use \Hcode\DB\Sql;

// a classe User é uma extensão da classe Model e para isso precisamos dessa classe
use \Hcode\Model;

// a classe Mailer é necessária para verificar se o usuário está logado
use \Hcode\Mailer;

class Product extends Model {

    // este método carrega os dados de usuários para alimentar a tela de categorias no template administrativo
    public static function listAll()
    {

        $sql = new Sql();

        // carrega os dados da categoria ordenados pelo nome
        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
    }

    // este método foi criado por conta da alteração do projeto, que não busca as imagens no BD. 
    public function checkList($list)
    {
        // o & permite manipular a variável na memória (verificar aula sobre o assunto)
        foreach ($list as &$row) {
            
            $p = new Product();
            $p->setData($row);
            $row = $p->getValues();
        }

        return $list;
    }

    
    public function save()
    {

        $sql = new Sql();

        // os dados que são passados dentros dos parênteses são feitos de modo seguro, impedindo uma ação de injection
        // primeiro os dados são inseridos na tabela, em seguida é feito o select, devolvendo para a aplicação
        $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct"=>$this->getidproduct(),
            ":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllength"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl()

        ));

        $this->setData($results[0]);

    }

    // busca uma categoria específica no BD
    public function get($idproduct)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct'=>$idproduct
        ]);

        $this->setData($results[0]);

    }

    // exclui uma categoria no BD.
    public function delete()
    {
        
        $sql = new Sql();

        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct'=>$this->getidproduct()
        ]);

    }

    public function checkPhoto()
    {

    if(file_exists(
    $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
    "res"  . DIRECTORY_SEPARATOR .
    "site" . DIRECTORY_SEPARATOR .
    "img" . DIRECTORY_SEPARATOR .
    "products" . DIRECTORY_SEPARATOR .
    $this->getidproduct() . ".jpg"
    )) {

        $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";

    } else {

        $url = "/res/site/img/product.jpg";

    }

    return $this->setdesphoto("$url");

    }

    public function getValues()
    {
        $this->checkPhoto();

        $values = parent::getValues();

        return $values;
    }

    // este método foi criado para atender à alteração do projeto por falta do campo de imagem no BD
    public function setPhoto($file)
    {
        
        $extension = explode('.', $file['name']);

        $extension = end($extension);

        switch ($extension) {

            case "jpg":
            case "jpeg":
                $image = imagecreatefromjpeg($file["tmp_name"]);
            break;
            
            case "gif":
                $image = imagecreatefromgif($file["tmp_name"]);
            break;
            
            case "png":
                $image = imagecreatefrompng($file["tmp_name"]);
            break;
        }

        $dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
            "res"  . DIRECTORY_SEPARATOR .
            "site" . DIRECTORY_SEPARATOR .
            "img" . DIRECTORY_SEPARATOR .
            "products" . DIRECTORY_SEPARATOR .
            $this->getidproduct() . ".jpg";

        imagejpeg($image, $dist);

        imagedestroy($image);

        $this->checkPhoto();

    }

    public function getFromURL($desurl)
    {

        $sql = new Sql();

        $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [
            'desurl'=>$desurl
        ]);

        $this->setData($rows[0]);
    }

    public function getCategories()
    {

        $sql = new Sql();

        return $sql->select
        ("SELECT * FROM 
        tb_categories a 
            INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory 
            WHERE b.idproduct = :idproduct
        ", [
            ':idproduct'=>$this->getidproduct()
        ]);

    }

	public static function getPage($page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select(
            "SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products 
			ORDER BY desproduct
			LIMIT $start, $itemsPerPage;
		");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}

	public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select(
            "SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products 
			WHERE desproduct LIKE :search || vlprice LIKE :search
			ORDER BY desproduct
			LIMIT $start, $itemsPerPage;
		", [
			':search'=>'%'.$search.'%'
		]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}

}