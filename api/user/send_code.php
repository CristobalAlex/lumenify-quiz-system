<?php
include '../../config/dbconfig.php';
require '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->username)) {
    $email = trim($data->email);
    $username = trim($data->username);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Username is already taken."]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Email is already registered."]);
        exit;
    }

    $code = rand(100000, 999999);
    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $stmt = $pdo->prepare("REPLACE INTO verification_codes (email, code, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $code, $expiry]);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'alecferry6@gmail.com';
        $mail->Password   = 'hfctbonaxwnwvlyt';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('your-email@gmail.com', 'Lumenify');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "
         <div style='font-family: sans-serif; text-align: center; padding: 20px;'>
            <h2>Email Confirmation</h2>
            <p>Use the code below to verify your email before registration. This code will expire in 10 minutes.</p>
            <h1 style='background: #eee; display: inline-block; padding: 10px 20px; letter-spacing: 5px; border-radius: 8px;'>$code</h1>
        </div>";
        $mail->send();
        echo json_encode(["status" => "success", "message" => "Verification code sent to email."]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Could not send email. Mailer Error."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Email and Username required."]);
}
?>