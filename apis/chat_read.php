<?php
namespace Chat\Apis;
require_once __DIR__ . '/consts.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
use function Chat\DB\get_chat_pdo;

// Get 'from' parameter (default 0)
$from = isset($_GET['from']) ? intval($_GET['from']) : 0;
$channel = isset($_GET['channel']) ? trim($_GET['channel']) : CHAT_DEFAULT_CHANNEL;

if (!file_exists(CHAT_DB_FILE)) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = get_chat_pdo();
    $stmt = $pdo->prepare('SELECT timestamp, name, message FROM ' . CHAT_TABLE . ' WHERE channel = :channel ORDER BY id ASC LIMIT -1 OFFSET :from');
    $stmt->bindValue(':channel', $channel, \PDO::PARAM_STR);
    $stmt->bindValue(':from', $from, \PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    echo json_encode($messages);
} catch (Exception $e) {
    echo json_encode([]);
    exit;
}
