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

    // este método carrega os dados de usuários para alimentar a tela de usuários no template administrativo
    public static function listAll()
    {

        $sql = new Sql();

        // os dados das duas tabelas são concatenados pelo campo idperson da tabela tb_persons e ordenado pelo nome do usuário
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }

    public function save()
    {

        $sql = new Sql();

        // os dados que são passados dentros dos parênteses são feitos de modo seguro, impedindo uma ação de injection
        // primeiro os dados são inseridos na tabela, em seguida é feito o select, devolvendo para a aplicação
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()

        ));

        $this->setData($results[0]);

    }

    public function get($iduser)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$iduser
        ));

        $this->setData($results[0]);
        
    }
    
    public function update(){
        
        $sql = new Sql();
        
        // idêntico ao método save, exceto que aqui precisa do id do usuário, porque lá ele é gerado automaticamente e aqui está apenas atualizando os dados
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
            
        ));
        
        $this->setData($results[0]);

    }

    // exclui um usuário
    public function delete(Type $var = null)
    {
        $sql = new Sql();

        // chama a procedure para exclusão do usuário e da pessoa
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }

}