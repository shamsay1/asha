<?php
require_once 'config.php';

$input = getInput();
$email = trim(strtolower($input['email'] ?? ''));
$password = $input['password'] ?? '';

if (!$email || !$password) {
    jsonResponse(['success' => false, 'message' => 'Email and password are required.']);
}

$stmt = $conn->prepare("SELECT id, name, email, password, phone, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid email or password.']);
}

// Save session
$_SESSION['user_id'] = $user['id'];
$_SESSION['name']    = $user['name'];
$_SESSION['email']   = $user['email'];
$_SESSION['role']    = $user['role'];

// Don't return the password hash
unset($user['password']);

jsonResponse(['success' => true, 'user' => $user]);
?>
