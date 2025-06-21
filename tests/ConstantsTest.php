<?php
use PHPUnit\Framework\TestCase;
use Aslan\Chat\Constants;

class ConstantsTest extends TestCase {
    public function testConstantsValues() {
        $this->assertEquals('/chat/apis/', Constants::API_PREFIX_PATH);
        $this->assertEquals('../chat_data.sqlite', Constants::CHAT_DB_FILE);
        $this->assertEquals('messages', Constants::CHAT_TABLE);
        $this->assertEquals('default', Constants::CHAT_DEFAULT_CHANNEL);
    }

    public function testConfigMocking() {
        \Aslan\Chat\Config::setApiPrefixPath('/mock/path/');
        \Aslan\Chat\Config::setChatDbFile('mock_db.sqlite');
        \Aslan\Chat\Config::setChatTable('mock_table');
        \Aslan\Chat\Config::setChatDefaultChannel('mockchan');
        $this->assertEquals('/mock/path/', \Aslan\Chat\Config::getApiPrefixPath());
        $this->assertEquals('mock_db.sqlite', \Aslan\Chat\Config::getChatDbFile());
        $this->assertEquals('mock_table', \Aslan\Chat\Config::getChatTable());
        $this->assertEquals('mockchan', \Aslan\Chat\Config::getChatDefaultChannel());
        \Aslan\Chat\Config::reset();
        $this->assertEquals('/chat/apis/', \Aslan\Chat\Config::getApiPrefixPath());
        $this->assertEquals('../chat_data.sqlite', \Aslan\Chat\Config::getChatDbFile());
        $this->assertEquals('messages', \Aslan\Chat\Config::getChatTable());
        $this->assertEquals('default', \Aslan\Chat\Config::getChatDefaultChannel());
    }
} 