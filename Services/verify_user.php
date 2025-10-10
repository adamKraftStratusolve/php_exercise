<?php
require_once __DIR__ . '/cors_config.php';
session_start();
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';
require_once 'api_helpers.php';

ApiResponse::requirePostMethod();

$email = $_POST['email'] ?? '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ApiResponse::error('A valid email address is required.');
}

$userInstance = new Users();
$user = $userInstance->findByEmail($email);

if ($user) {
    $otp = random_int(100000, 999999);

    $_SESSION['reset_otp_hash'] = password_hash($otp, PASSWORD_DEFAULT);
    $_SESSION['reset_otp_expires'] = time() + 600;
    $_SESSION['password_reset_in_progress_uid'] = $user['user_id'];

    $response_data = [
        'otp' => $otp,
        'email' => $user['email_address']
    ];
    ApiResponse::sendJson($response_data);

} else {
    ApiResponse::sendJson([
        'otp' => random_int(100000, 999999), // Send a fake OTP
        'email' => $email
    ]);
}