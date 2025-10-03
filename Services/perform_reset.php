<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';
require_once 'api_helpers.php';

ApiResponse::requirePostMethod();

if (!isset($_SESSION['password_reset_uid']) || $_SESSION['password_reset_expires'] < time()) {
    unset($_SESSION['password_reset_uid'], $_SESSION['password_reset_expires']);
    ApiResponse::error('Unauthorized: Please verify your details again.', 401);
}

$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

if (empty($password) || $password !== $confirmPassword) {
    ApiResponse::error('Passwords cannot be empty and must match.');
}

$userId = $_SESSION['password_reset_uid'];
$userInstance = new Users();

if ($userInstance->forcePasswordUpdate($userId, $password)) {
    unset($_SESSION['password_reset_uid'], $_SESSION['password_reset_expires']);
    ApiResponse::success('Password has been reset successfully.');
} else {
    ApiResponse::error('Failed to update password.', 500);
}