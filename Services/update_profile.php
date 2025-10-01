<?php
session_start();
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = Database::getConnection();
    $user = new Users($pdo);
    $user->PersonId = $_SESSION['user_id'];

    $profileUpdated = false;
    $passwordUpdated = false;
    $error = null;

    if (isset($_POST['first_name']) || isset($_POST['last_name']) || isset($_POST['email'])) {
        $user->FirstName = $_POST['first_name'];
        $user->LastName = $_POST['last_name'];
        $user->EmailAddress = $_POST['email'];
        if ($user->updateUser()) {
            $profileUpdated = true;
        }
    }

    if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
        if ($user->updatePassword($_POST['current_password'], $_POST['new_password'])) {
            $passwordUpdated = true;
        } else {
            $error = 'Incorrect current password.';
        }
    }

    header('Content-Type: application/json');
    if ($profileUpdated || $passwordUpdated) {
        $updatedUser = $user->findById();
        echo json_encode($updatedUser);
    } elseif ($error) {
        http_response_code(400);
        echo json_encode(['error' => $error]);
    } else {
        http_response_code(200);
        echo json_encode(['message' => 'No changes were made to the profile.']);
    }
    exit();
}