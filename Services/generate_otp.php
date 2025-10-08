<?php
session_start();

header('Content-Type: application/json');

$otp = random_int(100000, 999999);
$userEmail = $_POST['email_to'] ?? null;

if ($userEmail) {
    $_SESSION['otp'] = $otp;
    $_SESSION['reset_email'] = $userEmail;

    echo json_encode(['success' => true, 'otp' => $otp]);
} else {
    echo json_encode(['success' => false, 'message' => 'Email not provided.']);
}
?>