<?php
session_start();
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.html?error=unauthorized');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = Database::getConnection();
    $user = new Users($pdo);

    $user->PersonId = $_SESSION['user_id'];
    $user->FirstName = $_POST['first_name'];
    $user->LastName = $_POST['last_name'];
    $user->EmailAddress = $_POST['email'];
    $user->Username = $_POST['username'];

    if ($user->updateUser()) {
        // --- Postman API Response ---
        $updatedUser = $user->findById();
        header('Content-Type: application/json');
        echo json_encode($updatedUser);
        exit();
    } else {
        // --- Postman API Response ---
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Could not update profile. No changes may have been made.']);
    }
}