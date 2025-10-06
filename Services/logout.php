<?php
require_once __DIR__ . '/cors_config.php';
require_once 'api_helpers.php';

session_start();
session_unset();
session_destroy();

ApiResponse::success('User logged out successfully.');