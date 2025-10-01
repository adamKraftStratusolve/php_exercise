<?php
// --- Postman API Endpoint ---
session_start();
session_unset();
session_destroy();

// --- Postman API Response ---
header('Content-Type: application/json');
echo json_encode(['success' => 'User logged out successfully.']);
exit();