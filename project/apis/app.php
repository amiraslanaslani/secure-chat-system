<?php
namespace Aslan\Chat;

use Slim\App as SlimApp;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Aslan\Chat\Config as Config;
use Aslan\Chat\DB as DB;

class App{
    public $app = null;

    public function __construct() {
        $this->app = AppFactory::create();

        $this->app->setBasePath(Config::getApiPrefixPath());
        $this->app->addRoutingMiddleware();
        $errorMiddleware = $this->app->addErrorMiddleware(true, true, true);
        
        $this->app->get('chat/read', \Aslan\Chat\Chat::class . ':read');
        $this->app->post('chat/send', \Aslan\Chat\Chat::class . ':send');
    }

    public function run() {
        $this->app->run();
    }
}
