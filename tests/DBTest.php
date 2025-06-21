<?php
use PHPUnit\Framework\TestCase;
use Aslan\Chat\DB;
use Aslan\Chat\Constants;

class DBTest extends TestCase {
    private $testDbFile;

    protected function setUp(): void {
        $this->testDbFile = __DIR__ . '/test_db.sqlite';
        if (file_exists($this->testDbFile)) {
            unlink($this->testDbFile);
        }
    }

    protected function tearDown(): void {
        if (file_exists($this->testDbFile)) {
            unlink($this->testDbFile);
        }
    }

    public function testConstructorCreatesDatabaseFile() {
        $db = new DB($this->testDbFile);
        $this->assertFileExists($this->testDbFile);
    }

    public function testInitCreatesTable() {
        DB::init($this->testDbFile);
        $db = new \SQLite3($this->testDbFile);
        $result = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='" . Constants::CHAT_TABLE . "'");
        $this->assertEquals(Constants::CHAT_TABLE, $result);
    }

    public function testGetChatPdoReturnsPdoInstance() {
        $pdo = DB::get_chat_pdo($this->testDbFile);
        $this->assertInstanceOf(\PDO::class, $pdo);
    }

    public function testGetChatPdoWithDifferentFilesResetsPdo() {
        $pdo1 = DB::get_chat_pdo($this->testDbFile);
        $otherDbFile = __DIR__ . '/other_test_db.sqlite';
        if (file_exists($otherDbFile)) {
            unlink($otherDbFile);
        }
        $pdo2 = DB::get_chat_pdo($otherDbFile);
        $this->assertInstanceOf(\PDO::class, $pdo2);
        $this->assertNotSame($pdo1, $pdo2);
        if (file_exists($otherDbFile)) {
            unlink($otherDbFile);
        }
    }

    public function testInitIsIdempotent() {
        DB::init($this->testDbFile);
        DB::init($this->testDbFile);
        $db = new \SQLite3($this->testDbFile);
        $result = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='" . Constants::CHAT_TABLE . "'");
        $this->assertEquals(Constants::CHAT_TABLE, $result);
    }
} 