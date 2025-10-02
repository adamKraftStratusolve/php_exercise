<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Posts.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['post_text'])) {
    $pdo = Database::getConnection();
    $post = new Posts($pdo);
    $post->PostText = $_POST['post_text'];
    $post->UserId = $_SESSION['user_id'];

    if ($post->createPost()) {
        // --- Postman API Response ---
        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode(['success' => 'Post created successfully.']);
        exit();
    } else {
        // --- Postman API Response ---
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Could not create post.']);
    }
} else {
    // --- Postman API Response ---
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Post text cannot be empty.']);
    exit();
}