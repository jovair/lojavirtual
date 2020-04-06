<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

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

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>Product::checkList($category->getProducts())
	]);
	
});