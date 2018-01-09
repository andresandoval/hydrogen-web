<?php
header("Access-Control-Allow-Origin: *");
require_once "../vendor/autoload.php";

setcookie("XDEBUG_SESSION", "PHPSTORM", 0, "/");

use Hydrogen\Web\Tests;

$controllers = [
    Tests\Controllers\MainController::class
];

$app = new Tests\Main($controllers);
$app->run();