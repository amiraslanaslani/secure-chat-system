<?php
namespace Aslan\Chat;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Aslan\Chat\Config;

class ChannelAuthMiddleware implements MiddlewareInterface {
    public function process(Request $request, RequestHandler $handler): Response {
        // Determine channel from query (GET) or body (POST)
        $method = $request->getMethod();
        $channel = null;
        if ($method === 'GET') {
            $params = $request->getQueryParams();
            $channel = isset($params['channel']) ? trim($params['channel']) : Config::getChatDefaultChannel();
        } elseif ($method === 'POST') {
            $parsed = $request->getParsedBody();
            $channel = isset($parsed['channel']) ? trim($parsed['channel']) : Config::getChatDefaultChannel();
        }
        if ($channel === null) {
            $channel = Config::getChatDefaultChannel();
        }

        $passwords = Config::getPrivateChannelPassword();
        $requiredPassword = null;
        if (isset($passwords[$channel])) {
            $requiredPassword = $passwords[$channel];
        }

        if ($requiredPassword !== null) {
            $authHeader = $request->getHeaderLine('Authorization');
            if (preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
                $expectedToken = base64_encode($requiredPassword);
                if (hash_equals($expectedToken, $token)) {
                    return $handler->handle($request);
                }
            }
            $response = new \Slim\Psr7\Response(401);
            $response->getBody()->write(json_encode(['error' => 'Unauthorized: Invalid or missing token.']));
            return $response->withHeader('Content-Type', 'application/json');
        }
        // No password required for this channel
        return $handler->handle($request);
    }
}; 