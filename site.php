<?php

use \Hcode\Page;

// esta rota carrega a página principal do site
$app->get('/', function() {
	
	$page = new Page();

	$page->setTpl("index");

});
