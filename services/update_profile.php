<?php
session_start();
require_once 'db_config.php';
require_once 'Users.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.html?error=unauthorized');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new Users();

    $user->PersonId = $_SESSION['user_id'];
    $user->FirstName = $_POST['first_name'];
    $user->LastName = $_POST['last_name'];
    $user->EmailAddress = $_POST['email'];
    $user->Username = $_POST['username'];

    if ($user->updateUser()) {
        header('Location: /profile.php?success=updated');
        exit();
    } else {
        echo "Error: Could not update profile. No changes were made.";
    }
}