<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request');
    }
    $studentId = (int)($_POST['student_id'] ?? 0);
    $courseId = (int)($_POST['course_id'] ?? 0);
    $semester = (int)($_POST['semester'] ?? 1);
    $year = (int)($_POST['academic_year'] ?? date('Y'));
    if (!$studentId || !$courseId) { throw new Exception('Missing student or course'); }

    $database = new Database();
    $pdo = $database->getConnection();

    $stmt = $pdo->prepare('INSERT IGNORE INTO student_courses (student_id, course_id, semester, academic_year) VALUES (?, ?, ?, ?)');
    $stmt->execute([$studentId, $courseId, $semester, $year]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
<?php


