<?php

use \Hcode\Page;
use \Hcode\Model\Product;

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
