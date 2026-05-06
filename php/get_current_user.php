<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'message' => 'Not logged in.']);
}

$stmt = $conn->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    jsonResponse(['success' => false, 'message' => 'User not found.']);
}

jsonResponse(['success' => true, 'user' => $user]);
?>
