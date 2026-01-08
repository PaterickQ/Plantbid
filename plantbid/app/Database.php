<?php

namespace App;

use mysqli;
use RuntimeException;

class Database
{
    private static ?mysqli $connection = null;

    public static function getConnection(): mysqli
    {
        if (self::$connection instanceof mysqli) {
            return self::$connection;
        }

        $config = require __DIR__ . '/../config/database.php';
        $conn = new mysqli(
            $config['host'],
            $config['user'],
            $config['pass'],
            $config['db'],
            $config['port']
        );

        if ($conn->connect_error) {
            throw new RuntimeException('Database connection failed.');
        }

        self::$connection = $conn;
        return self::$connection;
    }
}
