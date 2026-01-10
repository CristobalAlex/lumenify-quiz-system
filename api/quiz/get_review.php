<?php
session_start();
include '../../config/dbconfig.php';
header('Content-Type: application/json');
if (!isset($_GET['attempt_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing Attempt ID']);
    exit;
}

$attemptId = $_GET['attempt_id'];

try {
    $sql = "SELECT 
                q.id as question_id,
                q.question_text,
                q.option_a,
                q.option_b,
                q.option_c,
                q.option_d,
                q.correct_option,
                COALESCE(a.user_answer, '') as user_answer
            FROM quiz_attempts att
            JOIN quiz_questions q ON att.quiz_id = q.quiz_id
            LEFT JOIN quiz_attempt_answers a ON q.id = a.question_id AND a.attempt_id = att.id
            WHERE att.id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$attemptId]);
    $reviewData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $reviewData]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>