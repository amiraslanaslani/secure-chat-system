<?php
namespace Aslan\Chat;

use Aslan\Chat\Config as Config;

class DB {
    private $pdo = null;

    /**
     * Constructor can accept a PDO object or a file path (string).
     */
    public function __construct($pdoDsn = null) {
        if (is_string($pdoDsn) || $pdoDsn === null) {
            $dsn = $pdoDsn ?? Config::getPdoDsn();
            $this->pdo = $this->createPdo($dsn);
        } else {
            throw new \InvalidArgumentException('Invalid argument for DB constructor');
        }
    }

    /**
     * Instance: Create a new PDO for a given file.
     */
    private function createPdo($dsn) {
        $pdo = new \PDO($dsn);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    /**
     * Instance: Get PDO for a given file (or current if already set).
     */
    public function getPdo() {
        return $this->pdo;
    }

    /**
     * Instance: Create table if not exists.
     */
    public function init() {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS ' . Config::getChatTable() . ' (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp INTEGER NOT NULL,
            name TEXT NOT NULL,
            message TEXT NOT NULL,
            channel TEXT NOT NULL DEFAULT ' . $this->pdo->quote(Config::getChatDefaultChannel()) . '
        )');
    }
}
