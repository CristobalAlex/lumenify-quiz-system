<?php
include '../../config/api.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$text = $_POST['extractedText'] ?? '';
$qCount = isset($_POST['questionCount']) ? intval($_POST['questionCount']) : 5;
$quizType = $_POST['quizType'] ?? 'multiple_choice';

if (empty($text) || strlen($text) < 50) {
    echo json_encode(['status' => 'error', 'message' => 'No text content received or text is too short.']);
    exit;
}
if ($qCount < 1) $qCount = 1;
if ($qCount > 25) $qCount = 25; 

$text = substr($text, 0, 30000);
$apiKey = GEMINI_API_KEY;
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$apiKey";

$typeInstruction = "";
if ($quizType === 'identification') {
    $typeInstruction = "Generate strictly IDENTIFICATION (fill-in-the-blank) type questions. The 'options' field should be empty strings. The 'correct' field must contain the text answer.";
} elseif ($quizType === 'mixed') {
    $typeInstruction = "Generate a mix of MULTIPLE CHOICE and IDENTIFICATION questions.";
} else {
    $typeInstruction = "Generate strictly MULTIPLE CHOICE questions with 4 options (A, B, C, D).";
}

$prompt = "
Analyze the text below and detect its language. Generate $qCount questions in that SAME language.
$typeInstruction

Return a JSON array of objects:
{
    \"type\": \"MCQ\" or \"IDENT\",
    \"text\": \"Question text\",
    \"options\": { \"A\": \"\", \"B\": \"\", \"C\": \"\", \"D\": \"\" },
    \"correct\": \"A/B/C/D for MCQ, or the string answer for IDENT\"
}

Text: $text
";

$data = [
    "contents" => [["parts" => [["text" => $prompt]]]],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 8192,
        "response_mime_type" => "application/json"
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    curl_close($ch);
    echo json_encode(['status' => 'error', 'message' => 'Connection Error: ' . $error_msg]);
    exit;
}
curl_close($ch);
$result = json_decode($response, true);
if (isset($result['error'])) {
    echo json_encode(['status' => 'error', 'message' => 'Gemini API Error: ' . $result['error']['message']]);
    exit;
}
if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $rawAiText = trim($result['candidates'][0]['content']['parts'][0]['text']);
    $quizData = json_decode($rawAiText, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode(['status' => 'success', 'data' => $quizData]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'AI returned invalid JSON structure.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unexpected API Response format.']);
}
?>