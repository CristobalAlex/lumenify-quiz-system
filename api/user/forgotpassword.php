<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
date_default_timezone_set('Asia/Manila');

include '../../config/dbconfig.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->email)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Email is required."]);
    exit;
}

$email = trim($data->email);

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Email not found."]);
    exit;
}

$resetCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date("Y-m-d H:i:s", strtotime('+10 minutes'));
$hashedCode = password_hash($resetCode, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
$stmt->execute([$hashedCode, $expires, $user['id']]);

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'alecferry6@gmail.com';
    $mail->Password = 'hfctbonaxwnwvlyt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('yourgmail@gmail.com', 'Lumenify Security');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your Password Reset Code';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; text-align: center;'>
            <h2>Password Reset</h2>
            <p>Use the code below to reset your password. This code will expire in 10 minutes.</p>
            <h1 style='background: #f4f4f4; padding: 20px; display: inline-block; letter-spacing: 5px; border-radius: 5px;'>
                $resetCode
            </h1>
            <p>If you did not request this, please ignore this email.</p>
        </div>
    ";

    $mail->send();

    echo json_encode([
        "status" => "success",
        "message" => "A 6-digit reset code has been sent to your email."
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Email could not be sent."
    ]);
}
?>