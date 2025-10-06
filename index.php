<?php
require_once __DIR__ . '/Services/cors_config.php';
require_once 'auth_check.php';
require_once 'db_config.php';
require_once './Model_Repositories/Posts.php';
require_once './Services/api_helpers.php';

$sinceId = isset($_GET['sinceId']) ? (int)$_GET['sinceId'] : 0;

$postsInstance = new Posts();
$allPosts = $postsInstance->getAllPosts($_SESSION['user_id'], $sinceId);

ApiResponse::sendJson($allPosts);