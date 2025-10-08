<?php

require_once __DIR__ . '/cors_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'api_helpers.php';
ApiResponse::requirePostMethod();

$otp = $_POST['otp'] ?? '';

if (
    !isset($_SESSION['reset_otp_hash'], $_SESSION['reset_otp_expires'], $_SESSION['password_reset_in_progress_uid']) ||
    $_SESSION['reset_otp_expires'] < time()
) {
    unset($_SESSION['reset_otp_hash'], $_SESSION['reset_otp_expires'], $_SESSION['password_reset_in_progress_uid']);
    ApiResponse::error('Your OTP has expired or is invalid. Please start over.', 401);
}

if (password_verify($otp, $_SESSION['reset_otp_hash'])) {
    $_SESSION['password_reset_uid'] = $_SESSION['password_reset_in_progress_uid'];
    $_SESSION['password_reset_expires'] = time() + 600;

    unset($_SESSION['reset_otp_hash'], $_SESSION['reset_otp_expires'], $_SESSION['password_reset_in_progress_uid']);

    ApiResponse::success('OTP verified successfully.');
} else {
    ApiResponse::error('The OTP you entered is incorrect. Please try again.');
}