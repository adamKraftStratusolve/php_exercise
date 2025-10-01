<?php
class Database
{
    private static $pdo_instance = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo_instance == null) {
            $host = 'host.docker.internal';
            $db = 'twitter_app_db';
            $user = 'root';
            $pass = '1623';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$pdo_instance = new PDO($dsn, $user, $pass, $options);
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return self::$pdo_instance;
    }
}
