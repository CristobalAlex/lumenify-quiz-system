<?php
session_start();
header('Content-Type: application/json; charset=UTF-8'); 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
include '../../config/dbconfig.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->password)){
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$data->username]);
    $user = $stmt->fetch();

    if(!$user){
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "No user found with that username."]);
        exit();
    }
    if(password_verify($data->password, $user['password'])){
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['logged_in'] = true;
        
        echo json_encode([
            "status" => "success",
            "message" => "Login successful!", // Added for your alert()
            "redirect" => "../../views-js/user/main.php"
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid password"]);
    }
} else {
    http_response_code(400); // Changed to 400 Bad Request
    echo json_encode(["status" => "error", "message" => "Missing username or password"]);
}
?>