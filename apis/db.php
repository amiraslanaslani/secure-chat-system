<?php
namespace Aslan\Chat;

use Aslan\Chat\Constants as Constants;

class DB {
    private static $pdo = null;

    public static function get_chat_pdo() {
        if (self::$pdo === null) {
            self::$pdo = new \PDO('sqlite:' . Constants::CHAT_DB_FILE);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }

    public static function init() {
        $pdo = self::get_chat_pdo();

        $pdo->exec('CREATE TABLE IF NOT EXISTS ' . Constants::CHAT_TABLE . ' (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp INTEGER NOT NULL,
            name TEXT NOT NULL,
            message TEXT NOT NULL,
            channel TEXT NOT NULL DEFAULT ' . $pdo->quote(Constants::CHAT_DEFAULT_CHANNEL) . '
        )');
    }
}
