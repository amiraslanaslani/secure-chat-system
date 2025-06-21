<?php
use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Aslan\Chat\Constants;
use Aslan\Chat\DB;

require_once __DIR__ . '/../vendor/autoload.php';

class ChatIntegrationTest extends TestCase {
    private $app;

    protected function setUp(): void {
        // Set up Slim app as in apis/index.php
        $this->app = AppFactory::create();
        $this->app->setBasePath(Constants::API_PREFIX_PATH);
        $this->app->addRoutingMiddleware();
        $this->app->addErrorMiddleware(true, true, true);
        $this->app->get('chat/read', "\\Aslan\\Chat\\Chat:read");
        $this->app->post('chat/send', "\\Aslan\\Chat\\Chat:send");
        DB::init();
    }

    public function testReadEndpointReturns200() {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/chat/apis/chat/read');
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testSendEndpointValidatesInput() {
        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(http_build_query(['name' => '', 'message' => '']));
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/chat/apis/chat/send')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($body);
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('error', (string)$response->getBody());
    }

    public function testReadReturnsJsonResponse() {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/chat/apis/chat/read');
        $response = $this->app->handle($request);
        $body = (string)$response->getBody();
        $this->assertJson($body);
    }

    public function testSendReturnsJsonResponse() {
        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(http_build_query(['name' => 'TestUser', 'message' => 'Hello', 'channel' => 'testchan']));
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/chat/apis/chat/send')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($body);
        $response = $this->app->handle($request);
        $body = (string)$response->getBody();
        $this->assertJson($body);
    }
} 