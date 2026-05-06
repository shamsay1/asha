<?php
require_once 'config.php';
requireLogin();

$role   = $_SESSION['role'];
$userId = $_SESSION['user_id'];

$stats = [];

if ($role === 'seller') {
    $r = $conn->prepare("SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status='valued'  THEN 1 ELSE 0 END) AS valued,
        SUM(CASE WHEN status='sold'    THEN 1 ELSE 0 END) AS sold,
        COALESCE(SUM(valuation_amount),0) AS total_value
        FROM lands WHERE seller_id = ?");
    $r->bind_param("i", $userId);
    $r->execute();
    $stats = $r->get_result()->fetch_assoc();

    $r2 = $conn->prepare("SELECT COALESCE(SUM(amount),0) AS revenue FROM payments WHERE seller_id = ?");
    $r2->bind_param("i", $userId);
    $r2->execute();
    $stats['revenue'] = $r2->get_result()->fetch_assoc()['revenue'];
}

elseif ($role === 'buyer') {
    $stats['available'] = $conn->query("SELECT COUNT(*) AS c FROM lands WHERE status = 'valued'")
                               ->fetch_assoc()['c'];

    $r = $conn->prepare("SELECT COUNT(*) AS purchases, COALESCE(SUM(amount),0) AS spent
                         FROM payments WHERE buyer_id = ?");
    $r->bind_param("i", $userId);
    $r->execute();
    $row = $r->get_result()->fetch_assoc();
    $stats['purchases'] = $row['purchases'];
    $stats['spent']     = $row['spent'];
}

elseif ($role === 'authority') {
    $stats['pending']   = $conn->query("SELECT COUNT(*) AS c FROM lands WHERE status = 'pending'")
                               ->fetch_assoc()['c'];
    $stats['valued']    = $conn->query("SELECT COUNT(*) AS c FROM lands WHERE valuation_amount IS NOT NULL")
                               ->fetch_assoc()['c'];
    $stats['total_val'] = $conn->query("SELECT COALESCE(SUM(valuation_amount),0) AS s FROM lands")
                               ->fetch_assoc()['s'];
}

elseif ($role === 'admin') {
    $stats['users']        = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
    $stats['sellers']      = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='seller'")->fetch_assoc()['c'];
    $stats['buyers']       = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='buyer'")->fetch_assoc()['c'];
    $stats['authorities']  = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='authority'")->fetch_assoc()['c'];
    $stats['lands']        = $conn->query("SELECT COUNT(*) AS c FROM lands")->fetch_assoc()['c'];
    $stats['transactions'] = $conn->query("SELECT COUNT(*) AS c FROM payments")->fetch_assoc()['c'];
    $stats['revenue']      = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM payments")->fetch_assoc()['s'];
    $stats['pending']      = $conn->query("SELECT COUNT(*) AS c FROM lands WHERE status='pending'")->fetch_assoc()['c'];
    $stats['valued']       = $conn->query("SELECT COUNT(*) AS c FROM lands WHERE status='valued'")->fetch_assoc()['c'];
    $stats['sold']         = $conn->query("SELECT COUNT(*) AS c FROM lands WHERE status='sold'")->fetch_assoc()['c'];
}

jsonResponse(['success' => true, 'stats' => $stats]);
?>
