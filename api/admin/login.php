<?php
session_start();
include '../../config/dbconfig.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter both username and password.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['is_admin'] = true;
        echo json_encode([
            'status' => 'success', 
            'message' => 'Login successful',
            'redirect' => 'dashboard.php'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
    }

} catch (PDOException $e) {
    error_log("Admin Login Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
}
?>