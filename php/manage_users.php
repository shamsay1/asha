<?php
require_once 'config.php';
requireRole('admin');

$method = $_SERVER['REQUEST_METHOD'];

// LIST users
if ($method === 'GET') {
    $result = $conn->query("SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    jsonResponse(['success' => true, 'users' => $users]);
}

$input = getInput();
$action = $input['action'] ?? '';

// ADD user (any role - admin can create authority/admin)
if ($action === 'add') {
    $name     = trim($input['name']     ?? '');
    $email    = trim(strtolower($input['email'] ?? ''));
    $phone    = trim($input['phone']    ?? '');
    $password = $input['password'] ?? '';
    $role     = $input['role']     ?? '';

    if (!$name || !$email || !$phone || !$password || !$role) {
        jsonResponse(['success' => false, 'message' => 'All fields are required.']);
    }
    if (!in_array($role, ['seller','buyer','authority','admin'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid role.']);
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        jsonResponse(['success' => false, 'message' => 'A user with this email already exists.']);
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $hashed, $phone, $role);

    if ($stmt->execute()) {
        jsonResponse(['success' => true, 'user_id' => $conn->insert_id]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to add user: ' . $stmt->error]);
    }
}
// UPDATE user
if ($action === 'update') {

    $id    = intval($input['id'] ?? 0);
    $name  = trim($input['name'] ?? '');
    $email = trim(strtolower($input['email'] ?? ''));
    $phone = trim($input['phone'] ?? '');
    $role  = trim($input['role'] ?? '');

    if (!$id || !$name || !$email || !$phone || !$role) {
        jsonResponse([
            'success' => false,
            'message' => 'All fields are required.'
        ]);
    }

    if (!in_array($role, ['seller', 'buyer', 'authority', 'admin'])) {
        jsonResponse([
            'success' => false,
            'message' => 'Invalid role.'
        ]);
    }

    // Hakikisha email haitumiki na user mwingine
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->bind_param("si", $email, $id);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        jsonResponse([
            'success' => false,
            'message' => 'Email already exists.'
        ]);
    }

    $stmt = $conn->prepare("
        UPDATE users
        SET name = ?, email = ?, phone = ?, role = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssssi",
        $name,
        $email,
        $phone,
        $role,
        $id
    );

    if ($stmt->execute()) {
        jsonResponse([
            'success' => true,
            'message' => 'User updated successfully.'
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'Failed to update user: ' . $stmt->error
        ]);
    }
}
// DELETE user
if ($action === 'delete') {
    $id = intval($input['id'] ?? 0);
    if (!$id) jsonResponse(['success' => false, 'message' => 'User ID required.']);
    if ($id === intval($_SESSION['user_id'])) {
        jsonResponse(['success' => false, 'message' => 'You cannot delete your own account.']);
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        jsonResponse(['success' => true]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to delete user.']);
    }
}

jsonResponse(['success' => false, 'message' => 'Invalid action.']);
?>
