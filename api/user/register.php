<?php
include '../../config/dbconfig.php';
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"));
if (!empty($data->username) && !empty($data->password) && !empty($data->email)) {
    $username = trim($data->username);
    $email = trim($data->email);
    $password = $data->password;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Username already taken."]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Email already registered."]);
        exit;
    }
    $hashed_pass = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $email, $hashed_pass])) {
        echo json_encode(["status" => "success", "message" => "User registered successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to register user."]);
    }

} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
}
?>
