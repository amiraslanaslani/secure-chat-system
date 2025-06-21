<?php
namespace Aslan\Chat;

use Aslan\Chat\Config as Config;

class DB {
    private $pdo = null;

    /**
     * Constructor can accept a PDO object or a file path (string).
     */
    public function __construct($pdoOrFile = null) {
        if ($pdoOrFile instanceof \PDO) {
            $this->pdo = $pdoOrFile;
        } elseif (is_string($pdoOrFile) || $pdoOrFile === null) {
            $file = $pdoOrFile ?? Config::getChatDbFile();
            $this->pdo = $this->createPdo($file);
        } else {
            throw new \InvalidArgumentException('Invalid argument for DB constructor');
        }
    }

    /**
     * Instance: Create a new PDO for a given file.
     */
    private function createPdo($file) {
        $pdo = new \PDO('sqlite:' . $file);
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
