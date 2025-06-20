<?php
// chat_write.php

header('Content-Type: application/json');
require_once __DIR__ . '/consts.php';
require_once __DIR__ . '/db.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Get and sanitize input
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$channel = isset($_POST['channel']) ? trim($_POST['channel']) : CHAT_DEFAULT_CHANNEL;

if ($name === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Name and message are required.']);
    exit;
}

$entry = [
    'timestamp' => time(),
    'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
    'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
    'channel' => htmlspecialchars($channel, ENT_QUOTES, 'UTF-8')
];

try {
    $pdo = get_chat_pdo();
    // Create table if not exists
    $pdo->exec('CREATE TABLE IF NOT EXISTS ' . CHAT_TABLE . ' (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        timestamp INTEGER NOT NULL,
        name TEXT NOT NULL,
        message TEXT NOT NULL,
        channel TEXT NOT NULL DEFAULT ' . $pdo->quote(CHAT_DEFAULT_CHANNEL) . '
    )');
    // Insert message
    $stmt = $pdo->prepare('INSERT INTO ' . CHAT_TABLE . ' (timestamp, name, message, channel) VALUES (:timestamp, :name, :message, :channel)');
    $stmt->execute([
        ':timestamp' => $entry['timestamp'],
        ':name' => $entry['name'],
        ':message' => $entry['message'],
        ':channel' => $entry['channel']
    ]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error.']);
    exit;
}
