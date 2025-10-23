<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';

parse_str(file_get_contents('php://input'), $body);
$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($body['id']) ? (int)$body['id'] : 0);

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing student id']);
    exit;
}

try {
    $database = new Database();
    $pdo = $database->getConnection();

    // Wrap in transaction to ensure enrollment rows are deleted first when needed
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('DELETE FROM student_courses WHERE student_id = ?');
    $stmt->execute([$id]);

    $stmt = $pdo->prepare('DELETE FROM students WHERE id = ?');
    $stmt->execute([$id]);

    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
<?php


