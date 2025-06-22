<?php
namespace Aslan\Chat;

use Slim\App as SlimApp;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Aslan\Chat\Config as Config;
use Aslan\Chat\DB as DB;
use Aslan\Chat\Chat as Chat;

class App{
    public $app = null;

    public function __construct(DB $db) {
        $this->app = AppFactory::create();

        $this->app->setBasePath(Config::getApiPrefixPath());
        $this->app->addRoutingMiddleware();
        $errorMiddleware = $this->app->addErrorMiddleware(true, true, true);

        $chat = new Chat($db);
        $this->app->get('chat/read', [$chat, 'read']);
        $this->app->post('chat/send', [$chat, 'send']);
    }

    public function run() {
        $this->app->run();
    }
}
