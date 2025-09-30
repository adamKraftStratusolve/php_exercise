<?php
require_once 'db_config.php';
require_once 'Model_Repositories/Users.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new Users();
    $user->Username = $_POST['username'];
    $user->Password = $_POST['password'];

    $loggedInUser = $user->getUserByCredentials();

    if ($loggedInUser) {
        $_SESSION['user_id'] = $loggedInUser->PersonId;
        $_SESSION['username'] = $loggedInUser->Username;

        header('Location: /index.php');
        exit();
    } else {
        echo "Invalid username or password.";
    }
}