<?php
namespace Aslan\Chat;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Aslan\Chat\DB as DB;

class Chat {
    private $db;

    public function __construct(DB $db) {
        if(is_null($db)){
            $db = new DB();
        }
        $this->db = $db;
    }

    public function get_msgs($channel, $from=0) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare('SELECT timestamp, name, message FROM ' . Config::getChatTable() . ' WHERE channel = :channel ORDER BY id ASC LIMIT -1 OFFSET :from');
        $stmt->bindValue(':channel', $channel, \PDO::PARAM_STR);
        $stmt->bindValue(':from', $from, \PDO::PARAM_INT);
        $stmt->execute();
        $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $messages;
    }

    public function send_msg($name, $message, $channel) {
        $entry = [
            'timestamp' => time(),
            'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
            'channel' => htmlspecialchars($channel, ENT_QUOTES, 'UTF-8')
        ];
        try {
            $this->db->init();
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare('INSERT INTO ' . Config::getChatTable() . ' (timestamp, name, message, channel) VALUES (:timestamp, :name, :message, :channel)');
            $stmt->execute([
                ':timestamp' => $entry['timestamp'],
                ':name' => $entry['name'],
                ':message' => $entry['message'],
                ':channel' => $entry['channel']
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function read(Request $request, Response $response, $args) {
        $params = $request->getQueryParams();
        $from = isset($params['from']) ? intval($params['from']) : 0;
        $channel = isset($params['channel']) ? trim($params['channel']) : Config::getChatDefaultChannel();
        
        $messages = $this->get_msgs($channel, $from);
    
        $response->getBody()->write(json_encode($messages));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function send(Request $request, Response $response, $args) {
        $response->withHeader('Content-Type', 'application/json');
        $post_data = $request->getParsedBody();
    
        $name = isset($post_data['name']) ? trim($post_data['name']) : '';
        $message = isset($post_data['message']) ? trim($post_data['message']) : '';
        $channel = isset($post_data['channel']) ? trim($post_data['channel']) : Config::getChatDefaultChannel();
    
        if ($name === '' || $message === '' || $channel === '') {
            http_response_code(400);
            $response->getBody()->write(json_encode(['error' => 'Name, message and channel fields are required.']));
            return $response->withStatus(200);
        }

        if (Config::isOnlyAllowedChannels() && !in_array($channel, Config::getAllowedChannels(), true)) {
            $response->getBody()->write(json_encode(['error' => 'There is no ' . $channel . ' channel.']));
            return $response->withStatus(404);
        }

        $result = $this->send_msg($name, $message, $channel);
        if($result) {
            $response->getBody()->write(json_encode(['success' => true]));
            return $response;
        } else {
            $response->getBody()->write(json_encode(['error' => 'Database error.']));
            return $response->withStatus(500);
        }
    }
}
