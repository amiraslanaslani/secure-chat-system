<?php
namespace Aslan\Chat;

class Constants {
    const API_PREFIX_PATH = "/chat/project/apis/";

    const PDO_DSN = "sqlite:../chat_data.sqlite";

    const CHAT_TABLE = "messages";
    const CHAT_DEFAULT_CHANNEL = "default";

    const IS_ONLY_ALLOWED_CHANNELS = false;
    const ALLOWED_CHANNELS = [self::CHAT_DEFAULT_CHANNEL];

    const PRIVATE_CHANNEL_PASSWORD = [
        self::CHAT_DEFAULT_CHANNEL => "password"
        // Add more specific channel passwords here as needed
        // For example: 'private' => 'private_password'
    ];
}
