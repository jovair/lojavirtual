<?php 

// INICIANDO O AMBIENTE DE ROTAS

// inicialização da sessão. (FUTURO: verificar se a seção existe antes de iniciar)
session_start();

// requerimento do autoload, que vai controlar todas as aplicações proprietárias e de terceiros do sistema
require_once("vendor/autoload.php");

// todas as classes usadas pela aplicação está aqui
use Slim\Slim;

// esta rota  carrega o slim que é responsável pela criação de rotas dentro do sistema
$app = new \Slim\Slim();

$app->config('debug', true);

require_once("functions.php");
require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");
require_once("admin-orders.php");

$app->run();

 ?>
