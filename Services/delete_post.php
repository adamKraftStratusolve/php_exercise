<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Posts.php';
require_once 'api_helpers.php';

ApiResponse::requirePostMethod();

$postId = $_POST['postId'] ?? null;
$userId = $_SESSION['user_id'];

if (!$postId) {
    ApiResponse::error('Post ID is required.');
}

try {
    $postsInstance = new Posts();
    $success = $postsInstance->deletePost($postId, $userId);

    if ($success) {
        ApiResponse::success('Post deleted successfully.');
    } else {
        ApiResponse::error('You do not have permission to delete this post.', 403);
    }
} catch (Exception $e) {
    ApiResponse::error('An error occurred while deleting the post.', 500);
}