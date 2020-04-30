<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

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

	if (isset($_GET['zipcode'])) {

		$_GET['zipcode'] = $cart->getdeszipcode();

	}
	
	if (isset($_GET['zipcode'])) {

		$address->loadFromCEP($_GET['zipcode']);
		
		$cart->setdeszipcode($_GET['zipcode']);

		$cart->save();

		$cart->getCalculateTotal();

	}

	if(!$address->getdesaddress()) $address->setdesaddress('');
	if(!$address->getdesnumber()) $address->setdesnumber('');
	if(!$address->getdescomplement()) $address->setdescomplement('');
	if(!$address->getdesdistrict()) $address->setdesdistrict('');
	if(!$address->getdescity()) $address->setdescity('');
	if(!$address->getdesstate()) $address->setdesstate('');
	if(!$address->getdescountry()) $address->setdescountry('');
	if(!$address->getdeszipcode()) $address->setdeszipcode('');

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Address::getMsgError()
	]);
});

$app->post("/checkout", function(){

	User::verifylogin(false);

	if(!isset($_POST['zipcode']) || $_POST['zipcode'] === '') {
		Address::setMsgError("Informe o CEP.");
		header("Location: /checkout");
		exit;

	}

	if(!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {
		Address::setMsgError("Informe o endereço.");
		header("Location: /checkout");
		exit;

	}

	if(!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') {
		Address::setMsgError("Informe o bairro.");
		header("Location: /checkout");
		exit;

	}

	if(!isset($_POST['descity']) || $_POST['descity'] === '') {
		Address::setMsgError("Informe a cidade.");
		header("Location: /checkout");
		exit;

	}

	if(!isset($_POST['desstate']) || $_POST['desstate'] === '') {
		Address::setMsgError("Informe o estado.");
		header("Location: /checkout");
		exit;

	}

	if(!isset($_POST['descountry']) || $_POST['descountry'] === '') {
		Address::setMsgError("Informe o país.");
		header("Location: /checkout");
		exit;

	}

	$user = User::getFromSession();

	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];

	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	$cart = Cart::getFromSession();

	$cart->getCalculateTotal();

	$order = new Order();

	$order->setData([
		'idcart'=>$cart->getidcart(),
		'idaddress'=>$address->getidaddress(),
		'iduser'=>$user->getiduser(),
		'idstatus'=>OrderStatus::EM_ABERTO,
		'vltotal'=>$cart->getvltotal()
	]);

	$order->save();

	header("Location: /order/".$order->getidorder());
	
	exit;

});

// verifica se o login é válido
$app->get("/login", function(){

	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
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

	header("Location: /checkout");

	exit;
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

$app->get("/order/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$page = new Page();

	$page->setTpl("payment", [
		'order'=>$order->getValues()
	]);
});

$app->get("/boleto/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(",", "",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress() . " " . $order->getdesdistrict();
	$dadosboleto["endereco2"] = $order->getdecity() . " - " . $order->getdesstate() . " - " .  $order->getdescountry() . " - " . "CEP: " .  $order->getdeszipcode();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "Hcode Treinamentos";
	$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
	$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
	$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
	$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

	// NÃO ALTERAR!
	$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;
	require_once($path . "funcoes_itau.php");
	require_once($path . "layout_itau.php");

});

$app->get("/profile/orders", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile-orders", [
		'orders'=>$user->getOrders()
	]);
});

$app->get("/profile/orders/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart = new Cart();

	$cart->get((int)$order->getidcart());

	$cart->getCalculateTotal();

	$page = new Page();

	$page->setTpl("profile-orders-detail", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);

});

$app->get("/profile/change-password", function(){

	User::verifyLogin(false);

	$page = new Page();

	$page->setTpl("profile-change-password", [
		'changePassError'=>User::getError(),
		'changePassSuccess'=>User::getSuccess()
	]);

});

$app->post("/profile/change-password", function(){

	User::verifyLogin(false);

	if (!isset($_POST['current_pass']) || $_POST['current_pass'] === '') {
		
		User::setError("Digite a senha atual.");
		
		header("location: /profile/change-password");
		
		exit;
	}

	if (!isset($_POST['new_pass']) || $_POST['new_pass'] === '') {
		
		User::setError("Digite a nova senha.");
		
		header("location: /profile/change-password");
		
		exit;
	}

	if (!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] === '') {
		
		User::setError("Confirme a nova senha.");
		
		header("location: /profile/change-password");
		
		exit;
	}

	if ($_POST['current_pass'] === $_POST['new_pass']) {
		
		User::setError("A sua nova senha deve ser diferente da atual.");
		
		header("location: /profile/change-password");
		
		exit;
	}

	$user = User::getFromSession();

	if (!password_verify($_POST['current_pass'], $user->getdespassword())) {
				
		User::setError("A sua senha está inválida.");
		
		header("location: /profile/change-password");
		
		exit;
	}

	$user->setdespassword($_POST['new_pass']);

	$user->update();

	$user::setSuccess("Senha alterada com sucesso.");
		
	header("location: /profile/change-password");
		
	exit;
});