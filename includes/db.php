<?php
require_once __DIR__ . '/../src/bootstrap.php';

function get_db_connection(): PDO
{
    return Database::getConnection();
}
