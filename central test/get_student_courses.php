<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';

$response = ['success' => false, 'enrollments' => []];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid student id']);
    exit;
}

try {
    $database = new Database();
    $pdo = $database->getConnection();

    $stmt = $pdo->prepare("SELECT sc.id, c.id AS course_id, c.course_code, c.name AS course_name, sc.semester, sc.academic_year, sc.grade, sc.remarks
                           FROM student_courses sc
                           JOIN courses c ON sc.course_id = c.id
                           WHERE sc.student_id = ?
                           ORDER BY sc.academic_year DESC, sc.semester DESC, c.course_code ASC");
    $stmt->execute([$_GET['id']]);
    $response['enrollments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['success'] = true;
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
<?php


