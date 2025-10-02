<?php
session_start();
require_once 'db_config.php';
require_once './Model_Repositories/Users.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is accepted.']);
    exit();
}

$credential = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($credential) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Username/email and password are required.']);
    exit();
}

try {
    $pdo = Database::getConnection();
    $userInstance = new Users($pdo);

    $user = $userInstance->findByCredential($credential);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        http_response_code(200);
        echo json_encode(['success' => 'Login successful.']);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}