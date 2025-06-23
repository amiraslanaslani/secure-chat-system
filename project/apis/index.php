<?php
namespace Aslan\Chat;

use Aslan\Chat\App as App;
use Aslan\Chat\DB as DB;

require __DIR__ . '/../vendor/autoload.php';

function main() {
    $db = new DB();
    $app = new App($db);
    $app->run();
}

main();
