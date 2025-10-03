<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Posts.php';
require_once 'api_helpers.php';

ApiResponse::requirePostMethod();

if (empty($_POST['post_text'])) {
    ApiResponse::error('Post text cannot be empty.');
}

$post = new Posts();
$post->PostText = $_POST['postText'];
$post->UserId = $_SESSION['user_id'];

if ($post->createPost()) {
    ApiResponse::success('Post created successfully.', 201);
} else {
    ApiResponse::error('Could not create post.', 500);
}