<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.html?error=unauthorized');
    exit();
}

require_once 'db_config.php';
require_once './Model_Repositories/Posts.php';

$pdo = Database::getConnection();

$postsInstance = new Posts($pdo);

$allPosts = $postsInstance->getAllPosts();

// --- Postman API Endpoint ---\\
header('Content-Type: application/json');
echo json_encode($allPosts);