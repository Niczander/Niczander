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

        $stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($staff) {
            $response['success'] = true;
            $response['staff'] = $staff;
        } else {
            $response['message'] = 'Staff member not found.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'No staff ID provided.';
}

echo json_encode($response);
