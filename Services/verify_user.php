<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';
require_once 'api_helpers.php';

ApiResponse::requirePostMethod();

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';

if (empty($username) || empty($email)) {
    ApiResponse::error('Username and email are required.');
}

$userInstance = new Users();
$pdo = Database::getConnection();

$stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND email_address = ?");
$stmt->execute([$username, $email]);
$user = $stmt->fetch();

if ($user) {

    $_SESSION['password_reset_uid'] = $user['user_id'];
    $_SESSION['password_reset_expires'] = time() + 300;
    ApiResponse::success('User verified.');
} else {
    ApiResponse::error('Invalid username or email.', 401);
}