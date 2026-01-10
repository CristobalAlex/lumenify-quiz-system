<?php
session_start();
include '../../config/dbconfig.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
$userId = $_SESSION['user_id'];
try {
    $sql = "SELECT 
                q.*,
                (SELECT score FROM quiz_attempts WHERE quiz_id = q.id AND user_id = ? ORDER BY attempted_at DESC LIMIT 1) as last_score,
                (SELECT total_questions FROM quiz_attempts WHERE quiz_id = q.id AND user_id = ? ORDER BY attempted_at DESC LIMIT 1) as last_total,
                (SELECT attempted_at FROM quiz_attempts WHERE quiz_id = q.id AND user_id = ? ORDER BY attempted_at DESC LIMIT 1) as last_attempt_date
            FROM quizzes q 
            WHERE q.user_id = ? 
            ORDER BY q.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $userId, $userId, $userId]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $quizzes]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>