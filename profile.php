<?php
require_once 'auth_check.php';
require_once 'db_config.php';
require_once './Model_Repositories/Users.php';
require_once './Model_Repositories/Posts.php';

$user = new Users();
$user->PersonId = $_SESSION['user_id'];
$currentUser = $user->findById();

$postsInstance = new Posts();
$userPosts = $postsInstance->getPostsByUserId($_SESSION['user_id']);

header('Content-Type: application/json');

$response_data = [
    'profile' => $currentUser,
    'posts' => $userPosts
];

echo json_encode($response_data);


