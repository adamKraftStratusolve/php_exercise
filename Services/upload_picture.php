<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';
require_once 'api_helpers.php';

ApiResponse::requirePostMethod();

$userId = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$base64Image = $input['profilePicture'] ?? null;

if (empty($base64Image)) {
    ApiResponse::error('No image data provided.');
}

if (strpos($base64Image, 'data:image/') !== 0) {
    ApiResponse::error('Invalid image data format.');
}

$user = new Users();
if ($user->updateProfilePicture($userId, $base64Image)) {
    ApiResponse::sendJson(['success' => 'Profile picture updated.', 'imageUrl' => $base64Image]);
} else {
    ApiResponse::error('Failed to update profile picture in the database.', 500);
}