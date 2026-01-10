<?php
session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../../config/dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->otp) || empty($data->new_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
    exit;
}

$userId = $_SESSION['user_id'];
$inputCode = $data->otp;
$newPass = $data->new_password;

try {
    $stmt = $pdo->prepare("SELECT reset_token, reset_expires FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || empty($user['reset_token'])) {
        echo json_encode(['status' => 'error', 'message' => 'No active request found.']);
        exit;
    }

    if (strtotime($user['reset_expires']) < time()) {
        echo json_encode(['status' => 'error', 'message' => 'Code expired.']);
        exit;
    }

    if (!password_verify($inputCode, $user['reset_token'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid code.']);
        exit;
    }
    $newHash = password_hash($newPass, PASSWORD_BCRYPT);
    $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
    $update->execute([$newHash, $userId]);

    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully!']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server Error']);
}
?>