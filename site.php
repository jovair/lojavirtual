<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;

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
		'products'=>$cart->getProducts()
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