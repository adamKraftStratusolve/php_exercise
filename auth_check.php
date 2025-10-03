<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/Services/api_helpers.php';

if (!isset($_SESSION['user_id'])) {
    ApiResponse::error('User not authenticated.', 401);
}