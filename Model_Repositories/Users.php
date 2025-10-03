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

    public function updateProfile($data) {

        $this->PersonId = $data['user_id'];
        $this->FirstName = $data['first_name'];
        $this->LastName = $data['last_name'];
        $this->EmailAddress = $data['email'];
        $this->Username = $data['username'];

        $detailsUpdated = $this->updateUser();

        $passwordMessage = '';
        if (!empty($data['new_password'])) {
            if (empty($data['current_password'])) {
                return ['success' => false, 'message' => 'Current password is required to set a new one.'];
            }

            $passwordUpdated = $this->updatePassword($data['current_password'], $data['new_password']);

            if ($passwordUpdated) {
                $passwordMessage = ' Password was also updated.';
            } else {
                $message = 'Current password was incorrect. Profile details were saved, but the password was not changed.';
                return ['success' => false, 'message' => $message];
            }
        }

        if ($detailsUpdated || !empty($passwordMessage)) {
            return ['success' => true, 'message' => 'Profile updated successfully.' . $passwordMessage];
        } else {
            return ['success' => true, 'message' => 'No changes were made.'];
        }
    }

    public function findByCredential($credential) {
        $sql = "SELECT * FROM users WHERE username = :uname OR email_address = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'uname' => $credential,
            'email' => $credential
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function forcePasswordUpdate(int $userId, string $newPassword): bool {
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = :password WHERE user_id = :user_id";
        $params = [
            'password' => $newHashedPassword,
            'user_id' => $userId
        ];
        $statement = $this->run($sql, $params);
        return $statement->rowCount() > 0;
    }
}