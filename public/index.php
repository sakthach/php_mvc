<?php 

require __DIR__. './../vendor/autoload.php';
use Framework\Router;
use Framework\Session;
Session::start();



require '../helplers.php';
// First create a router object
$router = new Router();

// Second Register route
$routes = require basePath('routes.php');

// Third : Get url
$uri =  parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)  ;


//
$router->route($uri);


