<?php
/**
 * Lucky Puffin - Database Configuration
 * Update these values with your Hostinger credentials
 */

define('DB_HOST', 'localhost');  // Usually 'localhost' for Hostinger
define('DB_NAME', '');
define('DB_USER', '');  // Replace with your MySQL username
define('DB_PASS', '');  // Replace with your MySQL password
define('DB_CHARSET', 'utf8mb4');

// Error reporting (set to 0 in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Timezone
date_default_timezone_set('UTC');

// CORS settings (adjust for production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
