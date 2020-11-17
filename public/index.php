<?php
session_start();

/**
 * Front controller
 *
 * PHP version 7.0
 */

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('login', ['controller' => 'Login', 'action' => 'new']);
$router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);
$router->add('dodaj-przychód', ['controller' => 'Income', 'action' => 'new']);
$router->add('dodaj-wydatek', ['controller' => 'Expense', 'action' => 'new']);
$router->add('przeglądaj-bilans', ['controller' => 'BrowseStatement', 'action' => 'show']);
$router->add('{controller}/{action}');
$router->add('{controller}/{action}/{token:[\da-f]+}');
    
$router->dispatch($_SERVER['QUERY_STRING']);
