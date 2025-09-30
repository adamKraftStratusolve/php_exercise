<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.html?error=unauthorized');
    exit();
}

require_once 'db_config.php';
require_once 'Users.php';
require_once 'Posts.php';

$postsInstance = new Posts();
$allPosts = $postsInstance->getAllPosts();
