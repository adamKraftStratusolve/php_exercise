<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';
require_once 'api_helpers.php';

ApiResponse::requirePostMethod();

$userId = $_SESSION['user_id'];

$data = [
    'userId' => $userId,
    'firstName' => $_POST['firstName'] ?? '',
    'lastName' => $_POST['lastName'] ?? '',
    'email' => $_POST['email'] ?? '',
    'currentPassword' => $_POST['currentPassword'] ?? null,
    'newPassword' => $_POST['newPassword'] ?? null
];

try {
    $userInstance = new Users();
    $result = $userInstance->updateProfile($data);

    if ($result['success']) {
        ApiResponse::sendJson(['success' => true, 'message' => $result['message']]);
    } else {
        ApiResponse::error($result['message']);
    }
} catch (Exception $e) {
    ApiResponse::error('An error occurred while updating the profile.', 500);
}