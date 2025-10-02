<?php

abstract class Model {

    protected $pdo;
    protected $map = [];

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    protected function run(string $query, array $params = []): PDOStatement {
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        return $statement;
    }

    protected function mapData(array $data) {
        foreach ($this->map as $column_name => $property_name) {
            if (isset($data[$column_name])) {
                $this->{$property_name} = $data[$column_name];
            }
        }
    }
}