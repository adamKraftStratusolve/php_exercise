<?php
require_once 'auth_check.php';
require_once 'db_config.php';
require_once './Model_Repositories/Posts.php';

$postsInstance = new Posts();

$allPosts = $postsInstance->getAllPosts();

header('Content-Type: application/json');
echo json_encode($allPosts);