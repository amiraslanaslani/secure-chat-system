<?php
use PHPUnit\Framework\TestCase;
use Aslan\Chat\DB;
use Aslan\Chat\Constants;

class DBTest extends TestCase {
    /**
     * Test that init creates the messages table.
     */
    public function test_init_creates_table() {
        $db = new DB(DB::IN_MEMORY_PDO_DSN);
        $db->init();
        $pdo = $db->getPdo();
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='" . Constants::CHAT_TABLE . "'");
        $result = $stmt->fetchColumn();
        $this->assertEquals(Constants::CHAT_TABLE, $result);
    }

    /**
     * Test that get_chat_pdo returns a PDO instance.
     */
    public function test_get_chat_pdo_returns_pdo_instance() {
        $db = new DB(DB::IN_MEMORY_PDO_DSN);
        $pdo = $db->getPdo();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }

    /**
     * Test that init is idempotent (can be called multiple times safely).
     */
    public function test_init_is_idempotent() {
        $db = new DB(DB::IN_MEMORY_PDO_DSN);
        $db->init();
        $db->init();
        $pdo = $db->getPdo();
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='" . Constants::CHAT_TABLE . "'");
        $result = $stmt->fetchColumn();
        $this->assertEquals(Constants::CHAT_TABLE, $result);
    }

    /**
     * Test get_chat_pdo with an invalid file path throws exception.
     */
    public function test_get_chat_pdo_with_invalid_file_path() {
        $this->expectException(\PDOException::class);
        $db = new DB('sqlite:/invalid/path/doesnotexist.sqlite');
        $db->getPdo();
    }
} 