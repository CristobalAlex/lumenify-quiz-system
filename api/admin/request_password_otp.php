<?php
session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');
include '../../config/dbconfig.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$adminId = $_SESSION['admin_id'];
try {
    $stmt = $pdo->prepare("SELECT email FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        throw new Exception("Admin account not found.");
    }
    $email = $admin['email'];
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date("Y-m-d H:i:s", strtotime('+10 minutes'));
    $updateStmt = $pdo->prepare("UPDATE admins SET reset_token = ?, reset_expires = ? WHERE id = ?");
    $updateStmt->execute([$otp, $expires, $adminId]);
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'alecferry6@gmail.com';
    $mail->Password   = 'hfctbonaxwnwvlyt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('alecferry6@gmail.com', 'Lumenify Admin Security');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Admin Verification Code';
    $mail->Body    = "
        <div style='font-family: sans-serif; text-align: center; padding: 20px; background-color: #f8fafc; border-radius: 10px;'>
            <h2 style='color: #1e293b;'>Admin Security Verification</h2>
            <p style='color: #64748b;'>You requested to change your password. Use the code below to verify your identity.</p>
            <div style='margin: 30px 0;'>
                <span style='background: #ffffff; color: #4f46e5; padding: 15px 30px; font-size: 24px; font-weight: bold; letter-spacing: 5px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);'>
                    $otp
                </span>
            </div>
            <p style='font-size: 12px; color: #94a3b8;'>This code expires in 10 minutes.</p>
        </div>
    ";

    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Verification code sent to ' . $email]);
} catch (Exception $e) {
    error_log("Mailer Error: " . $mail->ErrorInfo);
    echo json_encode(['status' => 'error', 'message' => 'Could not send email. Please check server logs.']);
}
?>