<?php
session_start();
ini_set('display_errors', 0); 
error_reporting(E_ALL);
include '../../config/dbconfig.php';
header('Content-Type: application/json');
if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please login.']);
    exit;
}
$userId = $_SESSION['user_id'];
$title = $_POST['title'] ?? 'Untitled Quiz';
$description = $_POST['description'] ?? '';
$mode = $_POST['mode'] ?? 'manual';

try {
    $pdo->beginTransaction();
    $filePath = null;
    $fileType = 'manual';
    if ($mode === 'upload' && isset($_FILES['quizFile']) && $_FILES['quizFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['quizFile']['tmp_name'];
        $fileName = $_FILES['quizFile']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        if (!in_array($fileExtension, ['pdf', 'pptx'])) {
            throw new Exception('Invalid file type. Only PDF and PPTX allowed.');
        }
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }
        $dest_path = $uploadFileDir . $newFileName;
        
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $filePath = './uploads/' . $newFileName;
            $fileType = $fileExtension;
        } else {
            throw new Exception('Failed to move uploaded file.');
        }
    }
    $sql = "INSERT INTO quizzes (user_id, title, description, file_path, file_type) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $title, $description, $filePath, $fileType]);

    $quizId = $pdo->lastInsertId(); 
    $questionsJson = $_POST['questions'] ?? '[]'; 
    $questions = json_decode($questionsJson, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid questions data format.');
    }
    if (!empty($questions)) {
        $sqlQ = "INSERT INTO quiz_questions 
                 (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $qStmt = $pdo->prepare($sqlQ);

        foreach ($questions as $q) {
            $text = trim($q['text'] ?? '');
            
            if (empty($text)) continue;
            $optA = trim($q['options']['A'] ?? '');
            $optB = trim($q['options']['B'] ?? '');
            $optC = trim($q['options']['C'] ?? '');
            $optD = trim($q['options']['D'] ?? '');
            
            $correct = trim($q['correct'] ?? '');
            $qStmt->execute([
                $quizId, 
                $text, 
                $optA, 
                $optB, 
                $optC, 
                $optD, 
                $correct
            ]);
        }
    }
    $pdo->commit();
    echo json_encode([
        'status' => 'success', 
        'message' => 'Quiz created successfully',
        'quiz_id' => $quizId
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if (isset($dest_path) && file_exists($dest_path)) {
        unlink($dest_path);
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>