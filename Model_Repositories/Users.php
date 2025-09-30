<?php
class Users extends Database {
    public $PersonId;
    public $FirstName;
    public $LastName;
    public $EmailAddress;
    public $Username;
    public $Password;

    public function getUserByCredentials() {

        $user = $this->findByUsername();

        if ($user && password_verify($this->Password, $user->Password)) {
            return $user;
        }

        return false;
    }

    public function createUser() {

        $hashedPassword = password_hash($this->Password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, email_address, username, password) VALUES (:first_name, :last_name, :email_address, :username, :password)";

        $params = [
            'first_name' => $this->FirstName,
            'last_name' => $this->LastName,
            'email_address' => $this->EmailAddress,
            'username' => $this->Username,
            'password' => $hashedPassword
        ];

        $statement = $this->run($sql, $params);
        return $statement->rowCount() > 0;
    }

    public function updateUser() {

        $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, email_address = :email_address, username = :username WHERE user_id = :user_id";

        $params = [
            'first_name' => $this->FirstName,
            'last_name' => $this->LastName,
            'email_address' => $this->EmailAddress,
            'username' => $this->Username,
            'user_id' => $this->PersonId
        ];

        $statement = $this->run($sql, $params);
        return $statement->rowCount() > 0;
    }

    public function findById() {

        $sql = "SELECT * FROM users WHERE user_id = :user_id";
        $statement = $this->run($sql, ['user_id' => $this->PersonId]);
        $statement->setFetchMode(PDO::FETCH_CLASS, 'Users');
        return $statement->fetch();
    }

    public function findByUsername() {

        $sql = "SELECT * FROM users WHERE username = :username";
        $statement = $this->run($sql, ['username' => $this->Username]);
        $statement->setFetchMode(PDO::FETCH_CLASS, 'Users');
        return $statement->fetch();
    }

}