<?php

use PHPUnit\Framework\TestCase;
use Aslan\Chat\ChannelAuthMiddleware;
use Aslan\Chat\Config;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Aslan\Chat\Constants;

class ChannelAuthMiddlewareTest extends TestCase
{
    private ChannelAuthMiddleware $middleware;
    private ServerRequestFactory $requestFactory;
    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->middleware = new ChannelAuthMiddleware();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
        
        // Reset config to default values before each test
        Config::reset();
    }

    protected function tearDown(): void
    {
        // Reset config after each test
        Config::reset();
    }

    public function testGetRequestWithDefaultChannel()
    {
        // Create a GET request without channel parameter (uses default channel)
        $request = $this->requestFactory->createServerRequest('GET', '/test');
        
        // Add valid authorization header for default channel
        $token = base64_encode('password');
        $request = $request->withHeader('Authorization', 'Bearer ' . $token);
        
        // Mock the request handler
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetRequestWithSpecificChannel()
    {
        // Create a GET request with channel parameter
        $request = $this->requestFactory->createServerRequest('GET', '/test?channel=test-channel');
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostRequestWithDefaultChannel()
    {
        // Create a POST request without channel in body (uses default channel)
        $request = $this->requestFactory->createServerRequest('POST', '/test');
        
        // Add valid authorization header for default channel
        $token = base64_encode('password');
        $request = $request->withHeader('Authorization', 'Bearer ' . $token);
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostRequestWithSpecificChannel()
    {
        // Create a POST request with channel in body
        $request = $this->requestFactory->createServerRequest('POST', '/test');
        $request = $request->withParsedBody(['channel' => 'test-channel']);
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testChannelWithPasswordRequiresValidToken()
    {
        // Set up a channel with password requirement
        Config::setPrivateChannelPassword(['private-channel' => 'secret123']);
        
        // Create a GET request for the private channel
        $request = $this->requestFactory->createServerRequest('GET', '/test?channel=private-channel');
        
        // Add valid authorization header
        $token = base64_encode('secret123');
        $request = $request->withHeader('Authorization', 'Bearer ' . $token);
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testChannelWithPasswordRejectsInvalidToken()
    {
        // Set up a channel with password requirement
        Config::setPrivateChannelPassword(['private-channel' => 'secret123']);
        
        // Create a GET request for the private channel
        $request = $this->requestFactory->createServerRequest('GET', '/test?channel=private-channel');
        
        // Add invalid authorization header
        $request = $request->withHeader('Authorization', 'Bearer invalid-token');
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        
        $response->getBody()->rewind();
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('Unauthorized: Invalid or missing token.', $body['error']);
    }

    public function testChannelWithPasswordRejectsMissingToken()
    {
        // Set up a channel with password requirement
        Config::setPrivateChannelPassword(['private-channel' => 'secret123']);
        
        // Create a GET request for the private channel without authorization header
        $request = $this->requestFactory->createServerRequest('GET', '/test?channel=private-channel');
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        
        $response->getBody()->rewind();
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('Unauthorized: Invalid or missing token.', $body['error']);
    }

    public function testChannelWithPasswordRejectsMalformedAuthHeader()
    {
        // Set up a channel with password requirement
        Config::setPrivateChannelPassword(['private-channel' => 'secret123']);
        
        // Create a GET request for the private channel
        $request = $this->requestFactory->createServerRequest('GET', '/test?channel=private-channel');
        
        // Add malformed authorization header
        $request = $request->withHeader('Authorization', 'InvalidFormat token123');
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        
        $response->getBody()->rewind();
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('Unauthorized: Invalid or missing token.', $body['error']);
    }

    public function testChannelWithPasswordRejectsEmptyToken()
    {
        // Set up a channel with password requirement
        Config::setPrivateChannelPassword(['private-channel' => 'secret123']);
        
        // Create a GET request for the private channel
        $request = $this->requestFactory->createServerRequest('GET', '/test?channel=private-channel');
        
        // Add authorization header with empty token
        $request = $request->withHeader('Authorization', 'Bearer ');
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        
        $response->getBody()->rewind();
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('Unauthorized: Invalid or missing token.', $body['error']);
    }

    public function testChannelWithPasswordAcceptsCaseInsensitiveBearer()
    {
        // Set up a channel with password requirement
        Config::setPrivateChannelPassword(['private-channel' => 'secret123']);
        
        // Create a GET request for the private channel
        $request = $this->requestFactory->createServerRequest('GET', '/test?channel=private-channel');
        
        // Add authorization header with lowercase 'bearer'
        $token = base64_encode('secret123');
        $request = $request->withHeader('Authorization', 'bearer ' . $token);
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testChannelWithPasswordAcceptsMixedCaseBearer()
    {
        // Set up a channel with password requirement
        Config::setPrivateChannelPassword(['private-channel' => 'secret123']);
        
        // Create a GET request for the private channel
        $request = $this->requestFactory->createServerRequest('GET', '/test?channel=private-channel');
        
        // Add authorization header with mixed case 'BeArEr'
        $token = base64_encode('secret123');
        $request = $request->withHeader('Authorization', 'BeArEr ' . $token);
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testChannelWithWhitespaceTrimming()
    {
        // Set up a channel with password requirement
        Config::setPrivateChannelPassword(['private-channel' => 'secret123']);
        
        // Create a GET request for the private channel with whitespace
        $request = $this->requestFactory->createServerRequest('GET', '/test?channel=  private-channel  ');
        
        // Add valid authorization header
        $token = base64_encode('secret123');
        $request = $request->withHeader('Authorization', 'Bearer ' . $token);
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostRequestWithWhitespaceTrimming()
    {
        // Set up a channel with password requirement
        Config::setPrivateChannelPassword(['private-channel' => 'secret123']);
        
        // Create a POST request for the private channel with whitespace
        $request = $this->requestFactory->createServerRequest('POST', '/test');
        $request = $request->withParsedBody(['channel' => '  private-channel  ']);
        
        // Add valid authorization header
        $token = base64_encode('secret123');
        $request = $request->withHeader('Authorization', 'Bearer ' . $token);
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNullChannelFallsBackToDefault()
    {
        // Create a GET request with null channel (falls back to default)
        $request = $this->requestFactory->createServerRequest('GET', '/test?channel=');
        
        // Add valid authorization header for default channel
        $token = base64_encode('password');
        $request = $request->withHeader('Authorization', 'Bearer ' . $token);
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMultipleChannelsWithDifferentPasswords()
    {
        // Set up multiple channels with different passwords
        Config::setPrivateChannelPassword([
            'channel1' => 'password1',
            'channel2' => 'password2'
        ]);
        
        // Test channel1 with correct password
        $request1 = $this->requestFactory->createServerRequest('GET', '/test?channel=channel1');
        $token1 = base64_encode('password1');
        $request1 = $request1->withHeader('Authorization', 'Bearer ' . $token1);
        
        $handler1 = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse1 = $this->responseFactory->createResponse(200);
        $handler1->expects($this->once())
                 ->method('handle')
                 ->with($request1)
                 ->willReturn($expectedResponse1);

        $response1 = $this->middleware->process($request1, $handler1);
        $this->assertEquals(200, $response1->getStatusCode());
        
        // Test channel2 with wrong password
        $request2 = $this->requestFactory->createServerRequest('GET', '/test?channel=channel2');
        $request2 = $request2->withHeader('Authorization', 'Bearer ' . $token1); // Wrong token
        
        $handler2 = $this->createMock(RequestHandlerInterface::class);
        $handler2->expects($this->never())->method('handle');

        $response2 = $this->middleware->process($request2, $handler2);
        $this->assertEquals(401, $response2->getStatusCode());
    }

    public function testChannelWithoutPasswordRequiresNoAuth()
    {
        // Set up a channel without password requirement
        Config::setPrivateChannelPassword([]);
        
        // Create a GET request for a channel without password
        $request = $this->requestFactory->createServerRequest('GET', '/test?channel=public-channel');
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDefaultChannelWithoutPasswordRequiresNoAuth()
    {
        // Set up no password requirement for any channel
        Config::setPrivateChannelPassword([]);
        
        // Create a GET request without channel parameter (uses default)
        $request = $this->requestFactory->createServerRequest('GET', '/test');
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testOtherChannelsPasswordFallback()
    {
        // Set up password for OTHER_CHANNELS (fallback)
        Config::setPrivateChannelPassword([
            Constants::OTHER_CHANNELS => 'fallback-secret',
            'explicit-channel' => 'explicit-secret',
        ]);

        // Request to a channel not explicitly listed (should use fallback password)
        $request = $this->requestFactory->createServerRequest('GET', '/test?channel=not-in-list');
        $token = base64_encode('fallback-secret');
        $request = $request->withHeader('Authorization', 'Bearer ' . $token);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(200);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());

        // Request to a channel not explicitly listed with wrong token
        $request2 = $this->requestFactory->createServerRequest('GET', '/test?channel=not-in-list');
        $request2 = $request2->withHeader('Authorization', 'Bearer wrong-token');
        $handler2 = $this->createMock(RequestHandlerInterface::class);
        $handler2->expects($this->never())->method('handle');
        $response2 = $this->middleware->process($request2, $handler2);
        $this->assertEquals(401, $response2->getStatusCode());
        $response2->getBody()->rewind();
        $body = json_decode($response2->getBody()->getContents(), true);
        $this->assertEquals('Unauthorized: Invalid or missing token.', $body['error']);

        // Request to an explicitly listed channel (should use explicit password)
        $request3 = $this->requestFactory->createServerRequest('GET', '/test?channel=explicit-channel');
        $token3 = base64_encode('explicit-secret');
        $request3 = $request3->withHeader('Authorization', 'Bearer ' . $token3);
        $handler3 = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse3 = $this->responseFactory->createResponse(200);
        $handler3->expects($this->once())
                ->method('handle')
                ->with($request3)
                ->willReturn($expectedResponse3);
        $response3 = $this->middleware->process($request3, $handler3);
        $this->assertEquals(200, $response3->getStatusCode());
    }
} 