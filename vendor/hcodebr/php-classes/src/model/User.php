<?php

// aqui é informado o namespace do caminho onde está a classe user
namespace Hcode\Model;

// para executar esta classe, precisamos dos dados que estão no Banco de Dados e para acessar
// o banco, precisamos da classe Sql; a barra antes do Hcode é importante
use \Hcode\DB\Sql;

// a classe User é uma extensão da classe Model e para isso precisamos dessa classe
use \Hcode\Model;

use \Hcode\Mailer;

// aqui é criada a classe User, que extende da classe Model
class User extends Model {

    // nome da seção que vai identificar o usuário logado
    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret"; //constante para encriptar os dados do usuário
    const SECRET_IV = "HcodePhp7_Secret_IV";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
    const SUCCESS = "UserSucesss";
    
    // verifica se existe uma sessão e retorna o seu id, caso exista
    public static function getFromSession()
    {

        $user = new User();

        if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0)
        {

            $user->setData($_SESSION[User::SESSION]);

        }

        return $user;

    }

    // verifica se o usuário está logado, se é ou não administrador, permitindo acesso apenas onde ele tem autorização
    public static function checkLogin($inadmin = true)
    {
        if (
            // a seção não existe
            !isset($_SESSION[User::SESSION])
            ||
            // a seção é falsa (expirada, por exemplo)
            !$_SESSION[User::SESSION]
            ||
            // se o id do usuário não existe. Se o resultado estiver vazio, não é um usuário
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
        ) {

            // não está logado
            return false;

        } else {

            // verifica se é administrador e se está logado
            if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {

                return true;

            //não é administrador, mas verifica se está logado
            } else if ($inadmin === false) {

                return true;

            } else {

                return false;
            }
        }
    }

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

            $data['desperson'] = utf8_encode($data['desperson']);

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

        // se inadmin for true é direcionado para o login de administração, se false, é direcionado para o login de compras
        if (User::checkLogin($inadmin)) {

            if ($inadmin) {
                
                header("Location: /admin/login");

            } else {

                header("Location: /login");
            
            }

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
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>User::getPasswordHash($this->getdespassword()),
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

        $data['desperson'] = utf8_encode($data['desperson']);
        
    }
    
    public function update(){
        
        $sql = new Sql();
        
        // idêntico ao método save, exceto que aqui precisa do id do usuário, porque lá ele é gerado automaticamente e aqui está apenas atualizando os dados
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>User::getPasswordHash($this->getdespassword()),
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

    // envia o e-mail recebido do usuário para o BD 
    public static function getForgot($email, $inadmin = true)
    {

        $sql = new Sql();

        // traz os dadod do BD
        $results = $sql->select("SELECT * 
        FROM tb_persons a 
        INNER JOIN tb_users b 
        USING(idperson)
        WHERE a.desemail = :email", array(
            ":email"=>$email
        ));

        // se o e-mail não for encontrado retorna uma mensagem de erro
        if (count($results) === 0)
        {

            throw new \Exception("Não foi possível recuperar a senha");

        }
        else
        {

            $data = $results[0];

            // executa a procedure no BD, que processa as informações e devolve
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"] // recebe o ip remoto
            ));

            if (count($results2) === 0)
            {

                throw new \Exception("Não foi possível recuperar a senha");

            }
            else
            {

                // os dados estão em sua forma bruta
                $dataRecovery = $results2[0];

                // os dados são encriptados. O método de encriptação foi alterado da aula original para outro atualizado
                $code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

                // os dados são colocados na base64
                $code = base64_encode($code);

				if ($inadmin === true) {

					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

				} else {

					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
					
				}				

				$mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Hcode Store", "forgot", array(
					"name"=>$data['desperson'],
					"link"=>$link
				));				

				$mailer->send();

				return $link;

			}

        }

    }

    // recebe o código encriptado para validação
    public static function validForgotDecrypt($code)
	{

        // decripta o código recebido
        $code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

        // verifica no BD se o uso do código de validação está sendo usado no prazo máximo de 1 hora
        $sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
			":idrecovery"=>$idrecovery
		));

		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{

			return $results[0];

		}

    }
    	
    // verifica se o código de validação da nova senha já foi usado
    public static function setForgotUsed($idrecovery)
	{

        // atualiza o código de recuperação, para que ele não seja usado novamente no futuro
        $sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));

	}

    // altera a senha do usuário
    public function setPassword($password)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));

	}

	public static function setError($msg)
	{

		$_SESSION[User::ERROR] = $msg;

	}

	public static function getError()
	{

		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

		User::clearError();

		return $msg;

	}

	public static function clearError()
	{

		$_SESSION[User::ERROR] = NULL;

	}

	public static function setSuccess($msg)
	{

		$_SESSION[User::SUCCESS] = $msg;

	}

	public static function getSuccess()
	{

		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

		User::clearSuccess();

		return $msg;

	}

	public static function clearSuccess()
	{

		$_SESSION[User::SUCCESS] = NULL;

	}

	public static function setErrorRegister($msg)
	{

		$_SESSION[User::ERROR_REGISTER] = $msg;

	}

	public static function getErrorRegister()
	{

		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

		User::clearErrorRegister();

		return $msg;

	}

	public static function clearErrorRegister()
	{

		$_SESSION[User::ERROR_REGISTER] = NULL;

	}

	public static function checkLoginExist($login)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
		]);

		return (count($results) > 0);

	}

	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);

	}

    

}