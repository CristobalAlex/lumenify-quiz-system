<?php
session_start();
include '../../config/dbconfig.php';
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$quizId = $_GET['quiz_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? ORDER BY attempted_at DESC");
    $stmt->execute([$userId, $quizId]);
    $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $attempts]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>