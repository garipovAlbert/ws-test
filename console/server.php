<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/Server.php';



$app = new Server('websocket://0.0.0.0:8000');
$app->run();