<?php
session_start();
require_once __DIR__ . '/../../config/dbconfig.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $userStmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $userStmt->fetchColumn();
    $quizStmt = $pdo->query("SELECT COUNT(*) FROM quizzes");
    $totalQuizzes = $quizStmt->fetchColumn();
    echo json_encode([
        'status' => 'success',
        'data' => [
            'users' => $totalUsers,
            'quizzes' => $totalQuizzes
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>