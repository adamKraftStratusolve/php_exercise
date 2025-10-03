<?php
require_once 'auth_check.php';
require_once 'db_config.php';
require_once './Model_Repositories/Posts.php';
require_once './Services/api_helpers.php';

$postsInstance = new Posts();

$allPosts = $postsInstance->getAllPosts($_SESSION['user_id']);

ApiResponse::sendJson($allPosts);