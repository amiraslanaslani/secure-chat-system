<?php
namespace Chat\DB;
require_once __DIR__ . '/consts.php';

function get_chat_pdo() {
    $pdo = new \PDO('sqlite:' . CHAT_DB_FILE);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $pdo;
}
