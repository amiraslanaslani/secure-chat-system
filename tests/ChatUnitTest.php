<?php
use PHPUnit\Framework\TestCase;
use Aslan\Chat\Chat;
use Aslan\Chat\DB;
use Aslan\Chat\Constants;

class ChatUnitTest extends TestCase {
    private $testDbFile;

    protected function setUp(): void {
        $this->testDbFile = __DIR__ . '/test_db.sqlite';
        if (file_exists($this->testDbFile)) {
            unlink($this->testDbFile);
        }
        // Patch Constants::CHAT_DB_FILE for this test (not possible, so patch DB::$pdo)
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp INTEGER NOT NULL,
            name TEXT NOT NULL,
            message TEXT NOT NULL,
            channel TEXT NOT NULL DEFAULT "default"
        )');
        $ref = new ReflectionClass(DB::class);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $prop->setValue(null, $pdo);
    }

    protected function tearDown(): void {
        $ref = new ReflectionClass(DB::class);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
        if (file_exists($this->testDbFile)) {
            unlink($this->testDbFile);
        }
    }

    public function testSendMsgInsertsMessage() {
        $ref = new ReflectionClass(Chat::class);
        $method = $ref->getMethod('send_msg');
        $method->setAccessible(true);
        $result = $method->invoke(null, 'Alice', 'Hello', 'testchan');
        $this->assertTrue($result);
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $stmt = $pdo->query('SELECT * FROM messages WHERE name = "Alice" AND message = "Hello" AND channel = "testchan"');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($row);
    }

    public function testGetMsgsReturnsInsertedMessages() {
        // Insert a message first
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $pdo->exec('INSERT INTO messages (timestamp, name, message, channel) VALUES (123456, "Bob", "Hi", "chan2")');
        $ref = new ReflectionClass(Chat::class);
        $method = $ref->getMethod('get_msgs');
        $method->setAccessible(true);
        $msgs = $method->invoke(null, 'chan2', 0);
        $this->assertNotEmpty($msgs);
        $this->assertEquals('Bob', $msgs[0]['name']);
        $this->assertEquals('Hi', $msgs[0]['message']);
        $this->assertEquals(123456, $msgs[0]['timestamp']);
    }

    public function testSendMsgSanitizesInput() {
        $ref = new ReflectionClass(Chat::class);
        $method = $ref->getMethod('send_msg');
        $method->setAccessible(true);
        $name = '<b>Alice</b>';
        $message = '<script>alert(1)</script>';
        $channel = 'chan<script>';
        $result = $method->invoke(null, $name, $message, $channel);
        $this->assertTrue($result);
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $stmt = $pdo->query('SELECT * FROM messages WHERE channel = "chan&lt;script&gt;"');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('&lt;b&gt;Alice&lt;/b&gt;', $row['name']);
        $this->assertStringNotContainsString('<', $row['message']);
    }

    public function testSendMsgReturnsFalseOnDbError() {
        $ref = new ReflectionClass(Chat::class);
        $method = $ref->getMethod('send_msg');
        $method->setAccessible(true);
        // Close PDO connection to simulate error
        $refDB = new ReflectionClass(DB::class);
        $prop = $refDB->getProperty('pdo');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
        // Use an invalid DB file path
        $result = $method->invoke(null, 'A', 'B', 'chan', '/invalid/path/doesnotexist.sqlite');
        $this->assertFalse($result);
    }

    public function testGetMsgsWithOffset() {
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $pdo->exec('INSERT INTO messages (timestamp, name, message, channel) VALUES (1, "A", "M1", "chan")');
        $pdo->exec('INSERT INTO messages (timestamp, name, message, channel) VALUES (2, "B", "M2", "chan")');
        $pdo->exec('INSERT INTO messages (timestamp, name, message, channel) VALUES (3, "C", "M3", "chan")');
        $ref = new ReflectionClass(Chat::class);
        $method = $ref->getMethod('get_msgs');
        $method->setAccessible(true);
        $msgs = $method->invoke(null, 'chan', 2);
        $this->assertCount(1, $msgs);
        $this->assertEquals('C', $msgs[0]['name']);
    }

    public function testGetMsgsWithNoResults() {
        $ref = new ReflectionClass(Chat::class);
        $method = $ref->getMethod('get_msgs');
        $method->setAccessible(true);
        $msgs = $method->invoke(null, 'emptychan', 0);
        $this->assertIsArray($msgs);
        $this->assertCount(0, $msgs);
    }

    public function testSendMsgWithEmptyNameOrMessage() {
        $ref = new ReflectionClass(Chat::class);
        $method = $ref->getMethod('send_msg');
        $method->setAccessible(true);
        $result1 = $method->invoke(null, '', 'msg', 'chan');
        $result2 = $method->invoke(null, 'name', '', 'chan');
        $this->assertTrue($result1); // DB layer does not validate, only HTTP layer
        $this->assertTrue($result2);
    }

    public function testSendMsgWithLongInputs() {
        $ref = new ReflectionClass(Chat::class);
        $method = $ref->getMethod('send_msg');
        $method->setAccessible(true);
        $longName = str_repeat('a', 1000);
        $longMsg = str_repeat('b', 5000);
        $longChan = str_repeat('c', 100);
        $result = $method->invoke(null, $longName, $longMsg, $longChan);
        $this->assertTrue($result);
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $stmt = $pdo->query('SELECT * FROM messages WHERE name = "' . $longName . '"');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($row);
    }
} 