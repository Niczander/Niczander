<?php require_once __DIR__.'/includes/db.php'; ?>
<?php require_once __DIR__.'/includes/header.php'; ?>
<?php
$code=trim($_GET['code']??'');
$phone=trim($_GET['phone']??'');
$order=null;$items=[];$msg='';
if($code && $phone){
  $stmt=$pdo->prepare("SELECT * FROM orders WHERE order_code=? AND customer_phone=?");
  $stmt->execute([$code,$phone]);
  $order=$stmt->fetch();
  if($order){
    $it=$pdo->prepare("SELECT name,price,qty FROM order_items WHERE order_id=?");
    $it->execute([$order['id']]);
    $items=$it->fetchAll();
  } else { $msg='No order found. Check your code and phone number.'; }
}
?>
<section class="py-5">
  <div class="container">
    <h3 class="mb-3">Track Order</h3>
    <form class="row g-3 mb-4" method="get">
      <div class="col-md-4">
        <label class="form-label">Order Code</label>
        <input type="text" name="code" class="form-control" placeholder="e.g. UHS-AB12CD34" value="<?php echo htmlspecialchars($code); ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input type="tel" name="phone" class="form-control" placeholder="07XXXXXXXX" value="<?php echo htmlspecialchars($phone); ?>" required>
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-accent" type="submit">Track</button>
      </div>
    </form>
    <?php if($msg): ?><div class="alert alert-warning"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if($order): ?>
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap gap-2">
          <div>
            <div class="small text-muted">Order Code</div>
            <div class="fw-bold"><?php echo htmlspecialchars($order['order_code']); ?></div>
          </div>
          <div>
            <div class="small text-muted">Status</div>
            <span class="badge bg-<?php echo $order['status']==='paid'?'success':($order['status']==='awaiting_mobile_money'?'warning text-dark':'secondary'); ?>"><?php echo htmlspecialchars($order['status']); ?></span>
          </div>
          <div>
            <div class="small text-muted">Total</div>
            <div class="fw-bold text-navy">UGX <?php echo number_format($order['total_amount']); ?></div>
          </div>
        </div>
      </div>
    </div>
    <h6>Items</h6>
    <div class="table-responsive">
      <table class="table table-sm">
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
        <tbody>
          <?php foreach($items as $it): $sub=$it['price']*$it['qty']; ?>
          <tr>
            <td><?php echo htmlspecialchars($it['name']); ?></td>
            <td><?php echo (int)$it['qty']; ?></td>
            <td>UGX <?php echo number_format($it['price']); ?></td>
            <td>UGX <?php echo number_format($sub); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__.'/includes/footer.php'; ?>
