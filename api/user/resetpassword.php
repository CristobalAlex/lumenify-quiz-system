<?php
date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json; charset=UTF-8');
include '../../config/dbconfig.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$data = json_decode(file_get_contents("php://input"));
if (empty($data->email) || empty($data->token) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required information."]);
    exit;
}
$email = $data->email;
$userInputCode = $data->token;
$newPassword = $data->password;
$currentTime = date("Y-m-d H:i:s");
try {
    $stmt = $pdo->prepare("SELECT id, reset_token, reset_expires FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !$user['reset_token'] || !password_verify($userInputCode, $user['reset_token']) || $currentTime > $user['reset_expires']) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid or expired reset code."]);
        exit;
    }
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare(
        "UPDATE users 
         SET password = ?, reset_token = NULL, reset_expires = NULL 
         WHERE id = ?"
    );

    if ($stmt->execute([$hashedPassword, $user['id']])) {
        echo json_encode([
            "status" => "success", 
            "message" => "Password updated successfully."
        ]);
    } else {
        throw new Exception();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error."]);
}
?>