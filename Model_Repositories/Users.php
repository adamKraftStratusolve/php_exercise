<?php
require_once 'Base_Model.php';

class Users extends Model {
    public $PersonId;
    public $FirstName;
    public $LastName;
    public $EmailAddress;
    public $Username;
    public $Password;

    protected $map = [
        'user_id' => 'PersonId',
        'first_name' => 'FirstName',
        'last_name' => 'LastName',
        'email_address' => 'EmailAddress',
        'username' => 'Username',
        'password' => 'Password'
    ];

    public function getUserByCredentials() {
        $user_data = $this->findByUsername();

        if ($user_data && password_verify($this->Password, $user_data['password'])) {

            $userObject = new Users($this->pdo);
            $userObject->mapData($user_data);
            return $userObject;
        }

        return false;
    }

    public function findByUsername() {
        $sql = "SELECT * FROM users WHERE username = :username";
        $statement = $this->run($sql, ['username' => $this->Username]);
        return $statement->fetch();
    }

    public function findById() {
        $sql = "SELECT * FROM users WHERE user_id = :user_id";
        $statement = $this->run($sql, ['user_id' => $this->PersonId]);
        $data = $statement->fetch();
        if ($data) {
            $this->mapData($data);
            return $this;
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
        $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, email_address = :email_address WHERE user_id = :user_id";
        $params = [
            'first_name' => $this->FirstName,
            'last_name' => $this->LastName,
            'email_address' => $this->EmailAddress,
            'user_id' => $this->PersonId
        ];
        $statement = $this->run($sql, $params);
        return $statement->rowCount() > 0;
    }

    public function updatePassword($currentPassword, $newPassword) {
        $user_data = $this->run("SELECT password FROM users WHERE user_id = :user_id", ['user_id' => $this->PersonId])->fetch();

        if ($user_data && password_verify($currentPassword, $user_data['password'])) {

            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = :password WHERE user_id = :user_id";
            $params = [
                'password' => $newHashedPassword,
                'user_id' => $this->PersonId
            ];
            $statement = $this->run($sql, $params);
            return $statement->rowCount() > 0;
        }

        return false;
    }
}