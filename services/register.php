<?php
require_once 'db_config.php';
require_once 'Model_Repositories/Users.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new Users();

    $user->FirstName = $_POST['first_name'];
    $user->LastName = $_POST['last_name'];
    $user->EmailAddress = $_POST['email'];
    $user->Username = $_POST['username'];
    $user->Password = $_POST['password'];

    if ($user->createUser()) {
        header('Location: /login.html?success=registered');
        exit();
    } else {
        echo "Error: Could not create user.";
    }
}