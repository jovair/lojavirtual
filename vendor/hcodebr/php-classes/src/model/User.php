<?php

// aqui é informado o namespace do caminho onde está a classe user
namespace Hcode\Model;

// para executar esta classe, precisamos dos dados que estão no Banco de Dados e para acessar
// o banco, precisamos da classe Sql; a barra antes do Hcode é importante
use \Hcode\DB\Sql;

// a classe User é uma extensão da classe Model e para isso precisamos dessa classe
use \Hcode\Model;

// aqui é criada a classe User, que extende da classe Model
class User extends Model {

    // nome da seção que vai identificar o usuário logado
    const SESSION = "User";

    // o login e senha deste método vem da tela de login
    public static function login($login, $password) {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));

        if (count($results) === 0){

            // esta Exception é a classe do namespace do PHP; neste sistema não foi criada 
            // nenhuma estrutura de erros, portanto, \Exception faz com que a mensagem de
            // erro do PHP seja exibida, casno não seja encontrado o login
            throw new \Exception("Usuario inexistente ou senha inválida.");
        }

        $data = $results[0];

        // a função password_verify recebe a senha que recebeu da tela de login e também a que recebeu
        // do banco de dados e faz uma comparação para saber se são idênticas
        if (password_verify($password, $data["despassword"]) === true) {

            $user = new User();

            // invoca o método setData da classe pai
            $user->setData($data);

            // recebe os dados do usuário que é retornado da classe pai e fica à disposição para ser 
            // usado com o return. O objeto user não pode ser acessado diretamente porque é privado
            $_SESSION[User::SESSION] = $user->getValues();

            return $user;

        }else {

            throw new \Exception("Usuario inexistente ou senha inválida.");
        }
    }

    // este método valida o login do usuário
    public static function verifyLogin($inadmin = true)
    {

        // se a seção não existir dentro das condições abaixo, o usuário e direcionado para a tela de login
        if (
            // a seção não existe
            !isset($_SESSION[User::SESSION])
            ||
            // a seção é falsa (expirada, por exemplo)
            !$_SESSION[User::SESSION]
            ||
            // se o id do usuário não existe. Se o resultado estiver vazio, não é um usuário
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
            ||
            // se o usuário não está logado na administração
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
        ) {

            // só é direcionado para a tela de login se não se enquadrou em nenhuma condição acima
            header("Location: /admin/login");

            exit;

        }

    }

    // este método faz o logout do usuário
    public static function logout()
    {

        $_SESSION[User::SESSION] = NULL;

    }
}