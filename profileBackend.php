<?php
require_once __DIR__ . '/Services/cors_config.php';
require_once 'auth_check.php';
require_once 'db_config.php';
require_once './Model_Repositories/Users.php';
require_once './Model_Repositories/Posts.php';
require_once './Services/api_helpers.php';

$user = new Users();
$user->personId = $_SESSION['user_id'];
$currentUser = $user->findById();

$postsInstance = new Posts();

$userPosts = $postsInstance->getPostsByUserId($_SESSION['user_id'], $_SESSION['user_id']);

$response_data = [
    'profile' => $currentUser,
    'posts' => $userPosts
];

ApiResponse::sendJson($response_data);