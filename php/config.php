<?php
/* ============================================================
   DATABASE CONFIGURATION
   Edit these values to match your XAMPP/WAMP setup
   ============================================================ */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Default for XAMPP
define('DB_PASS', '');             // Default for XAMPP (empty)
define('DB_NAME', 'lvms_db');

// Establish connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset("utf8mb4");

// Start session for login persistence
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ----------------------------------------
   Helper: send JSON response and exit
---------------------------------------- */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/* ----------------------------------------
   Helper: require login (any role)
---------------------------------------- */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['success' => false, 'message' => 'You must be logged in.'], 401);
    }
}

/* ----------------------------------------
   Helper: require specific role
---------------------------------------- */
function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        jsonResponse(['success' => false, 'message' => 'Access denied.'], 403);
    }
}

/* ----------------------------------------
   Helper: read JSON or POST input
---------------------------------------- */
function getInput() {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    return $json ?: $_POST;
}
?>
