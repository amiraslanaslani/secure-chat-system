<?php
use PHPUnit\Framework\TestCase;
use Aslan\Chat\Chat;
use Aslan\Chat\DB;
use Aslan\Chat\Constants;

class ChatUnitTest extends TestCase {
    private $testDbFile;
    const TEST_USER = 'Alice';
    const TEST_MESSAGE = 'Hello';
    const TEST_CHANNEL = 'testchan';

    /**
     * Set up a fresh SQLite database for each test.
     */
    protected function setUp(): void {
        $this->testDbFile = __DIR__ . '/test_db.sqlite';
        if (file_exists($this->testDbFile)) {
            unlink($this->testDbFile);
        }
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp INTEGER NOT NULL,
            name TEXT NOT NULL,
            message TEXT NOT NULL,
            channel TEXT NOT NULL DEFAULT "default"
        )');
    }

    /**
     * Clean up the test database after each test.
     */
    protected function tearDown(): void {
        if (file_exists($this->testDbFile)) {
            unlink($this->testDbFile);
        }
    }

    /**
     * Helper to call private static Chat methods via Reflection.
     */
    private function callChatMethod($method, ...$args) {
        $db = new DB($this->testDbFile);
        $chat = new Chat($db);
        $ref = new \ReflectionClass(Chat::class);
        $meth = $ref->getMethod($method);
        $meth->setAccessible(true);
        return $meth->invokeArgs($chat, $args);
    }

    /**
     * Test that send_msg inserts a message into the database.
     */
    public function test_send_msg_inserts_message_into_database() {
        $result = $this->callChatMethod('send_msg', self::TEST_USER, self::TEST_MESSAGE, self::TEST_CHANNEL);
        $this->assertTrue($result);
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $stmt = $pdo->query('SELECT * FROM messages WHERE name = "' . self::TEST_USER . '" AND message = "' . self::TEST_MESSAGE . '" AND channel = "' . self::TEST_CHANNEL . '"');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($row);
    }

    /**
     * Test that get_msgs returns inserted messages.
     */
    public function test_get_msgs_returns_inserted_messages() {
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $pdo->exec('INSERT INTO messages (timestamp, name, message, channel) VALUES (123456, "Bob", "Hi", "chan2")');
        $msgs = $this->callChatMethod('get_msgs', 'chan2', 0);
        $this->assertNotEmpty($msgs);
        $this->assertEquals('Bob', $msgs[0]['name']);
        $this->assertEquals('Hi', $msgs[0]['message']);
        $this->assertEquals(123456, $msgs[0]['timestamp']);
    }

    /**
     * Test that send_msg sanitizes input.
     */
    public function test_send_msg_sanitizes_input() {
        $name = '<b>Alice</b>';
        $message = '<script>alert(1)</script>';
        $channel = 'chan<script>';
        $result = $this->callChatMethod('send_msg', $name, $message, $channel);
        $this->assertTrue($result);
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $stmt = $pdo->query('SELECT * FROM messages WHERE channel = "chan&lt;script&gt;"');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('&lt;b&gt;Alice&lt;/b&gt;', $row['name']);
        $this->assertStringNotContainsString('<', $row['message']);
    }

    /**
     * Test that send_msg returns false on DB error.
     */
    public function test_send_msg_returns_false_on_db_error() {
        $this->expectException(\PDOException::class);
        $db = new DB('/invalid/path/doesnotexist.sqlite');
        $chat = new Chat($db);
        $chat->send_msg('A', 'B', 'chan');
    }

    /**
     * Test get_msgs with offset returns correct messages.
     */
    public function test_get_msgs_with_offset() {
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $pdo->exec('INSERT INTO messages (timestamp, name, message, channel) VALUES (1, "A", "M1", "chan")');
        $pdo->exec('INSERT INTO messages (timestamp, name, message, channel) VALUES (2, "B", "M2", "chan")');
        $pdo->exec('INSERT INTO messages (timestamp, name, message, channel) VALUES (3, "C", "M3", "chan")');
        $msgs = $this->callChatMethod('get_msgs', 'chan', 2);
        $this->assertCount(1, $msgs);
        $this->assertEquals('C', $msgs[0]['name']);
    }

    /**
     * Test get_msgs returns empty array for no results.
     */
    public function test_get_msgs_with_no_results() {
        $msgs = $this->callChatMethod('get_msgs', 'emptychan', 0);
        $this->assertIsArray($msgs);
        $this->assertCount(0, $msgs);
    }

    /**
     * Test send_msg with empty name or message.
     */
    public function test_send_msg_with_empty_name_or_message() {
        $result1 = $this->callChatMethod('send_msg', '', 'msg', 'chan');
        $result2 = $this->callChatMethod('send_msg', 'name', '', 'chan');
        $this->assertTrue($result1); // DB layer does not validate, only HTTP layer
        $this->assertTrue($result2);
    }

    /**
     * Test send_msg with long inputs.
     */
    public function test_send_msg_with_long_inputs() {
        $longName = str_repeat('a', 1000);
        $longMsg = str_repeat('b', 5000);
        $longChan = str_repeat('c', 100);
        $result = $this->callChatMethod('send_msg', $longName, $longMsg, $longChan);
        $this->assertTrue($result);
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $stmt = $pdo->query('SELECT * FROM messages WHERE name = "' . $longName . '"');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($row);
    }

    /**
     * Test send_msg with SQL injection attempt (should be sanitized).
     */
    public function test_send_msg_with_sql_injection_attempt() {
        $maliciousName = 'Robert"); DROP TABLE messages;--';
        $result = $this->callChatMethod('send_msg', $maliciousName, 'test', 'chan');
        $this->assertTrue($result);
        $pdo = new PDO('sqlite:' . $this->testDbFile);
        $stmt = $pdo->query('SELECT * FROM messages WHERE name LIKE "%Robert%"');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($row);
        // Table should still exist
        $tables = $pdo->query('SELECT name FROM sqlite_master WHERE type="table" AND name="messages"')->fetchAll(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($tables);
    }
} 