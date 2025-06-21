<?php
use PHPUnit\Framework\TestCase;
use Aslan\Chat\DB;
use Aslan\Chat\Constants;

class DBTest extends TestCase {
    private $testDbFile;
    const OTHER_DB_FILE = __DIR__ . '/other_test_db.sqlite';

    /**
     * Set up a fresh SQLite database file for each test.
     */
    protected function setUp(): void {
        $this->testDbFile = __DIR__ . '/test_db.sqlite';
        if (file_exists($this->testDbFile)) {
            unlink($this->testDbFile);
        }
        if (file_exists(self::OTHER_DB_FILE)) {
            unlink(self::OTHER_DB_FILE);
        }
    }

    /**
     * Clean up the test database after each test.
     */
    protected function tearDown(): void {
        if (file_exists($this->testDbFile)) {
            unlink($this->testDbFile);
        }
        if (file_exists(self::OTHER_DB_FILE)) {
            unlink(self::OTHER_DB_FILE);
        }
    }

    /**
     * Test that the constructor creates the database file.
     */
    public function test_constructor_creates_database_file() {
        $db = new DB($this->testDbFile);
        $this->assertFileExists($this->testDbFile);
    }

    /**
     * Test that init creates the messages table.
     */
    public function test_init_creates_table() {
        $db = new DB($this->testDbFile);
        $db->init();
        $sqlite = new \SQLite3($this->testDbFile);
        $result = $sqlite->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='" . Constants::CHAT_TABLE . "'");
        $this->assertEquals(Constants::CHAT_TABLE, $result);
    }

    /**
     * Test that get_chat_pdo returns a PDO instance.
     */
    public function test_get_chat_pdo_returns_pdo_instance() {
        $db = new DB($this->testDbFile);
        $pdo = $db->getPdo();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }

    /**
     * Test that get_chat_pdo with different files resets PDO.
     */
    public function test_get_chat_pdo_with_different_files_resets_pdo() {
        $db1 = new DB($this->testDbFile);
        $db2 = new DB(self::OTHER_DB_FILE);
        $pdo1 = $db1->getPdo();
        $pdo2 = $db2->getPdo();
        $this->assertInstanceOf(\PDO::class, $pdo2);
        $this->assertNotSame($pdo1, $pdo2);
    }

    /**
     * Test that init is idempotent (can be called multiple times safely).
     */
    public function test_init_is_idempotent() {
        $db = new DB($this->testDbFile);
        $db->init();
        $db->init();
        $sqlite = new \SQLite3($this->testDbFile);
        $result = $sqlite->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='" . Constants::CHAT_TABLE . "'");
        $this->assertEquals(Constants::CHAT_TABLE, $result);
    }

    /**
     * Test get_chat_pdo with an invalid file path throws exception.
     */
    public function test_get_chat_pdo_with_invalid_file_path() {
        $this->expectException(\PDOException::class);
        $db = new DB('/invalid/path/doesnotexist.sqlite');
        $db->getPdo();
    }
} 