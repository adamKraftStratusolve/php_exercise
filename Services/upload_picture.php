<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';
require_once 'api_helpers.php';

ApiResponse::requirePostMethod();

if (isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];

    // Basic validation
    if ($file['error'] !== UPLOAD_ERR_OK) {
        ApiResponse::error('File upload error.');
    }
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        ApiResponse::error('Invalid file type. Please upload a JPG, PNG, or GIF.');
    }
    if ($file['size'] > 5000000) { // 5 MB limit
        ApiResponse::error('File is too large. Limit is 5MB.');
    }

    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueFilename = $_SESSION['user_id'] . '-' . uniqid() . '.' . $fileExtension;
    $uploadPath = '../Uploads/' . $uniqueFilename;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $userInstance = new Users();
        $imageUrl = '/Uploads/' . $uniqueFilename;

        if ($userInstance->updateProfilePicture($_SESSION['user_id'], $imageUrl)) {
            ApiResponse::sendJson(['success' => true, 'imageUrl' => $imageUrl]);
        } else {
            ApiResponse::error('Failed to update profile picture in database.', 500);
        }
    } else {
        ApiResponse::error('Failed to save uploaded file.', 500);
    }
} else {
    ApiResponse::error('No file uploaded.');
}