<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Posts.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is accepted.']);
    exit();
}

$postId = $_POST['post_id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$postId) {
    http_response_code(400);
    echo json_encode(['error' => 'Post ID is required.']);
    exit();
}

try {
    $postsInstance = new Posts();
    $success = $postsInstance->deletePost($postId, $userId);

    if ($success) {
        echo json_encode(['success' => 'Post deleted successfully.']);
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'You do not have permission to delete this post.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while deleting the post.']);
}