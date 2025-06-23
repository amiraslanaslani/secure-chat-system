<?php
use PHPUnit\Framework\TestCase;
use Aslan\Chat\Constants;

class ConstantsTest extends TestCase {
    const DEFAULT_API_PREFIX = '/chat/project/apis/';
    const DEFAULT_DB_FILE = 'sqlite:../chat_data.sqlite';
    const DEFAULT_TABLE = 'messages';
    const DEFAULT_CHANNEL = 'default';

    /**
     * Test that Constants values are correct.
     */
    public function test_constants_values() {
        $this->assertEquals(self::DEFAULT_API_PREFIX, Constants::API_PREFIX_PATH);
        $this->assertEquals(self::DEFAULT_DB_FILE, Constants::PDO_DSN);
        $this->assertEquals(self::DEFAULT_TABLE, Constants::CHAT_TABLE);
        $this->assertEquals(self::DEFAULT_CHANNEL, Constants::CHAT_DEFAULT_CHANNEL);
    }

    /**
     * Test that Config can be mocked and reset.
     */
    public function test_config_mocking_and_reset() {
        \Aslan\Chat\Config::setApiPrefixPath('/mock/path/');
        \Aslan\Chat\Config::setPdoDsn('sqlite:mock_db.sqlite');
        \Aslan\Chat\Config::setChatTable('mock_table');
        \Aslan\Chat\Config::setChatDefaultChannel('mockchan');
        \Aslan\Chat\Config::setIsOnlyAllowedChannels(true);
        \Aslan\Chat\Config::setAllowedChannels(['test1', 'test2']);
        \Aslan\Chat\Config::setPrivateChannelPassword(['test1' => 'pass1', 'test2' => 'pass2']);
        
        $this->assertEquals('/mock/path/', \Aslan\Chat\Config::getApiPrefixPath());
        $this->assertEquals('sqlite:mock_db.sqlite', \Aslan\Chat\Config::getPdoDsn());
        $this->assertEquals('mock_table', \Aslan\Chat\Config::getChatTable());
        $this->assertEquals('mockchan', \Aslan\Chat\Config::getChatDefaultChannel());
        $this->assertEquals(true, \Aslan\Chat\Config::isOnlyAllowedChannels());
        $this->assertEquals(['test1', 'test2'], \Aslan\Chat\Config::getAllowedChannels());
        $this->assertEquals(['test1' => 'pass1', 'test2' => 'pass2'], \Aslan\Chat\Config::getPrivateChannelPassword());
        
        \Aslan\Chat\Config::reset();
        $this->assertEquals(self::DEFAULT_API_PREFIX, \Aslan\Chat\Config::getApiPrefixPath());
        $this->assertEquals(self::DEFAULT_DB_FILE, \Aslan\Chat\Config::getPdoDsn());
        $this->assertEquals(self::DEFAULT_TABLE, \Aslan\Chat\Config::getChatTable());
        $this->assertEquals(self::DEFAULT_CHANNEL, \Aslan\Chat\Config::getChatDefaultChannel());
    }

    /**
     * Test setting config to empty values and resetting.
     */
    public function test_config_set_empty_and_reset() {
        \Aslan\Chat\Config::setApiPrefixPath('');
        \Aslan\Chat\Config::setPdoDsn('');
        \Aslan\Chat\Config::setChatTable('');
        \Aslan\Chat\Config::setChatDefaultChannel('');
        \Aslan\Chat\Config::setIsOnlyAllowedChannels(false);
        \Aslan\Chat\Config::setAllowedChannels([]);
        \Aslan\Chat\Config::setPrivateChannelPassword([]);
        
        $this->assertEquals('', \Aslan\Chat\Config::getApiPrefixPath());
        $this->assertEquals('', \Aslan\Chat\Config::getPdoDsn());
        $this->assertEquals('', \Aslan\Chat\Config::getChatTable());
        $this->assertEquals('', \Aslan\Chat\Config::getChatDefaultChannel());
        $this->assertEquals(false, \Aslan\Chat\Config::isOnlyAllowedChannels());
        $this->assertEquals([], \Aslan\Chat\Config::getAllowedChannels());
        $this->assertEquals([], \Aslan\Chat\Config::getPrivateChannelPassword());
        
        \Aslan\Chat\Config::reset();
        $this->assertEquals(self::DEFAULT_API_PREFIX, \Aslan\Chat\Config::getApiPrefixPath());
        $this->assertEquals(self::DEFAULT_DB_FILE, \Aslan\Chat\Config::getPdoDsn());
        $this->assertEquals(self::DEFAULT_TABLE, \Aslan\Chat\Config::getChatTable());
        $this->assertEquals(self::DEFAULT_CHANNEL, \Aslan\Chat\Config::getChatDefaultChannel());
        $this->assertEquals(false, \Aslan\Chat\Config::isOnlyAllowedChannels());
        $this->assertEquals([Constants::CHAT_DEFAULT_CHANNEL], \Aslan\Chat\Config::getAllowedChannels());
        $this->assertEquals(Constants::PRIVATE_CHANNEL_PASSWORD, \Aslan\Chat\Config::getPrivateChannelPassword());
    }
} 