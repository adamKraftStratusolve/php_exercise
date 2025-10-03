<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Posts.php';
require_once 'api_helpers.php';

ApiResponse::requirePostMethod();

$userId = $_SESSION['user_id'];
$postId = $_POST['postId'] ?? 0;

if (empty($postId)) {
    ApiResponse::error('Post ID is required.');
}

try {
    $postsInstance = new Posts();
    $postsInstance->toggleLike($postId, $userId);
    ApiResponse::success('Action successful.');
} catch (Exception $e) {
    ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
}