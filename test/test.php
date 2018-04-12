<?php
use liuguang\mvc\Application;
define('PUBLIC_PATH', __DIR__);
include PUBLIC_PATH . '/../vendor/autoload.php';
define('APP_CONTEXT', '');
$app = new Application();
$app->startTest();