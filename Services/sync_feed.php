<?php
require_once __DIR__ . '/cors_config.php';
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Posts.php';
require_once '../Services/api_helpers.php';

ApiResponse::requirePostMethod();

$input = json_decode(file_get_contents('php://input'), true);
$sinceId = $input['sinceId'] ?? 0;
$existingIds = $input['existingIds'] ?? [];
$currentUserId = $_SESSION['user_id'];

$postsInstance = new Posts();

$newPosts = $postsInstance->getAllPosts($currentUserId, $sinceId);

$updates = $postsInstance->getPostUpdates($existingIds, $currentUserId);

$response_data = [
    'newPosts' => $newPosts,
    'updates' => $updates
];

ApiResponse::sendJson($response_data);