<?php

error_reporting(E_ERROR | E_PARSE);

require __DIR__ . '/vendor/autoload.php';

use App\PrivateMessage;

$app = new Ratchet\App('localhost', 8080);
$app->route('/pm', new PrivateMessage('pm'), ['*']);
$app->route('/echo', new Ratchet\Server\EchoServer, ['*']);
$app->run();
