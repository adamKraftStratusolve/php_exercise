<?php
require_once __DIR__ . '/cors_config.php';
session_start();

require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';
require_once '../Services/api_helpers.php';

ApiResponse::requirePostMethod();

$user = new Users();
$user->firstName = $_POST['firstName'] ?? '';
$user->lastName = $_POST['lastName'] ?? '';
$user->emailAddress = $_POST['email'] ?? '';
$user->username = $_POST['username'] ?? '';
$user->password = $_POST['password'] ?? '';

if (!filter_var($user->emailAddress, FILTER_VALIDATE_EMAIL)) {
    ApiResponse::error('Please provide a valid email address.');
}
$passwordError = $user->validatePassword($user->password);
if ($passwordError) {
    ApiResponse::error($passwordError);
}
$userChecker = new Users();
if ($userChecker->findByCredential($user->username)) {
    ApiResponse::error('That username is already taken. Please choose another.');
}
if ($userChecker->findByCredential($user->emailAddress)) {
    ApiResponse::error('An account with this email address already exists.');
}

$newUserId = $user->createUser();

if ($newUserId) {

    $_SESSION['user_id'] = $newUserId;
    $_SESSION['username'] = $user->username;

    ApiResponse::success('Registration successful! Logging you in...', 201);
} else {
    ApiResponse::error('Could not create user due to a server error.', 500);
}