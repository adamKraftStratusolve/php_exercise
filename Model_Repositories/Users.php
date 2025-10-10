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
        // Include the new defaults file
        require_once __DIR__ . '/../defaults.php';

        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, email_address, username, password, profile_image_url) VALUES (:first_name, :last_name, :email_address, :username, :password, :profile_image_url)";

        $params = [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email_address' => $this->emailAddress,
            'username' => $this->username,
            'password' => $hashedPassword,
            'profile_image_url' => DEFAULT_AVATAR_BASE64
        ];
        $statement = $this->run($sql, $params);

        if ($statement->rowCount() > 0) {
            return $this->pdo->lastInsertId();
        }
        return false;
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
        $this->findById();
        $currentUsername = $this->username;
        $currentEmail = $this->emailAddress;

        if ($currentEmail !== $data['email']) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Please provide a valid email address.'];
            }
            if ($this->isEmailTakenByAnother($data['email'], $data['userId'])) {
                return ['success' => false, 'message' => 'That email address is already in use.'];
            }
        }

        if ($currentUsername !== $data['username']) {
            if ($this->isUsernameTakenByAnother($data['username'], $data['userId'])) {
                return ['success' => false, 'message' => 'That username is already taken.'];
            }
        }

        $this->firstName = $data['firstName'];
        $this->lastName = $data['lastName'];
        $this->emailAddress = $data['email'];
        $this->username = $data['username'];

        $detailsUpdated = $this->updateUser();

        $passwordMessage = '';
        if (!empty($data['newPassword'])) {

            $passwordError = $this->validatePassword($data['newPassword']);
            if ($passwordError) {
                return ['success' => false, 'message' => $passwordError];
            }

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

    public function forcePasswordUpdate(int $userId, string $newPassword) {
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = :password WHERE user_id = :user_id";
        $params = [
            'password' => $newHashedPassword,
            'user_id' => $userId
        ];
        $statement = $this->run($sql, $params);
        return $statement->rowCount() > 0;
    }

    public function updateProfilePicture(int $userId, string $imageUrl) {
        $sql = "UPDATE users SET profile_image_url = :image_url WHERE user_id = :user_id";
        $params = [
            'image_url' => $imageUrl,
            'user_id' => $userId
        ];
        try {
            $this->run($sql, $params);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function findUserByUsernameAndEmail(string $username, string $email) {
        $sql = "SELECT user_id FROM users WHERE username = :username AND email_address = :email";
        $statement = $this->run($sql, ['username' => $username, 'email' => $email]);
        return $statement->fetch();
    }

    public function isUsernameTakenByAnother(string $username, int $userId) {
        $sql = "SELECT COUNT(*) FROM users WHERE username = :username AND user_id != :user_id";
        $statement = $this->run($sql, ['username' => $username, 'user_id' => $userId]);
        return $statement->fetchColumn() > 0;
    }

    public function isEmailTakenByAnother(string $email, int $userId) {
        $sql = "SELECT COUNT(*) FROM users WHERE email_address = :email AND user_id != :user_id";
        $statement = $this->run($sql, ['email' => $email, 'user_id' => $userId]);
        return $statement->fetchColumn() > 0;
    }

    public function validatePassword(string $password): ?string {
        if (strlen($password) < 8) {
            return "Password must be at least 8 characters long.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return "Password must contain at least one uppercase letter.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            return "Password must contain at least one lowercase letter.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            return "Password must contain at least one number.";
        }
        if (!preg_match('/[\'^?$%&*()}{@#~?><>,|=_+?-]/', $password)) {
            return "Password must contain at least one special character.";
        }
        return null;
    }

    public function findByEmail(string $email) {
        $sql = "SELECT * FROM users WHERE email_address = :email";
        $statement = $this->run($sql, ['email' => $email]);
        return $statement->fetch();
    }
}