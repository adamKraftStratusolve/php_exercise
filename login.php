<?php
require_once 'db_config.php';
require_once './Model_Repositories/Users.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pdo = Database::getConnection();

    $user = new Users($pdo);

    $user->Username = $_POST['username'];
    $user->Password = $_POST['password'];

    $loggedInUser = $user->getUserByCredentials();

    if ($loggedInUser) {
        $_SESSION['user_id'] = $loggedInUser->PersonId;
        $_SESSION['username'] = $loggedInUser->Username;

        header('Location: /index.php');
        // --- Postman API Endpoint ---\\
        header('Content-Type: application/json');
        echo json_encode($loggedInUser);
        exit();
    } else {
        http_response_code(401);
        // --- Postman API Endpoint ---\\
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid username or password.']);
    }
}