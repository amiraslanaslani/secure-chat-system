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
    const TEST_USER = 'TestUser';
    const TEST_MESSAGE = 'Hello';
    const TEST_CHANNEL = 'testchan';

    /**
     * Set up Slim app and database for each test.
     */
    protected function setUp(): void {
        $this->app = AppFactory::create();
        $this->app->setBasePath(Constants::API_PREFIX_PATH);
        $this->app->addRoutingMiddleware();
        $this->app->addErrorMiddleware(true, true, true);
        $this->app->get('chat/read', function ($request, $response, $args) {
            $db = new \Aslan\Chat\DB();
            $chat = new \Aslan\Chat\Chat($db);
            $params = $request->getQueryParams();
            $from = isset($params['from']) ? intval($params['from']) : 0;
            $channel = isset($params['channel']) ? trim($params['channel']) : \Aslan\Chat\Config::getChatDefaultChannel();
            $messages = $chat->get_msgs($channel, $from);
            $response->getBody()->write(json_encode($messages));
            return $response->withHeader('Content-Type', 'application/json');
        });
        $this->app->post('chat/send', function ($request, $response, $args) {
            $db = new \Aslan\Chat\DB();
            $chat = new \Aslan\Chat\Chat($db);
            $post_data = $request->getParsedBody();
            $name = isset($post_data['name']) ? trim($post_data['name']) : '';
            $message = isset($post_data['message']) ? trim($post_data['message']) : '';
            $channel = isset($post_data['channel']) ? trim($post_data['channel']) : \Aslan\Chat\Config::getChatDefaultChannel();
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
        $db = new DB();
        $db->init();
    }

    /**
     * Test that the read endpoint returns 200 and JSON content type.
     */
    public function test_read_endpoint_returns_200() {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/chat/apis/chat/read');
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Test that the send endpoint validates input and returns error for empty fields.
     */
    public function test_send_endpoint_validates_input() {
        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(http_build_query(['name' => '', 'message' => '']));
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/chat/apis/chat/send')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($body);
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('error', (string)$response->getBody());
    }

    /**
     * Test that the read endpoint returns a JSON response.
     */
    public function test_read_returns_json_response() {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/chat/apis/chat/read');
        $response = $this->app->handle($request);
        $body = (string)$response->getBody();
        $this->assertJson($body);
    }

    /**
     * Test that the send endpoint returns a JSON response.
     */
    public function test_send_returns_json_response() {
        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(http_build_query(['name' => self::TEST_USER, 'message' => self::TEST_MESSAGE, 'channel' => self::TEST_CHANNEL]));
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/chat/apis/chat/send')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($body);
        $response = $this->app->handle($request);
        $this->assertJson((string)$response->getBody());
    }

    /**
     * Test sending and reading a message with special characters.
     */
    public function test_send_and_read_message_with_special_characters() {
        $specialUser = 'Üsér!@#';
        $specialMsg = 'Héllo <b>world</b> & "quotes"';
        $specialChan = 'chan-!@#';
        // Send message
        $streamFactory = new StreamFactory();
        $postData = ['name' => $specialUser, 'message' => $specialMsg, 'channel' => $specialChan];
        $body = $streamFactory->createStream(http_build_query(['name' => $specialUser, 'message' => $specialMsg, 'channel' => $specialChan]));
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/chat/apis/chat/send')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withParsedBody($postData);
        $response = $this->app->handle($request);
        $this->assertJson((string)$response->getBody());
        // Read message
        $readRequest = (new ServerRequestFactory())->createServerRequest('GET', '/chat/apis/chat/read?channel=' . urlencode($specialChan));
        $readResponse = $this->app->handle($readRequest);
        $body = (string)$readResponse->getBody();
        $msgs = json_decode($body, true);
        $this->assertNotEmpty($msgs);
        $this->assertEquals(htmlspecialchars($specialUser, ENT_QUOTES, 'UTF-8'), $msgs[0]['name']);
        $this->assertEquals(htmlspecialchars($specialMsg, ENT_QUOTES, 'UTF-8'), $msgs[0]['message']);
    }
} 