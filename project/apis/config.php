<?php
namespace Aslan\Chat;

class Config {
    private static $apiPrefixPath = null;
    private static $chatDbFile = null;
    private static $chatTable = null;
    private static $chatDefaultChannel = null;

    public static function getApiPrefixPath() {
        return self::$apiPrefixPath ?? Constants::API_PREFIX_PATH;
    }
    public static function getChatDbFile() {
        return self::$chatDbFile ?? Constants::CHAT_DB_FILE;
    }
    public static function getChatTable() {
        return self::$chatTable ?? Constants::CHAT_TABLE;
    }
    public static function getChatDefaultChannel() {
        return self::$chatDefaultChannel ?? Constants::CHAT_DEFAULT_CHANNEL;
    }

    // Setters for testing
    public static function setApiPrefixPath($value) {
        self::$apiPrefixPath = $value;
    }
    public static function setChatDbFile($value) {
        self::$chatDbFile = $value;
    }
    public static function setChatTable($value) {
        self::$chatTable = $value;
    }
    public static function setChatDefaultChannel($value) {
        self::$chatDefaultChannel = $value;
    }
    public static function reset() {
        self::$apiPrefixPath = null;
        self::$chatDbFile = null;
        self::$chatTable = null;
        self::$chatDefaultChannel = null;
    }
}
