<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

// ==============================RECUPERAÇÃO DE SENHA====================================

// carrega o template para a entrada do e-mail a ser recuperado
$app->get("/admin/forgot", function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	
	]);

	$page->setTpl("forgot");
	
});

// envia o e-mail digitado no template de recuperação da senha
$app->post("/admin/forgot", function(){

	// passa o e-mail para o método getForgot
	$user = User::getForgot($_POST["email"]);

	// informa o usuário que o e-mail foi enviado com sucesso
	header("Location: /admin/forgot/sent");
	
	exit;

});

// carrega o template de mensagem enviada
$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	
	]);

	$page->setTpl("forgot-sent");

});

// valida o código do usuário
$app->get("/admin/forgot/reset", function(){

	// primeira verificação de segurança do código
	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
	
});

// envia a nova senha do usuário para alteração
$app->post("/admin/forgot/reset", function(){

	// segunda verificação para ter certeza que não houve alguma brecha de segurança
	$forgot = User::validForgotDecrypt($_POST["code"]);

	// passa a senha para o método
	User::setForgotUsed($forgot["idrecovery"]);

	// instancia o objeto
	$user = new User();

	// recebe a senha bruta do banco
	$user->get((int)$forgot["iduser"]);

	// envia o hash da senha com a API password_hash
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12 //custo de processamento para gerar a senha
	]);

	// passa a senha para o método
	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	
	]);

	$page->setTpl("forgot-reset-success");

});