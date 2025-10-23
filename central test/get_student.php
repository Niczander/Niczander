<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';

$response = ['success' => false];

if (isset($_GET['id'])) {
    try {
        $database = new Database();
        $pdo = $database->getConnection();

        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student) {
            $enrollStmt = $pdo->prepare("SELECT course_id FROM student_courses WHERE student_id = ?");
            $enrollStmt->execute([$student['id']]);
            $enrolledCourses = array_map('intval', array_column($enrollStmt->fetchAll(PDO::FETCH_ASSOC), 'course_id'));

            $response['success'] = true;
            $response['student'] = $student;
            $response['enrolled_courses'] = $enrolledCourses;
        } else {
            $response['message'] = 'Student not found.';
        }
    } catch (PDOException $e) {
        http_response_code(500);
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'No student ID provided.';
}

echo json_encode($response);
<?php


