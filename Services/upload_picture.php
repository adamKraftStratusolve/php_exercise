<?php
require_once __DIR__ . '/cors_config.php';
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../Model_Repositories/Users.php';
require_once 'api_helpers.php';

ApiResponse::requirePostMethod();

if (!isset($_FILES['profilePicture'])) {
    ApiResponse::error('No file uploaded.');
}

$file = $_FILES['profilePicture'];
$userId = $_SESSION['user_id'];


$maxFileSize = 2 * 1024 * 1024;
if ($file['size'] > $maxFileSize) {
    ApiResponse::error('File is too large. Limit is 2MB.');
}

$imageInfo = getimagesize($file['tmp_name']);
if ($imageInfo === false) {
    ApiResponse::error('Invalid image file.');
}
$mime = $imageInfo['mime'];

$sourceImage = null;
switch ($mime) {
    case 'image/jpeg':
        $sourceImage = imagecreatefromjpeg($file['tmp_name']);
        break;
    case 'image/png':
        $sourceImage = imagecreatefrompng($file['tmp_name']);
        break;
    case 'image/gif':
        $sourceImage = imagecreatefromgif($file['tmp_name']);
        break;
    default:
        ApiResponse::error('Unsupported image type. Please use JPG, PNG, or GIF.');
}

if ($sourceImage === false) {
    ApiResponse::error('Could not process the image file.');
}

$width = imagesx($sourceImage);
$height = imagesy($sourceImage);
$targetSize = 300;
$resizedImage = imagecreatetruecolor($targetSize, $targetSize);

if ($mime == 'image/png' || $mime == 'image/gif') {
    imagealphablending($resizedImage, false);
    imagesavealpha($resizedImage, true);
    $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
    imagefilledrectangle($resizedImage, 0, 0, $targetSize, $targetSize, $transparent);
}

imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $targetSize, $targetSize, $width, $height);

$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
$uniqueFilename = $userId . '-' . uniqid() . '.jpg';
$uploadPath = '../Uploads/' . $uniqueFilename;

if (imagejpeg($resizedImage, $uploadPath, 90)) {

    $userInstance = new Users();
    $imageUrl = '/Uploads/' . $uniqueFilename;

    if ($userInstance->updateProfilePicture($userId, $imageUrl)) {
        ApiResponse::sendJson(['success' => true, 'imageUrl' => $imageUrl]);
    } else {
        ApiResponse::error('Failed to update profile picture in database.', 500);
    }
} else {
    ApiResponse::error('Failed to save resized image.', 500);
}

imagedestroy($sourceImage);
imagedestroy($resizedImage);