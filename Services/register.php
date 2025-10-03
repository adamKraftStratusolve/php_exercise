<?php
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';
require_once 'api_helpers.php';

ApiResponse::requirePostMethod();

$user = new Users();

$user->FirstName = $_POST['firstName'] ?? '';
$user->LastName = $_POST['lastName'] ?? '';
$user->EmailAddress = $_POST['email'] ?? '';
$user->Username = $_POST['username'] ?? '';
$user->Password = $_POST['password'] ?? '';

if ($user->createUser()) {
    ApiResponse::success('User registered successfully.', 201);
} else {
    ApiResponse::error('Could not create user.', 500);
}