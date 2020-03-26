<?php 

// inicialização da sessão. (FUTURO: verificar se a seção existe antes de iniciar)
session_start();

// requerimento do autoload, que vai controlar todas as aplicações proprietárias e de terceiros do sistema
require_once("vendor/autoload.php");

// todas as classes usadas pela aplicação está aqui
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model;

// o slim é responsável pela criação de rotas dentro do sistema
$app = new \Slim\Slim();

$app->config('debug', true);

// esta rota carrega a página principal do site
$app->get('/', function() {
	
	$page = new Page();

	$page->setTpl("index");

});

// essa rota carrega a página de administração do sistema
$app->get('/admin', function() {

	// verifica/valida se o usuário está logado na classe User
	User::verifyLogin();
	
	$page = new PageAdmin();

	$page->setTpl("index");

});

// essa rota carrega a tela de login no sistema
$app->get('/admin/login', function() {

	// A classe PageAdmin é uma herança da classe Page, cujos valores de header e footer são true. Assim que a PageAdmin 
	// é executada para mesclar os dados, esses valores são alterados para false e a página de administração é carregada
	// ao invés da página do site
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	// carrega a tela de usuários, sem o cabeçalho e rodapé; apenas a tela com as opções de login e senha
	$page->setTpl("login");

});

// Esta rota envia a senha e o login para a classe User, que vai passar os dados para o banco de dados fazer a validação.
$app->post('/admin/login', function() {

	// a classe User está lá na pasta de classes, dentro da pasta Model; o método login vai receber o login e senha recebidos
	// da tela de login HTML
	User::login($_POST["login"], $_POST["password"]);

	// se os dados de login e senha estiverem corretos, o header.html da página de administração é invocado e a tela é aberta
	header("Location: /admin");

	exit;

});

// essa rota faz o logout do sistema pelo método logout que está na classe User
$app->get('/admin/logout', function()
{

	User::logout();

	// ao sair, o usuário é redirecionado para a tela de login
	header("location: /admin/login");
	exit;

});

// essa rota lista todos os usuários cadastrados na tela de Usuários 
$app->get("/admin/users", function() {

	// para acessar essa tela, o usuário precisa estar logado; por padrão o inadimin é true, se o usuário estiver logado,
	// terá acesso à tela
	User::verifyLogin();
	
	// chama o método que vai trazer os dados do banco e armazena na variável $users
	$users = User::listAll();

	// cria o objeto PageAdmin
	$page = new PageAdmin();

	// carrega a tela de usuários com a lista de todos os usuários cadastrados
	$page->setTpl("users", array(
		"users"=>$users
	));

});

// Essa rota acessa a tela para a criação de usuários
$app->get("/admin/users/create", function() {

	// para acessar essa tela, o usuário precisa estar logado; por padrão o inadimin, no método verifyLogin é true, 
	// se o usuário estiver logado no ambiente de administração, terá acesso à tela
	User::verifyLogin();
	
	$page = new PageAdmin();

	// carrega o template para a criação de usuários
	$page->setTpl("users-create");

});

// Essa rota exclui os dados de usuários no Banco de dados.
// IMPORTANTE: esta rota precisa ficar acima da rota /admin/users/:iduser, caso contrário, ao chegar nela, a execução seria encerrada
$app->get("/admin/users/:iduser/delete", function($iduser){

	User::verifyLogin();

	// cria o objeto $user()
	$user = new User();

	// carrega os dados do usuário
	$user->get((int)$iduser);
	
	// invoca o método delete
	$user->delete();

	// carrega a tela de usuários
	header("location: /admin/users");
	
	exit;


	
});

// essa rota atualiza os dados de um usuário. O valor a ser recebido pela função é passado para :iduser
$app->get("/admin/users/:iduser", function($iduser) {

	// para acessar essa tela, o usuário precisa estar logado; por padrão o inadimin é true, se o usuário estiver logado,
	// terá acesso à tela
	User::verifyLogin();

	// cria o objeto $user
	$user = new User();

	// pega o id do usuário; o int é para receber o id como número
	$user->get((int)$iduser);
	
	// cria o objeto PageAdmin()
	$page = new PageAdmin();

	// carrega a tela com os dados do usuário para que sejam feitas as alterações
	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});

// Essa rota insere os dados de usuários no Banco de dados
$app->post("/admin/users/create", function(){

	User::verifyLogin();

	$user = new User();

	// verifica se o usuário tem acesso como administrador. Esse valor vem do box html
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	// recebe os dados informados para cadastro via $_POST e armazena em $user
	$user->setData($_POST);

	// invoca o método save() que envia os dados para o banco
	$user->save();

	// carrega a tela de usuários 
	header("Location: /admin/users");
	
	exit;

});

// Essa rota insere os dados de usuários no Banco de dados
$app->post("/admin/users/:iduser", function($iduser){

	// verifica se o usuário está logado
	User::verifyLogin();

	// cria o objeto Usere()
	$user = new User();

	// verifica se o usuário é um administrador e armazena no método
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	// carrega os dados atuais do usuário
	$user->get((int)$iduser);

	// passa os dados via $_POST()
	$user->setData($_POST);

	// insere os dados no banco com o método update
	$user->update();

	// carrega a tela de usuários
	header("Location: /admin/users");
	
	exit;

});

$app->run();

 ?>