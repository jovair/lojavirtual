<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;


// Carrega o template de categorias
$app->get("/admin/categories", function(){

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {

		$pagination = Category::getPageSearch($search, $page);

	} else {

		$pagination = Category::getPage($page);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{

		array_push($pages, [
			'href'=>'/admin/categories?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("categories", [
		"categories"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	]);	


});

// carrega o template para cadastrar a categoria
$app->get("/admin/categories/create", function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");

});

// envia os dados para cadastrar a categoria
$app->post("/admin/categories/create", function() {

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;
	
});

// apaga a categoria identificada
$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();
	
	$category = new Category();
	
	$category->get((int)$idcategory);
	
	$category->delete();
	
	header('Location: /admin/categories');
	exit;
	
});

// carrega uma categoria existente para alteração
$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();
	
	$category = new Category();
	
	$category->get((int)$idcategory);
	
	$page = new PageAdmin();
	
	$page->setTpl("categories-update", [
		'category'=>$category->getValues()
	]);
		
});

// envia a alteração da categoria para o BD.
$app->post("/admin/categories/:idcategory", function($idcategory){
	
	User::verifyLogin();
	
	$category = new Category();
	
	$category->get((int)$idcategory);
	
	$category->setData($_POST);
	
	$category->save();
	
	header('Location: /admin/categories');
	exit;

});

// carrega os produtos que estão e que não estão relacionados a uma categoria
$app->get("/admin/categories/:idcategory/products", function($idcategory){
	
	User::verifyLogin();
	
	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-products", [
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
	]);
	
});

// adiciona um produto a uma categoria
$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){
	
	User::verifyLogin();
	
	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/" . $idcategory . "/products");
	exit;
	
});

// remove um produto a uma categoria
$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){
	
	User::verifyLogin();
	
	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/" . $idcategory . "/products");
	exit;
	
});
