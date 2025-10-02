<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is accepted.']);
    exit();
}

$userId = $_SESSION['user_id'];

$data = [
    'user_id' => $userId,
    'first_name' => $_POST['first_name'] ?? '',
    'last_name' => $_POST['last_name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'username' => $_POST['username'] ?? '',
    'current_password' => $_POST['current_password'] ?? null,
    'new_password' => $_POST['new_password'] ?? null
];

try {
    $pdo = Database::getConnection();
    $userInstance = new Users($pdo);

    $result = $userInstance->updateProfile($data);

    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => $result['message']]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => $result['message']]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while updating the profile.']);
}