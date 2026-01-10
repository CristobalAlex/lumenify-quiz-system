<?php
// api/quiz/get_questions.php
session_start();
include '../../config/dbconfig.php';
header('Content-Type: application/json');
if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No Quiz ID provided']);
    exit;
}
try {
    $stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ?");
    $stmt->execute([$_GET['id']]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $questions]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>