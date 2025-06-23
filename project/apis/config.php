<?php
namespace Aslan\Chat;

class Config {
    private static $apiPrefixPath = null;
    private static $pdoDsn = null;
    private static $chatTable = null;
    private static $chatDefaultChannel = null;
    private static $isOnlyAllowedChannels = null;
    private static $allowedChannels = null;
    private static $privateChannelPassword = null;

    public static function getApiPrefixPath() {
        return self::$apiPrefixPath ?? Constants::API_PREFIX_PATH;
    }
    public static function getPdoDsn() {
        return self::$pdoDsn ?? Constants::PDO_DSN;
    }
    public static function getChatTable() {
        return self::$chatTable ?? Constants::CHAT_TABLE;
    }
    public static function getChatDefaultChannel() {
        return self::$chatDefaultChannel ?? Constants::CHAT_DEFAULT_CHANNEL;
    }
    public static function isOnlyAllowedChannels() {
        return self::$isOnlyAllowedChannels ?? Constants::IS_ONLY_ALLOWED_CHANNELS;
    }
    public static function getAllowedChannels() {
        return self::$allowedChannels ?? Constants::ALLOWED_CHANNELS;
    }
    public static function getPrivateChannelPassword() {
        return self::$privateChannelPassword ?? Constants::PRIVATE_CHANNEL_PASSWORD;
    }

    // Setters for testing
    public static function setApiPrefixPath($value) {
        self::$apiPrefixPath = $value;
    }
    public static function setPdoDsn($value) {
        self::$pdoDsn = $value;
    }
    public static function setChatTable($value) {
        self::$chatTable = $value;
    }
    public static function setChatDefaultChannel($value) {
        self::$chatDefaultChannel = $value;
    }
    public static function setIsOnlyAllowedChannels($value) {
        self::$isOnlyAllowedChannels = $value;
    }
    public static function setAllowedChannels($value) {
        self::$allowedChannels = $value;
    }
    public static function setPrivateChannelPassword($value) {
        self::$privateChannelPassword = $value;
    }
    public static function reset() {
        self::$apiPrefixPath = null;
        self::$pdoDsn = null;
        self::$chatTable = null;
        self::$chatDefaultChannel = null;
        self::$isOnlyAllowedChannels = null;
        self::$allowedChannels = null;
        self::$privateChannelPassword = null;
    }
}
