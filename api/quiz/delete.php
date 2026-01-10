<?php
// api/quiz/delete.php
session_start();
include '../../config/dbconfig.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
    exit;
}
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
$data = json_decode(file_get_contents("php://input"), true);
$quizId = $data['id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$quizId) {
    echo json_encode(['status' => 'error', 'message' => 'Quiz ID required']);
    exit;
}
try {
    $check = $pdo->prepare("SELECT id, file_path, file_type FROM quizzes WHERE id = ? AND user_id = ?");
    $check->execute([$quizId, $userId]);
    $quiz = $check->fetch();
    if (!$quiz) {
        echo json_encode(['status' => 'error', 'message' => 'Quiz not found or access denied']);
        exit;
    }
    if (($quiz['file_type'] === 'pdf' || $quiz['file_type'] === 'pptx') && file_exists($quiz['file_path'])) {
        unlink($quiz['file_path']);
    }
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->execute([$quizId]);
    echo json_encode(['status' => 'success', 'message' => 'Quiz deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>