<?php
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';

$userId = $_GET['user_id'] ?? 0;

if (empty($userId)) {
    http_response_code(400);
    exit('User ID is required.');
}

$userInstance = new Users();
$userInstance->personId = $userId;
$user = $userInstance->findById();

if (!$user || empty($user->profileImageUrl)) {
    http_response_code(404);
    exit;
}

$base64String = $user->profileImageUrl;

if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
    $data = substr($base64String, strpos($base64String, ',') + 1);
    $type = strtolower($type[1]);

    $data = base64_decode($data);

    if ($data === false) {
        http_response_code(500);
        exit('Base64 decode failed.');
    }

    // Serve the image with the correct header
    header('Content-Type: image/'.$type);
    echo $data;
    exit;
} else {
    http_response_code(500);
    exit('Invalid Data URL format.');
}