<?php
require_once __DIR__ . '/Services/cors_config.php';
require_once 'auth_check.php';
require_once 'db_config.php';
require_once './Model_Repositories/Posts.php';
require_once './Services/api_helpers.php';

$postsPerPage = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;


$postsInstance = new Posts();
$allPosts = $postsInstance->getAllPosts($_SESSION['user_id'], $postsPerPage, $offset);

ApiResponse::sendJson($allPosts);