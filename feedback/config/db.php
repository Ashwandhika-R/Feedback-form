<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_feedback_system');

// Create connection
function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Start secure session
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        session_start();
    }
}

// Check admin login
function requireAdminLogin() {
    startSecureSession();
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ' . getAdminBase() . 'login.php');
        exit();
    }
}

function getAdminBase() {
    // Works from both /admin/ and root
    return __DIR__ . '/../admin/';
}

// Sanitize output
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Generate acknowledgement number
function generateAckNo() {
    return 'ACK' . date('Y') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}
?>
