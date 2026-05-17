<?php
/* ============================================================
   DATABASE CONFIGURATION
   ============================================================ */

define('DB_HOST', 'localhost');
define('DB_USER', 'appuser');
define('DB_PASS', 'StrongPassword123!');
define('DB_NAME', 'lvms_db');


/* ============================================================
   DATABASE CONNECTION
   ============================================================ */

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset("utf8mb4");


/* ============================================================
   ADMIN SEEDER
   Creates default admin automatically
   ============================================================ */

// Default admin data
$defaultAdminName = "ASHA OTHMAN";
$defaultAdminEmail = "ashaothman282@gmail.com";
$defaultAdminPassword = "asha12345";

// Check if admin already exists
$checkAdmin = $conn->prepare("
    SELECT id FROM users WHERE email = ?
");

$checkAdmin->bind_param("s", $defaultAdminEmail);
$checkAdmin->execute();

$result = $checkAdmin->get_result();

// If admin does not exist
if ($result->num_rows === 0) {

    // Hash password
    $hashedPassword = password_hash(
        $defaultAdminPassword,
        PASSWORD_DEFAULT
    );

    // Insert admin
    $insertAdmin = $conn->prepare("
        INSERT INTO users (name, email, password, role)
        VALUES (?, ?, ?, 'admin')
    ");

    $insertAdmin->bind_param(
        "sss",
        $defaultAdminName,
        $defaultAdminEmail,
        $hashedPassword
    );

    if ($insertAdmin->execute()) {
        error_log("Default admin created successfully.");
    } else {
        error_log("Failed to create default admin.");
    }
}


/* ============================================================
   START SESSION
   ============================================================ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* ============================================================
   HELPER FUNCTIONS
   ============================================================ */

/* ----------------------------------------
   Send JSON response
---------------------------------------- */
function jsonResponse($data, $code = 200) {

    http_response_code($code);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode($data);

    exit;
}


/* ----------------------------------------
   Require Login
---------------------------------------- */
function requireLogin() {

    if (!isset($_SESSION['user_id'])) {

        jsonResponse([
            'success' => false,
            'message' => 'You must be logged in.'
        ], 401);
    }
}


/* ----------------------------------------
   Require Specific Role
---------------------------------------- */
function requireRole($role) {

    requireLogin();

    if ($_SESSION['role'] !== $role) {

        jsonResponse([
            'success' => false,
            'message' => 'Access denied.'
        ], 403);
    }
}


/* ----------------------------------------
   Read JSON or POST input
---------------------------------------- */
function getInput() {

    $raw = file_get_contents('php://input');

    $json = json_decode($raw, true);

    return $json ?: $_POST;
}

?>