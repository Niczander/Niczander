<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';

$response = ['success' => false, 'courses' => []];

try {
    $database = new Database();
    $pdo = $database->getConnection();
    $stmt = $pdo->query('SELECT id, course_code AS code, name FROM courses ORDER BY course_code');
    $response['courses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['success'] = true;
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
<?php


