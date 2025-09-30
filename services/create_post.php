<?php
session_start();
require_once 'db_config.php';
require_once 'Model_Repositories/Posts.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.html?error=unauthorized');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['post_text'])) {
    $post = new Posts();
    $post->PostText = $_POST['post_text'];
    $post->UserId = $_SESSION['user_id'];

    if ($post->createPost()) {
        header('Location: /index.php');
        exit();
    } else {
        echo "Error: Could not create post.";
    }
} else {
    header('Location: /index.php?error=empty_post');
    exit();
}