<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

// Carrega o template de categorias
$app->get("/admin/categories", function() {

	User::verifyLogin();

	$categories = Category::listAll();
	
	$page = new PageAdmin();

	$page->setTpl("categories", [
		'categories'=>$categories
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

// carrega o template da categoria de produtos escolhida pelo cliente
$app->get("/categories/:idcategory", function($idcategory) {

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>[]
	]);
		
});