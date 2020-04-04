<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

// carrega o template products 
$app->get("/admin/products", function(){

    User::verifyLogin();

    // lista os produtos cadastrados no BD
    $products = Product::listAll();

    $page = new PageAdmin();

    $page->setTpl("products", [
        'products'=>$products
    ]);
    
});

// carrega o template para cadastro de produtos
$app->get("/admin/products/create", function(){

    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("products-create");

});

// cadastra produtos no BD
$app->post("/admin/products/create", function(){

    User::verifyLogin();

    $product = new Product();

    $product->setData($_POST);

    $product->save();

    header("Location: /admin/products");
    
    exit;

});

// carrega o template para alterar dados de produtos
$app->get("/admin/products/:idproduct", function($idproduct){

    User::verifyLogin();

    $product = new Product();

    $product->get((int)$idproduct);

    $page = new PageAdmin();

    $page->setTpl("products-update", [
        'product'=>$product->getValues()
    ]);

});

// altera produtos no BD.
$app->post("/admin/products/:idproduct", function($idproduct){

    User::verifyLogin();

    $product = new Product();

    $product->get((int)$idproduct);

    $product->setData($_POST);

    $product->save();

    // método criado unicamente para atender à mudança estrutural por não possuir o campo de imagens no BD
    $product->setPhoto($_FILES["file"]);

    header("Location: /admin/products");
    
    exit;

});


// deleta um produto
$app->get("/admin/products/:idproduct/delete", function($idproduct){

    User::verifyLogin();

    $product = new Product();

    $product->get((int)$idproduct);

    $product->delete();

    header("Location: /admin/products");

    exit;

});