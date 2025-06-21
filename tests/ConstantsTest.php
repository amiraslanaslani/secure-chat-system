<?php
use PHPUnit\Framework\TestCase;
use Aslan\Chat\Constants;

class ConstantsTest extends TestCase {
    const DEFAULT_API_PREFIX = '/chat/apis/';
    const DEFAULT_DB_FILE = '../chat_data.sqlite';
    const DEFAULT_TABLE = 'messages';
    const DEFAULT_CHANNEL = 'default';

    /**
     * Test that Constants values are correct.
     */
    public function test_constants_values() {
        $this->assertEquals(self::DEFAULT_API_PREFIX, Constants::API_PREFIX_PATH);
        $this->assertEquals(self::DEFAULT_DB_FILE, Constants::CHAT_DB_FILE);
        $this->assertEquals(self::DEFAULT_TABLE, Constants::CHAT_TABLE);
        $this->assertEquals(self::DEFAULT_CHANNEL, Constants::CHAT_DEFAULT_CHANNEL);
    }

    /**
     * Test that Config can be mocked and reset.
     */
    public function test_config_mocking_and_reset() {
        \Aslan\Chat\Config::setApiPrefixPath('/mock/path/');
        \Aslan\Chat\Config::setChatDbFile('mock_db.sqlite');
        \Aslan\Chat\Config::setChatTable('mock_table');
        \Aslan\Chat\Config::setChatDefaultChannel('mockchan');
        $this->assertEquals('/mock/path/', \Aslan\Chat\Config::getApiPrefixPath());
        $this->assertEquals('mock_db.sqlite', \Aslan\Chat\Config::getChatDbFile());
        $this->assertEquals('mock_table', \Aslan\Chat\Config::getChatTable());
        $this->assertEquals('mockchan', \Aslan\Chat\Config::getChatDefaultChannel());
        \Aslan\Chat\Config::reset();
        $this->assertEquals(self::DEFAULT_API_PREFIX, \Aslan\Chat\Config::getApiPrefixPath());
        $this->assertEquals(self::DEFAULT_DB_FILE, \Aslan\Chat\Config::getChatDbFile());
        $this->assertEquals(self::DEFAULT_TABLE, \Aslan\Chat\Config::getChatTable());
        $this->assertEquals(self::DEFAULT_CHANNEL, \Aslan\Chat\Config::getChatDefaultChannel());
    }

    /**
     * Test setting config to empty values and resetting.
     */
    public function test_config_set_empty_and_reset() {
        \Aslan\Chat\Config::setApiPrefixPath('');
        \Aslan\Chat\Config::setChatDbFile('');
        \Aslan\Chat\Config::setChatTable('');
        \Aslan\Chat\Config::setChatDefaultChannel('');
        $this->assertEquals('', \Aslan\Chat\Config::getApiPrefixPath());
        $this->assertEquals('', \Aslan\Chat\Config::getChatDbFile());
        $this->assertEquals('', \Aslan\Chat\Config::getChatTable());
        $this->assertEquals('', \Aslan\Chat\Config::getChatDefaultChannel());
        \Aslan\Chat\Config::reset();
        $this->assertEquals(self::DEFAULT_API_PREFIX, \Aslan\Chat\Config::getApiPrefixPath());
        $this->assertEquals(self::DEFAULT_DB_FILE, \Aslan\Chat\Config::getChatDbFile());
        $this->assertEquals(self::DEFAULT_TABLE, \Aslan\Chat\Config::getChatTable());
        $this->assertEquals(self::DEFAULT_CHANNEL, \Aslan\Chat\Config::getChatDefaultChannel());
    }
} 