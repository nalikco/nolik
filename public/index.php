<?php
use App\App;

const ROOT = __DIR__ . "/../";

session_start();
require "../vendor/autoload.php";
require "../src/helpers.php";

$app = new App();
$app->serve();