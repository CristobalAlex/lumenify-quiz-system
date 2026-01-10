<?php
session_start();
include '../../config/dbconfig.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
$data = json_decode(file_get_contents("php://input"), true);
$userId = $_SESSION['user_id'];
$quizId = $data['quiz_id'];
$userAnswers = $data['details']; 

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT id, correct_option, option_a FROM quiz_questions WHERE quiz_id = ?");
    $stmt->execute([$quizId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $score = 0;
    $total = count($questions);

    foreach ($questions as $q) {
        $qId = $q['id'];
        $correctAnswer = trim($q['correct_option']);
        $userAnswer = isset($userAnswers[$qId]) ? trim($userAnswers[$qId]) : '';
        $isMCQ = !empty($q['option_a']);

        if ($isMCQ) {
            if (strtoupper($userAnswer) === strtoupper($correctAnswer)) {
                $score++;
            }
        } else {
            if (strtolower($userAnswer) === strtolower($correctAnswer)) {
                $score++;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO quiz_attempts (user_id, quiz_id, score, total_questions) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $quizId, $score, $total]);
    $attemptId = $pdo->lastInsertId();

    $detailStmt = $pdo->prepare("INSERT INTO quiz_attempt_answers (attempt_id, question_id, user_answer) VALUES (?, ?, ?)");
    
    foreach ($userAnswers as $qId => $ans) {
        $detailStmt->execute([$attemptId, $qId, $ans]);
    }
    $pdo->commit();
    echo json_encode(['status' => 'success', 'score' => $score, 'total' => $total]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>