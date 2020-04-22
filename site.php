<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

// esta rota carrega a página principal do site
$app->get('/', function() {

	// carrega os produtos do BD
	$products = Product::listAll();
	
	$page = new Page();

	// chama o método estático checkList na classe Product
	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]);

});

// carrega o template da categoria de produtos escolhida pelo cliente
$app->get("/categories/:idcategory", function($idcategory) {

	// verifica se o usuário está clicando um uma página específica, se não, carrega a pag. 1
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	// carrega os produtos da categoria escolhida pelo usuário
	$category->get((int)$idcategory);

	// recebe o número da página escolhida pelo usuário
	$pagination = $category->getProductsPage($page);

	// carrega os botões com a lista de páginas
	$pages = [];

	// carrega todos os itens da página escolhida pelo usuário
	for ($i=1; $i <= $pagination['pages']; $i++) {

		array_push($pages, [
			'link'=>'/categories/' . $category->getidcategory() . '?page=' . $i,
			'page'=>$i
		]);
	}

	$page = new Page();

	// exibe os itens dentro do template
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination["data"],
		'pages'=>$pages
	]);
	
});

// carrega os produtos por categoria
$app->get("/products/:desurl", function($desurl){

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);

});

// carrega o template do carrnho de compras
$app->get("/cart", function(){

	$cart = Cart::getFromSession();

	$page = new page();

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);

});

// adiciona um produto do carrinho
$app->get("/cart/:idproduct/add", function($idproduct) {

	$product = new Product();
	
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	// verifica quantos produtos foram selecionados em detalhes do produto e transfere para o carrinho
	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i = 0; $i < $qtd; $i++) {
		
		$cart->addProduct($product);
	}

	// $cart->addProduct($product);

	header("Location: /cart");
	
	exit;
});

// remove um produto do carrinho
$app->get("/cart/:idproduct/minus", function($idproduct) {

	$product = new Product();
	
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	
	exit;
});

// remove todos os produtos do carrinho
$app->get("/cart/:idproduct/remove", function($idproduct) {

	$product = new Product();
	
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	
	exit;

});

// envia os dados do carrinho, já com o frete, para o BD.
$app->post("/cart/freight", function() {

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");

	exit;
});

// faz toda a checagem dos dados do cliente para validação do login
$app->get("/checkout", function(){

	User::verifyLogin(false);

	$address = new Address();
	$cart = Cart::getFromSession();

	// if (!isset($_GET['zipcode'])) {

	// 	$_GET('zipcode') = $cart->getdeszipcode();

	// }

	// if (isset($_GET['zipcode'])) {

	// 	$address-loadFromCEP()
	// }

	// $page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);
});

// verifica se o login é válido
$app->get("/login", function(){

	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getError(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);
});

// envia o login e senha para o BD
$app->post("/login", function() {

	try {
		
		User::login($_POST['login'], $_POST['password']);

	} catch(Exception $e) {

		User::setError($e->getMessage());

	}

	header("Location: /checkout");
	
	exit;

});

// faz o logout da área do usuário
$app->get("/logout", function() {

	User::logout();

	header("Location: /login");
	
	exit;
});

// envia os dados do cadastro para o BD.
$app->post("/register", function(){

	$_SESSION['registerValues'] = $_POST;

	if (!isset($_POST['name']) || $_POST['name'] == '') {

		User::setErrorRegister("Preencha o seu nome.");

		header("Location: /login");

		exit;

	}

	if (!isset($_POST['email']) || $_POST['email'] == '') {

		User::setErrorRegister("Preencha o seu e-mail.");

		header("Location: /login");

		exit;

	}

	if (!isset($_POST['password']) || $_POST['password'] == '') {

		User::setErrorRegister("Preencha a senha.");

		header("Location: /login");

		exit;

	}

	if (User::checkLoginExist($_POST['email']) === true) {

		User::setErrorRegister("Este endereço de e-mail já está cadastrado.");

		header("Location: /login");

		exit;

	}

	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header('Location: /checkout');
});

// carrega o template para a entrada do e-mail a ser recuperado
$app->get("/forgot", function() {

	$page = new Page();

	$page->setTpl("forgot");
	
});

// envia o e-mail digitado no template de recuperação da senha
$app->post("/forgot", function(){

	// passa o e-mail para o método getForgot
	$user = User::getForgot($_POST["email"], false);

	// informa o usuário que o e-mail foi enviado com sucesso
	header("Location: /forgot/sent");
	
	exit;

});

// carrega o template de mensagem enviada
$app->get("/forgot/sent", function(){

	$page = new Page();

	$page->setTpl("forgot-sent");

});

// valida o código do usuário
$app->get("/forgot/reset", function(){

	// primeira verificação de segurança do código
	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
	
});

// envia a nova senha do usuário para alteração
$app->post("/forgot/reset", function(){

	// segunda verificação para ter certeza que não houve alguma brecha de segurança
	$forgot = User::validForgotDecrypt($_POST["code"]);

	// passa a senha para o método
	User::setForgotUsed($forgot["idrecovery"]);

	// instancia o objeto
	$user = new User();

	// recebe a senha bruta do banco
	$user->get((int)$forgot["iduser"]);

	// envia o hash da senha com a API password_hash
	$password = User::getPasswordHash($_POST["password"]);

	// passa a senha para o método
	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-success");

});

$app->get("/profile", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);

});

$app->post("/profile", function(){

	User::verifyLogin(false);

	if (!isset($_POST['desperson']) || $_POST['desperson'] === '') {
		
		User::setError("Preencha o seu nome.");

		header("Lcation: /profile");

		exit;
	}

	if (!isset($_POST['desemail']) || $_POST['desemail'] === '') {
		
		User::setError("Preencha o seu e-mail.");
		
		header("Lcation: /profile");

		exit;
	}

	$error = User::getFromSession();

	if ($_POST['desemail'] !== $user->getdesemail()) {

		if (User::checkLoginExist($_POST['desemail']) === true) {

			User::setError("Este endereço de e-mail já está cadastrado.");
			
			header("Lcation: /profile");

			exit;

		}

	}

	$_POST['inadmin'] = $user->getinadmin();

	$_POST['despassword'] = $user->getdespassword();

	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->update();

	User::setSuccesses("Dados alterados com sucesso");

	header("Location: /profile");
	exit;

});