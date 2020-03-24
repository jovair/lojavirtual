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

$app->run();

 ?>