<?php
namespace Aslan\Chat;

class Constants {
    const API_PREFIX_PATH = "/chat/project/apis/";

    const PDO_DSN = "sqlite:../chat_data.sqlite";

    const CHAT_TABLE = "messages";
    const CHAT_DEFAULT_CHANNEL = "default";
}
