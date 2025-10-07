<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Posts.php';
require_once '../Services/api_helpers.php';

ApiResponse::requirePostMethod();

$postText = $_POST['postText'] ?? '';

if (empty($postText)) {
    ApiResponse::error('Post text cannot be empty.');
}

if (strlen($postText) > 280) {
    ApiResponse::error('Post cannot exceed 280 characters.');
}

$post = new Posts();

$post->postText = $postText;
$post->userId = $_SESSION['user_id'];

$newPostId = $post->createPost();

if ($newPostId) {

    $newPostData = $post->getPostById($newPostId, $_SESSION['user_id']);
    ApiResponse::sendJson($newPostData, 201);
} else {
    ApiResponse::error('Could not create post.', 500);
}