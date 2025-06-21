<?php
namespace Aslan\Chat;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Aslan\Chat\Config as Config;
use Aslan\Chat\DB as DB;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->setBasePath(Config::getApiPrefixPath());

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('chat/read', \Aslan\Chat\Chat::class . ':read');
$app->post('chat/send', \Aslan\Chat\Chat::class . ':send');

$app->run();
