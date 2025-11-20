<?php
session_start();
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/config.php';

$order_code = trim($_GET['code'] ?? '');

if (!$order_code) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Get order details
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ? AND status = 'awaiting_mobile_money'");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: ' . BASE_URL . '/track_order.php?code=' . urlencode($order_code));
        exit;
    }
} catch (Exception $e) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<?php require_once __DIR__.'/includes/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h3 class="mb-0">
                            <i class="fas fa-mobile-alt me-2"></i>Complete Your Payment
                        </h3>
                        <p class="mb-0 opacity-75">Secure Mobile Money Payment</p>
                    </div>
                    <div class="card-body p-5">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h5 class="mb-3">Order Summary</h5>
                                <div class="bg-light p-3 rounded">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Order Code:</span>
                                        <strong><?php echo htmlspecialchars($order['order_code']); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Customer:</span>
                                        <span><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Phone:</span>
                                        <span><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Payment Method:</span>
                                        <span class="badge bg-info"><?php echo str_replace('_', ' ', $order['payment_method']); ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Total Amount:</span>
                                        <span class="fw-bold text-primary fs-5">UGX <?php echo number_format($order['total_amount']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3">Payment Instructions</h5>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>How it works:</strong>
                                    <ol class="mb-0 mt-2">
                                        <li>Click "Pay Now" button below</li>
                                        <li>Enter your Mobile Money PIN</li>
                                        <li>Confirm the payment</li>
                                        <li>You'll receive a confirmation SMS</li>
                                    </ol>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <strong>Security Note:</strong><br>
                                    Your PIN is encrypted and secure. We never store your PIN information.
                                </div>
                                
                                <div class="text-center">
                                    <button type="button" class="btn btn-primary btn-lg px-5" id="pay-now-btn">
                                        <i class="fas fa-credit-card me-2"></i>Pay Now
                                    </button>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        Having trouble? <a href="<?php echo BASE_URL; ?>/contact.php">Contact Support</a>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row text-center">
                            <div class="col-md-4">
                                <i class="fas fa-lock fa-2x text-success mb-2"></i>
                                <h6>Secure</h6>
                                <small class="text-muted">256-bit SSL encryption</small>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-clock fa-2x text-info mb-2"></i>
                                <h6>Instant</h6>
                                <small class="text-muted">Real-time processing</small>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-phone fa-2x text-warning mb-2"></i>
                                <h6>Support</h6>
                                <small class="text-muted">24/7 customer service</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="<?php echo BASE_URL; ?>/track_order.php?code=<?php echo urlencode($order['order_code']); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Order Tracking
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__.'/payment_modal.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const payNowBtn = document.getElementById('pay-now-btn');
    
    payNowBtn.addEventListener('click', function() {
        // Open payment modal with order details
        openPaymentModal(
            '<?php echo htmlspecialchars($order['order_code']); ?>',
            '<?php echo htmlspecialchars($order['payment_method']); ?>',
            '<?php echo htmlspecialchars($order['customer_phone']); ?>',
            '<?php echo $order['total_amount']; ?>'
        );
    });
});
</script>

<?php require_once __DIR__.'/includes/footer.php'; ?>
