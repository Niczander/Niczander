<?php
session_start();
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/config.php';

header('Content-Type: application/json');

// CSRF Protection
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security validation failed']);
    exit;
}

$order_code = trim($_POST['order_code'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$pin = trim($_POST['pin'] ?? '');

// Validate inputs
if (!$order_code || !$payment_method || !$phone_number) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate phone number format
if (!preg_match('/^(\+256|0)[7][0-9]{8}$/', $phone_number)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
    exit;
}

// Validate PIN (4 digits)
if (!preg_match('/^[0-9]{4}$/', $pin)) {
    echo json_encode(['success' => false, 'message' => 'PIN must be 4 digits']);
    exit;
}

try {
    // Get order details
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ? AND status = 'awaiting_mobile_money'");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found or already processed']);
        exit;
    }
    
    // Simulate mobile money API call
    $payment_result = simulateMobileMoneyPayment($payment_method, $phone_number, $pin, $order['total_amount']);
    
    if ($payment_result['success']) {
        // Update order status to paid
        $stmt = $pdo->prepare("UPDATE orders SET status = 'paid', payment_reference = ?, updated_at = NOW() WHERE order_code = ?");
        $stmt->execute([$payment_result['reference'], $order_code]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Payment successful!',
            'reference' => $payment_result['reference'],
            'redirect' => BASE_URL . '/order_success.php?code=' . urlencode($order_code)
        ]);
    } else {
        // Update order status to failed
        $stmt = $pdo->prepare("UPDATE orders SET status = 'failed', updated_at = NOW() WHERE order_code = ?");
        $stmt->execute([$order_code]);
        
        echo json_encode([
            'success' => false, 
            'message' => $payment_result['message']
        ]);
    }
    
} catch (Exception $e) {
    error_log("Payment processing error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Payment processing failed. Please try again.']);
}

function simulateMobileMoneyPayment($method, $phone, $pin, $amount) {
    // Simulate different scenarios based on PIN
    switch ($pin) {
        case '0000':
            return [
                'success' => false,
                'message' => 'Insufficient balance. Please top up your account.'
            ];
        case '1111':
            return [
                'success' => false,
                'message' => 'Invalid PIN. Please check your PIN and try again.'
            ];
        case '2222':
            return [
                'success' => false,
                'message' => 'Transaction timeout. Please try again.'
            ];
        case '3333':
            return [
                'success' => false,
                'message' => 'Service temporarily unavailable. Please try again later.'
            ];
        default:
            // Successful payment
            $reference = strtoupper($method) . '-' . date('YmdHis') . '-' . substr(md5($phone . $amount), 0, 6);
            return [
                'success' => true,
                'reference' => $reference,
                'message' => 'Payment successful'
            ];
    }
}
?>
