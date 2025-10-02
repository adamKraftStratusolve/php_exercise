<?php
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = Database::getConnection();
    $user = new Users($pdo);

    $user->FirstName = $_POST['first_name'];
    $user->LastName = $_POST['last_name'];
    $user->EmailAddress = $_POST['email'];
    $user->Username = $_POST['username'];
    $user->Password = $_POST['password'];

    if ($user->createUser()) {
        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode(['success' => 'User registered successfully.']);
        exit();
    } else {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Could not create user.']);
    }
}