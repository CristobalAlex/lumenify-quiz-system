<?php
// api/quiz/get_full_quiz.php
session_start();
include '../../config/dbconfig.php';
header('Content-Type: application/json');
if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID missing']);
    exit;
}
try {
    $quizId = $_GET['id'];
    $qStmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
    $qStmt->execute([$quizId]);
    $quiz = $qStmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        echo json_encode(['status' => 'error', 'message' => 'Quiz not found']);
        exit;
    }
    $qtStmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ?");
    $qtStmt->execute([$quizId]);
    $questions = $qtStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success', 
        'data' => [
            'info' => $quiz,
            'questions' => $questions
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>