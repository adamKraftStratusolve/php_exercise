<?php
require_once __DIR__ . '/cors_config.php';
session_start();
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
$user = $userInstance->findUserByUsernameAndEmail($username, $email); // Correct method call

if ($user) {
    $otp = random_int(100000, 999999);

    $_SESSION['reset_otp_hash'] = password_hash($otp, PASSWORD_DEFAULT);
    $_SESSION['reset_otp_expires'] = time() + 600;
    $_SESSION['password_reset_in_progress_uid'] = $user['user_id'];

    $response_data = [
        'otp' => $otp,
        'email' => $email
    ];
    ApiResponse::sendJson($response_data);

} else {
    ApiResponse::error('The username and/or email do not match our records.', 401);
}