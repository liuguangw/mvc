<?php
use liuguang\mvc\Application;
define('PUBLIC_PATH', __DIR__);
include PUBLIC_PATH . '/../vendor/autoload.php';
$app = new Application();
$app->startTest();