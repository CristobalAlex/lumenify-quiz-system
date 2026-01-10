<?php
include '../../config/dbconfig.php';
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
$data = json_decode(file_get_contents("php://input"));
if (empty($data->token) || empty($data->email)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Email and verification code are required."]);
    exit;
}
try {
    $email = $data->email;
    $token = $data->token;

    $query = "SELECT reset_token, reset_expires FROM users WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !$user['reset_token']) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "No active reset request found."]);
        exit;
    }
    if (strtotime($user['reset_expires']) < time()) {
        echo json_encode(["status" => "error", "message" => "Code has expired."]);
        exit;
    }
    if (password_verify($token, $user['reset_token'])) {
        echo json_encode(["status" => "success", "message" => "Code verified."]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid verification code."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error."]);
}
?>