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

$app->get('chat/read', function ($request, $response, $args) {
    $db = new \Aslan\Chat\DB();
    $chat = new \Aslan\Chat\Chat($db);
    $params = $request->getQueryParams();
    $from = isset($params['from']) ? intval($params['from']) : 0;
    $channel = isset($params['channel']) ? trim($params['channel']) : Config::getChatDefaultChannel();
    $messages = $chat->get_msgs($channel, $from);
    $response->getBody()->write(json_encode($messages));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('chat/send', function ($request, $response, $args) {
    $db = new \Aslan\Chat\DB();
    $chat = new \Aslan\Chat\Chat($db);
    // Accept JSON payloads
    $contentType = $request->getHeaderLine('Content-Type');
    if (strpos($contentType, 'application/json') !== false) {
        $post_data = json_decode($request->getBody()->getContents(), true);
    } else {
        $post_data = $request->getParsedBody();
    }
    $name = isset($post_data['name']) ? trim($post_data['name']) : '';
    $message = isset($post_data['message']) ? trim($post_data['message']) : '';
    $channel = isset($post_data['channel']) ? trim($post_data['channel']) : Config::getChatDefaultChannel();
    if ($name === '' || $message === '') {
        $response->getBody()->write(json_encode(['error' => 'Name and message are required.']));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
    $result = $chat->send_msg($name, $message, $channel);
    if ($result) {
        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(['error' => 'Database error.']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->run();
