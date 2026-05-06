<?php
require_once 'config.php';
requireLogin();

$role   = $_SESSION['role'];
$userId = $_SESSION['user_id'];

if ($role === 'buyer') {
    $stmt = $conn->prepare("SELECT p.*, l.title AS land_title, l.plot_number, l.location
                            FROM payments p LEFT JOIN lands l ON p.land_id = l.id
                            WHERE p.buyer_id = ? ORDER BY p.payment_date DESC");
    $stmt->bind_param("i", $userId);
} elseif ($role === 'seller') {
    $stmt = $conn->prepare("SELECT p.*, l.title AS land_title, l.plot_number, l.location, u.name AS buyer_name
                            FROM payments p
                            LEFT JOIN lands l ON p.land_id = l.id
                            LEFT JOIN users u ON p.buyer_id = u.id
                            WHERE p.seller_id = ? ORDER BY p.payment_date DESC");
    $stmt->bind_param("i", $userId);
} else {
    // admin & authority see all
    $stmt = $conn->prepare("SELECT p.*, l.title AS land_title, l.plot_number, l.location,
                                   b.name AS buyer_name, s.name AS seller_name
                            FROM payments p
                            LEFT JOIN lands l ON p.land_id = l.id
                            LEFT JOIN users b ON p.buyer_id = b.id
                            LEFT JOIN users s ON p.seller_id = s.id
                            ORDER BY p.payment_date DESC");
}

$stmt->execute();
$result = $stmt->get_result();
$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

jsonResponse(['success' => true, 'payments' => $payments]);
?>
