<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Comment.php';
require_once 'api_helpers.php';

ApiResponse::requirePostMethod();

$userId = $_SESSION['user_id'];
$postId = $_POST['postId'] ?? 0;
$commentText = trim($_POST['commentText'] ?? '');

if (empty($postId) || empty($commentText)) {
    ApiResponse::error('Post ID and comment text are required.');
}

$comment = new Comment();

$comment->postId = $postId;
$comment->userId = $userId;
$comment->commentText = $commentText;

if ($comment->create()) {
    ApiResponse::success('Comment posted.');
} else {
    ApiResponse::error('Failed to post comment.', 500);
}