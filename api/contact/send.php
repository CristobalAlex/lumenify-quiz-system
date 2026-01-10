<?php
// api/contact/send.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
require '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit();
}

$name = htmlspecialchars(strip_tags($data['name']));
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$message = htmlspecialchars(strip_tags($data['message']));

if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit();
}

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'alecferry6@gmail.com';
    $mail->Password   = 'hfctbonaxwnwvlyt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->setFrom('alecferry6@gmail.com', 'Lumenify Contact Form');
    
    $mail->addAddress('alecferry6@gmail.com'); 
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
    $mail->Subject = "New Inquiry from: $name";
    $mail->Body    = "
        <h3>New Contact Message</h3>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <br>
        <p><strong>Message:</strong></p>
        <p style='background:#f3f4f6; padding:15px; border-radius:5px;'>$message</p>
    ";
    $mail->send();
    echo json_encode(["status" => "success", "message" => "Message sent!"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Mailer Error: " . $mail->ErrorInfo]);
}
?>