<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Comment.php';
require_once '../Services/api_helpers.php';

ApiResponse::requirePostMethod();

$userId = $_SESSION['user_id'];
$postId = $_POST['postId'] ?? 0;
$commentText = trim($_POST['commentText'] ?? '');

if (empty($postId) || empty($commentText)) {
    ApiResponse::error('Post ID and comment text are required.');
}

if (strlen($commentText) > 180) {
    ApiResponse::error('Comment cannot exceed 180 characters.');
}

$comment = new Comment();
$comment->postId = $postId;
$comment->userId = $userId;
$comment->commentText = $commentText;

if ($comment->create()) {
    $pdo = Database::getConnection();
    $sql = "SELECT 
                c.comment_text AS commentText, c.comment_timestamp AS commentTimestamp, 
                u.username, u.profile_image_url AS profileImageUrl 
            FROM comments c 
            JOIN users u ON c.user_id = u.user_id 
            WHERE c.comment_id = ? 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pdo->lastInsertId()]);
    $newCommentData = $stmt->fetch(PDO::FETCH_ASSOC);

    ApiResponse::success($newCommentData);
} else {
    ApiResponse::error('Failed to post comment.', 500);
}