<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';

date_default_timezone_set(APP_TIMEZONE);

function get_db_connection(): PDO
{
    return Database::getConnection();
}
