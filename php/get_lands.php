<?php
require_once 'config.php';
requireLogin();

$role   = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$landId = isset($_GET['id']) ? intval($_GET['id']) : null;

// Single land lookup
if ($landId) {
    $stmt = $conn->prepare("SELECT l.*, u.name AS seller_name, u.email AS seller_email, u.phone AS seller_phone
                            FROM lands l LEFT JOIN users u ON l.seller_id = u.id
                            WHERE l.id = ?");
    $stmt->bind_param("i", $landId);
    $stmt->execute();
    $land = $stmt->get_result()->fetch_assoc();
    if (!$land) {
        jsonResponse(['success' => false, 'message' => 'Land not found.']);
    }
    jsonResponse(['success' => true, 'land' => $land]);
}

// List lands - filter based on role
if ($role === 'seller') {
    $stmt = $conn->prepare("SELECT l.*, u.name AS seller_name FROM lands l
                            LEFT JOIN users u ON l.seller_id = u.id
                            WHERE l.seller_id = ? ORDER BY l.created_at DESC");
    $stmt->bind_param("i", $userId);
} elseif ($role === 'buyer') {
    $stmt = $conn->prepare("SELECT l.*, u.name AS seller_name FROM lands l
                            LEFT JOIN users u ON l.seller_id = u.id
                            WHERE l.status = 'valued' ORDER BY l.created_at DESC");
} else {
    // authority and admin see everything
    $stmt = $conn->prepare("SELECT l.*, u.name AS seller_name FROM lands l
                            LEFT JOIN users u ON l.seller_id = u.id
                            ORDER BY l.created_at DESC");
}

$stmt->execute();
$result = $stmt->get_result();
$lands = [];
while ($row = $result->fetch_assoc()) {
    $lands[] = $row;
}

jsonResponse(['success' => true, 'lands' => $lands]);
?>
