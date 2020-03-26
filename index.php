<?php
/**
|--------------------------------------------------------------------------
| AutoLoader
|--------------------------------------------------------------------------
 */
$autoLoader = require_once (__DIR__.'/vendor/autoload.php');
/**
 * Create Instance of APP MANAGER
 */
$PHP7API_Manager = new \PHP7API\App\Manager();
/**
 * Start the app
 */
$PHP7API_Manager->start();


