<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

// essa rota lista todos os usuários cadastrados na tela de Usuários 
$app->get("/admin/users", function() {

	// para acessar essa tela, o usuário precisa estar logado; por padrão o inadimin é true, se o usuário estiver logado,
	// terá acesso à tela
	User::verifyLogin();
	
	// chama o método que vai trazer os dados do banco e armazena na variável $users

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {

		$pagination = User::getPageSearch($search, $page);

	} else {

		$pagination = User::getPage($page);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{

		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
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

