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

class Category extends Model {

    // este método carrega os dados de usuários para alimentar a tela de categorias no template administrativo
    public static function listAll()
    {

        $sql = new Sql();

        // carrega os dados da categoria ordenados pelo nome
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
    }

    public function save()
    {

        $sql = new Sql();

        // os dados que são passados dentros dos parênteses são feitos de modo seguro, impedindo uma ação de injection
        // primeiro os dados são inseridos na tabela, em seguida é feito o select, devolvendo para a aplicação
        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory(),

        ));

        $this->setData($results[0]);

        Category::updateFile();

    }

    // busca uma categoria específica no BD
    public function get($idcategory)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
            ':idcategory'=>$idcategory
        ]);

        $this->setData($results[0]);

    }

    // exclui uma categoria no BD.
    public function delete()
    {
        
        $sql = new Sql();

        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
            ':idcategory'=>$this->getidcategory()
        ]);

        Category::updateFile();

    }

    // atualiza as categorinas no arquivo categories-menu.html. Ela é usada todas as vezes que uma categoria é criada, modificada ou excluída.
    public static function updateFile()
    {

        $categories = Category::listAll();

        $html = [];

        foreach ($categories as $row) {
            array_push($html, '<li><a href="/categories/' . $row['idcategory'] . '">' . $row['descategory'] . '</a></li>');

        }

        // cria o caminho físico do arquivo dinamicamente
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
    }

    // carrega todos os produtos para o template de produtos na lista de categorias
    public function getProducts($related = true)
    {

        $sql = new Sql();

        if ($related === true) {

            return $sql->select("SELECT * FROM tb_products WHERE idproduct IN(
                SELECT a.idproduct
                FROM tb_products a
                INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                WHERE b.idcategory = :idcategory
            );", [
                ':idcategory'=>$this->getidcategory()
            ]);
        } else {
            
            return $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN(
                    SELECT a.idproduct
                    FROM tb_products a
                    INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                    WHERE b.idcategory = :idcategory
                );
            ", [
                ':idcategory'=>$this->getidcategory()
            ]);
        }
    }

    // faz a paginação dos produtos de acordo com a quantidade definida em $itemsPerPage
    public function getProductsPage($page = 1, $itemsPerPage = 3)
    {
        // começa com o registro 0 do BD
        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select
        ("SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_products a
            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
            INNER JOIN tb_categories c ON c.idcategory = b.idcategory
            WHERE c.idcategory = :idcategory
            LIMIT $start, $itemsPerPage;
        ", [

            ':idcategory'=>$this->getidcategory()

        ]);

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            'data'=>Product::checkList($results),
            'total'=>(int)$resultTotal[0]["nrtotal"],
            'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage) //ceil arredonda para cima
        ];

    }

    // adiciona um produto à uma categoria
    public function addProduct(Product $product)
    {

        $sql = new Sql();

        $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) 
            VALUES(:idcategory, :idproduct", [
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);

    }

    // adiciona um produto à uma categoria
    public function removeProduct(Product $product)
    {

        $sql = new Sql();

        $sql->query("DELETE FROM tb_productscategories 
            WHERE idcategory = :idcategory 
            AND idproduct = :idproduct", [
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);

    }

	public static function getPage($page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select(
            "SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories 
			ORDER BY descategory
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
			FROM tb_categories 
			WHERE descategory LIKE :search
			ORDER BY descategory
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