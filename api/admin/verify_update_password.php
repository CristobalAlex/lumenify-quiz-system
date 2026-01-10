<?php
session_start();
include '../../config/dbconfig.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) exit(json_encode(['status'=>'error', 'message'=>'Unauthorized']));

$data = json_decode(file_get_contents("php://input"), true);
$otp = $data['otp'] ?? '';
$newPassword = $data['new_password'] ?? '';

if (strlen($otp) !== 6 || strlen($newPassword) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP or password too short.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE id = ? AND reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$_SESSION['admin_id'], $otp]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired code.']);
        exit;
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $update = $pdo->prepare("UPDATE admins SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
    $update->execute([$newHash, $_SESSION['admin_id']]);

    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>