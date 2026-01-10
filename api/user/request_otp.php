<?php
session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');
include '../../config/dbconfig.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("User not found.");
    }

    $email = $user['email'];

    $resetCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date("Y-m-d H:i:s", strtotime('+10 minutes'));
    $hashedCode = password_hash($resetCode, PASSWORD_BCRYPT);

    $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
    $updateStmt->execute([$hashedCode, $expires, $userId]);

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'alecferry6@gmail.com';
    $mail->Password = 'hfctbonaxwnwvlyt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('alecferry6@gmail.com', 'Lumenify Security');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Security Code for Password Change';
    $mail->Body = "
        <div style='font-family: sans-serif; text-align: center; padding: 20px;'>
            <h2>Password Reset</h2>
            <p>Use the code below to reset your password. This code will expire in 10 minutes.</p>
            <h1 style='background: #eee; display: inline-block; padding: 10px 20px; letter-spacing: 5px; border-radius: 8px;'>$resetCode</h1>
        </div>
    ";
    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Code sent to ' . $email]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Mailer Error: ' . $e->getMessage()]);
}
?>