<?php
session_start();
require_once __DIR__ . '/../utils/extractor.php'; 
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['quizFile'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}
$fileTmpPath = $_FILES['quizFile']['tmp_name'];
$fileName = $_FILES['quizFile']['name'];
$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

$text = TextExtractor::extract($fileTmpPath, $ext);
$text = substr($text, 0, 30000); 

if (strlen($text) < 50) {
    echo json_encode(['status' => 'error', 'message' => 'Could not extract enough text from file.']);
    exit;
}

$qCount = isset($_POST['questionCount']) ? intval($_POST['questionCount']) : 5;
$quizType = isset($_POST['quizType']) ? $_POST['quizType'] : 'multiple_choice';

if ($qCount < 1) $qCount = 1;
if ($qCount > 20) $qCount = 20;

$apiKey = "AIzaSyCr8C68-JPkqQzlz6aVvygAgWv-mGqPneA";
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$apiKey";


$typeInstruction = "";
if ($quizType === 'identification') {
    $typeInstruction = "Generate strictly IDENTIFICATION (fill-in-the-blank) type questions. The 'options' field should be empty strings. The 'correct' field must contain the text answer.";
} elseif ($quizType === 'mixed') {
    $typeInstruction = "Generate a mix of MULTIPLE CHOICE and IDENTIFICATION questions (approx 50/50 split).";
} else {
    $typeInstruction = "Generate strictly MULTIPLE CHOICE questions with 4 options (A, B, C, D).";
}

$prompt = "
You are a professional quiz generator. 
1. **Analyze the text provided below.**
2. **Detect the language** of the text (e.g., Tagalog, English, Cebuano, etc.). **YOU MUST GENERATE THE QUESTIONS AND ANSWERS IN THE SAME LANGUAGE AS THE TEXT.** If the text is Tagalog, the questions must be Tagalog.
3. **Generate $qCount questions** based on the content.
4. $typeInstruction

Return the output strictly as a JSON array of objects. Do not use Markdown code blocks.

JSON Structure:
[
    {
        \"type\": \"MCQ\" (for multiple choice) or \"IDENT\" (for identification),
        \"text\": \"Question text here (in detected language)\",
        \"options\": {
            \"A\": \"Option A (or empty string if IDENT)\",
            \"B\": \"Option B (or empty string if IDENT)\",
            \"C\": \"Option C (or empty string if IDENT)\",
            \"D\": \"Option D (or empty string if IDENT)\"
        },
        \"correct\": \"Letter A/B/C/D if MCQ, or the direct text answer if IDENT\"
    }
]

Text to analyze:
$text
";

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
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
    $rawAiText = $result['candidates'][0]['content']['parts'][0]['text'];
    $rawAiText = str_replace(['```json', '```'], '', $rawAiText);
    
    $quizData = json_decode($rawAiText, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode(['status' => 'success', 'data' => $quizData]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'AI returned invalid JSON', 'raw' => $rawAiText]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unexpected API Response', 'debug' => $result]);
}
?>