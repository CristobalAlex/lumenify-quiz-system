<?php
// api/quiz/dashboard.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/dbconfig.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
$userId = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quizzes WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalQuizzes = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT quiz_id) FROM quiz_attempts WHERE user_id = ?");
    $stmt->execute([$userId]);
    $uniqueCompleted = $stmt->fetchColumn();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_quizzes' => $totalQuizzes,
            'total_attempts' => $uniqueCompleted
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>