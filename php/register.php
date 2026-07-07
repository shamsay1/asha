<?php
require_once 'config.php';

$input = getInput();
$name     = trim($input['name']     ?? '');
$email    = trim(strtolower($input['email'] ?? ''));
$phone    = trim($input['phone']    ?? '');
$password = $input['password'] ?? '';
$role     = $input['role']     ?? '';

// Validation
if (!$name || !$email || !$phone || !$password || !$role) {
    jsonResponse(['success' => false, 'message' => 'All fields are required.']);
}

// CRITICAL: prevent self-registration as authority or admin
if ($role !== 'seller' && $role !== 'buyer') {
    jsonResponse(['success' => false, 'message' => 'Only seller and buyer accounts can be self-registered. Please contact an administrator for other roles.']);
}

// Check if email already exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    jsonResponse(['success' => false, 'message' => 'Email already registered.']);
}

// Hash password and insert
$hashed = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $email, $hashed, $phone, $role);

if ($stmt->execute()) {
    jsonResponse([
        'success' => true,
        'redirect' => 'index.html'
    ]);
} else {
    jsonResponse([
        'success' => false,
        'message' => 'Registration failed: ' . $stmt->error
    ]);
}
?>
