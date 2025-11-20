<?php require_once __DIR__.'/includes/db.php'; ?>
<?php require_once __DIR__.'/includes/header.php'; ?>
<?php
$code=trim($_GET['code']??'');
$stmt=$pdo->prepare("SELECT order_code,customer_name,customer_phone,payment_method,total_amount,status FROM orders WHERE order_code=?");
$stmt->execute([$code]);
$order=$stmt->fetch();
?>
<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body text-center">
            <h3 class="mb-2">Thank you for your order</h3>
            <?php if($order): ?>
            <p class="text-muted">Your order code is</p>
            <div class="display-6 fw-bold text-navy mb-3"><?php echo htmlspecialchars($order['order_code']); ?></div>
            <p class="mb-3">Total: <strong>UGX <?php echo number_format($order['total_amount']); ?></strong></p>
            <p class="mb-4">Payment method: <strong><?php echo htmlspecialchars(str_replace('_',' ', $order['payment_method'])); ?></strong></p>
            <?php if($order['payment_method']==='VISA' || $order['status']==='paid'): ?>
              <div class="alert alert-success text-start">
                Your payment has been received successfully via <strong>VISA</strong>.
                A confirmation has been recorded with your order. You can track your order status below.
              </div>
            <?php else: ?>
              <div class="alert alert-warning text-start">
                To complete payment, approve the Mobile Money prompt sent to your phone number <strong><?php echo htmlspecialchars($order['customer_phone']); ?></strong>.
                If you did not receive a prompt, check your balance and network, then try again or contact support.
              </div>
            <?php endif; ?>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
              <a class="btn btn-accent" href="<?php echo BASE_URL; ?>/track_order.php?code=<?php echo urlencode($order['order_code']); ?>&phone=<?php echo urlencode($order['customer_phone']); ?>">Track this order</a>
              <a class="btn btn-outline-light" href="<?php echo BASE_URL; ?>/collections.php">Continue shopping</a>
            </div>
            <?php else: ?>
            <div class="alert alert-danger">Invalid order code.</div>
            <a class="btn btn-accent" href="<?php echo BASE_URL; ?>/collections.php">Shop now</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__.'/includes/footer.php'; ?>
