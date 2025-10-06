<?php
require_once __DIR__ . '/Services/cors_config.php';
session_start();
require_once 'db_config.php';
require_once './Model_Repositories/Users.php';
require_once './Services/api_helpers.php';

ApiResponse::requirePostMethod();

$credential = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($credential) || empty($password)) {
    ApiResponse::error('Username/email and password are required.');
}

try {
    $userInstance = new Users();
    $user = $userInstance->findByCredential($credential);

    if (!$user) {
        ApiResponse::error('No account found with that email or username.', 401);
    }

    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        ApiResponse::success('Login successful.');
    } else {

        ApiResponse::error('Incorrect password. Please try again.', 401);
    }

} catch (Exception $e) {
    ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
}