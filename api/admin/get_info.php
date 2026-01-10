<?php
session_start();
include '../../config/dbconfig.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT username, email, created_at FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo json_encode(['status' => 'success', 'data' => $admin]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Admin not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB Error']);
}
?>