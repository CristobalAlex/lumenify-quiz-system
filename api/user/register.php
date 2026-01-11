<?php
include '../../config/dbconfig.php';
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"));
if (!empty($data->username) && !empty($data->password) && !empty($data->email) && !empty($data->code)) {
    $username = trim($data->username);
    $email = trim($data->email);
    $password = $data->password;
    $code = trim($data->code);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "User already exists."]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT code, expires_at FROM verification_codes WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "No verification code found. Please request one."]);
        exit;
    }
    if ($row['code'] !== $code) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid verification code."]);
        exit;
    }

    if (strtotime($row['expires_at']) < time()) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Verification code expired."]);
        exit;
    }
    $hashed_pass = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $email, $hashed_pass])) {
        $pdo->prepare("DELETE FROM verification_codes WHERE email = ?")->execute([$email]);
        echo json_encode(["status" => "success", "message" => "User registered successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to register user."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "All fields including verification code are required."]);
}
?>