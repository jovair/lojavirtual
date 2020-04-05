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


}