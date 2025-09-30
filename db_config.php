<?php
class Database
{
    private static $instance = null;
    public $pdo;

    function __construct()
    {

        $host = 'db';
        $db = 'twitter_app_db ';
        $user = 'dockerlocal';
        $pass = '1qI$9$$NkTWm81';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    static function getInstance(): ?Database
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    function run($query, $params = []){

        $DatabaseInstance = Database::getInstance()->pdo;
        $statement = $DatabaseInstance->prepare("$query");
        $statement->execute($params);
        return $statement;
    }
}