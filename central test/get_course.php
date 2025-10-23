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

        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($course) {
            $response['success'] = true;
            $response['course'] = $course;
        } else {
            $response['message'] = 'Course not found.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'No course ID provided.';
}

echo json_encode($response);
