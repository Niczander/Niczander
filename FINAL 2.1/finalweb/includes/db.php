<?php
require_once __DIR__ . '/config.php';

// Enhanced database connection with better error handling
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_PERSISTENT => true, // Connection pooling
    PDO::ATTR_TIMEOUT => 30, // Connection timeout
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

$pdo = null;
$max_retries = 3;
$retry_count = 0;

while ($retry_count < $max_retries && $pdo === null) {
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        // Test connection
        $pdo->query('SELECT 1');
    } catch (Exception $e) {
        $retry_count++;
        error_log("Database connection attempt {$retry_count} failed: " . $e->getMessage());
        
        if ($retry_count >= $max_retries) {
            // Log critical error
            error_log("CRITICAL: Database connection failed after {$max_retries} attempts: " . $e->getMessage());
            
            // Return user-friendly error
            http_response_code(503);
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Service temporarily unavailable. Please try again later.']);
            } else {
                echo '<!DOCTYPE html><html><head><title>Service Unavailable</title></head><body><h1>Service Temporarily Unavailable</h1><p>We are experiencing technical difficulties. Please try again in a few minutes.</p></body></html>';
            }
            exit;
        }
        
        // Wait before retry (exponential backoff)
        sleep($retry_count * 2);
    }
}

// Function to safely execute queries with error handling
function safe_query($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (Exception $e) {
        error_log("Database query error: " . $e->getMessage() . " SQL: " . $sql);
        throw $e;
    }
}
