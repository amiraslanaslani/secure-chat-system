<?php
namespace Aslan\Chat;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Aslan\Chat\Constants as Constants;
use Aslan\Chat\DB as DB;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->setBasePath(Constants::API_PREFIX_PATH);

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('chat/read', "\Aslan\Chat\Chat:read");
$app->post('chat/send', "\Aslan\Chat\Chat:send");

$app->run();
