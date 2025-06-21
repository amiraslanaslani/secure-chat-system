<?php
namespace Aslan\Chat;

use Aslan\Chat\Config as Config;

class DB {
    private $db;
    private $dbFile;
    private static $pdo = null;

    public function __construct($dbFile = null) {
        $this->dbFile = $dbFile ?? __DIR__ . '/../chat_data.sqlite';
        $this->db = new \SQLite3($this->dbFile);
    }

    public static function get_chat_pdo($dbFile = null) {
        if (self::$pdo === null || $dbFile !== null) {
            $file = $dbFile ?? Config::getChatDbFile();
            self::$pdo = new \PDO('sqlite:' . $file);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }

    public static function init($dbFile = null) {
        $pdo = self::get_chat_pdo($dbFile);
        $pdo->exec('CREATE TABLE IF NOT EXISTS ' . Config::getChatTable() . ' (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp INTEGER NOT NULL,
            name TEXT NOT NULL,
            message TEXT NOT NULL,
            channel TEXT NOT NULL DEFAULT ' . $pdo->quote(Config::getChatDefaultChannel()) . '
        )');
    }
}
