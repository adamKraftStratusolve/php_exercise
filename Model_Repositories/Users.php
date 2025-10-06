<?php
require_once 'Base_Model.php';

class Users extends Model {
    public $personId;
    public $firstName;
    public $lastName;
    public $emailAddress;
    public $username;
    public $password;
    public $profileImageUrl;

    protected $map = [
        'user_id' => 'personId',
        'first_name' => 'firstName',
        'last_name' => 'lastName',
        'email_address' => 'emailAddress',
        'username' => 'username',
        'password' => 'password',
        'profile_image_url' => 'profileImageUrl'
    ];

    public function findById() {
        $sql = "SELECT * FROM users WHERE user_id = :user_id";
        $statement = $this->run($sql, ['user_id' => $this->personId]);
        $data = $statement->fetch();

        if ($data) {
            $this->mapData($data);
            if ($this->profileImageUrl === '') {
                $this->profileImageUrl = null;
            }

            return $this;
        }

        return false;
    }

    public function createUser() {
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (first_name, last_name, email_address, username, password) VALUES (:first_name, :last_name, :email_address, :username, :password)";
        $params = [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email_address' => $this->emailAddress,
            'username' => $this->username,
            'password' => $hashedPassword
        ];
        $statement = $this->run($sql, $params);
        return $statement->rowCount() > 0;
    }

    public function updateUser() {
        $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, email_address = :email_address, username = :username WHERE user_id = :user_id";

        $params = [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email_address' => $this->emailAddress,
            'username' => $this->username,
            'user_id' => $this->personId
        ];

        $statement = $this->run($sql, $params);
        return $statement->rowCount() > 0;
    }

    public function updatePassword($currentPassword, $newPassword) {
        $user_data = $this->run("SELECT password FROM users WHERE user_id = :user_id", ['user_id' => $this->personId])->fetch();

        if ($user_data && password_verify($currentPassword, $user_data['password'])) {
            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = :password WHERE user_id = :user_id";
            $params = [
                'password' => $newHashedPassword,
                'user_id' => $this->personId
            ];
            $statement = $this->run($sql, $params);
            return $statement->rowCount() > 0;
        }

        return false;
    }

    public function updateProfile($data) {

        $this->personId = $data['userId'];
        $this->firstName = $data['firstName'];
        $this->lastName = $data['lastName'];
        $this->emailAddress = $data['email'];
        $this->username = $data['username'];

        $detailsUpdated = $this->updateUser();

        $passwordMessage = '';
        if (!empty($data['newPassword'])) {
            if (empty($data['currentPassword'])) {
                return ['success' => false, 'message' => 'Current password is required to set a new one.'];
            }

            $passwordUpdated = $this->updatePassword($data['currentPassword'], $data['newPassword']);

            if ($passwordUpdated) {
                $passwordMessage = ' Password was also updated.';
            } else {
                return ['success' => false, 'message' => 'Current password was incorrect. Profile details were saved, but the password was not changed.'];
            }
        }

        if ($detailsUpdated || !empty($passwordMessage)) {
            return ['success' => true, 'message' => 'Profile updated successfully.' . $passwordMessage];
        } else {
            return ['success' => true, 'message' => 'No changes were made.'];
        }
    }

    public function findByCredential($credential)
    {
        $sql = "SELECT * FROM users WHERE username = :username OR email_address = :email";
        $params = [
            'username' => $credential,
            'email' => $credential
        ];
        $statement = $this->run($sql, $params);
        return $statement->fetch();
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

    public function updateProfilePicture(int $userId, string $imageUrl): bool {
        $sql = "UPDATE users SET profile_image_url = :image_url WHERE user_id = :user_id";
        $params = [
            'image_url' => $imageUrl,
            'user_id' => $userId
        ];
        $statement = $this->run($sql, $params);
        return $statement->rowCount() > 0;
    }

    public function findUserByUsernameAndEmail(string $username, string $email) {
        $sql = "SELECT user_id FROM users WHERE username = :username AND email_address = :email";
        $statement = $this->run($sql, ['username' => $username, 'email' => $email]);
        return $statement->fetch();
    }
}