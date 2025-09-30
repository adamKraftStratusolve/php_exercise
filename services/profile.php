<?php
session_start();
require_once 'db_config.php';
require_once 'Model_Repositories/Users.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.html?error=unauthorized');
    exit();
}

$user = new Users();
$user->PersonId = $_SESSION['user_id'];
$currentUser = $user->findById();
