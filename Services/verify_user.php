<?php
require_once __DIR__ . '/cors_config.php';
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
$user = $userInstance->findUserByUsernameAndEmail($username, $email);

if ($user) {
    $_SESSION['password_reset_uid'] = $user['user_id'];
    $_SESSION['password_reset_expires'] = time() + 300;
    ApiResponse::success('User verified.');
} else {
    ApiResponse::error('Invalid username or email.', 401);
}