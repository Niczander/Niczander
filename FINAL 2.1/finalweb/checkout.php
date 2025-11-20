<?php 
session_start();
require_once __DIR__.'/includes/db.php'; 

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function gen_code(){ return 'UHS-'.strtoupper(substr(bin2hex(random_bytes(4)),0,8)); }

// Enhanced input validation functions
function validate_name($name) {
    return preg_match('/^[a-zA-Z\s]{2,50}$/', trim($name));
}

function validate_phone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return preg_match('/^(\+256|0)[7][0-9]{8}$/', $phone);
}

function validate_email($email) {
    return empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitize_cart_data($cart) {
    if (!is_array($cart)) return false;
    
    $sanitized = [];
    foreach ($cart as $item) {
        if (!isset($item['id'], $item['name'], $item['price'], $item['qty'])) continue;
        
        $sanitized[] = [
            'id' => (int)$item['id'],
            'name' => htmlspecialchars(trim($item['name']), ENT_QUOTES, 'UTF-8'),
            'price' => max(0, (int)$item['price']),
            'qty' => max(1, min(99, (int)$item['qty']))
        ];
    }
    return $sanitized;
}

$created=false;$err='';$order_code='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    // CSRF validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $err = 'Security validation failed. Please refresh and try again.';
    } else {
        $name = trim($_POST['customer_name'] ?? '');
        $phone = trim($_POST['customer_phone'] ?? '');
        $email = trim($_POST['customer_email'] ?? '');
        $method = $_POST['payment_method'] ?? '';
        $cart_json = $_POST['cart_json'] ?? '';
        
        // Enhanced validation
        if (!validate_name($name)) {
            $err = 'Please enter a valid name (2-50 characters, letters only).';
        } elseif (!validate_phone($phone)) {
            $err = 'Please enter a valid Ugandan phone number (e.g., 0701234567).';
        } elseif (!validate_email($email)) {
            $err = 'Please enter a valid email address.';
        } elseif (!in_array($method, ['MTN_MOMO','AIRTEL_MONEY','VISA'])) {
            $err = 'Please select a valid payment method.';
        } else {
            $cart = json_decode($cart_json, true);
            $cart = sanitize_cart_data($cart);
            
            if (!$cart || empty($cart)) {
                $err = 'Your cart is empty or contains invalid items.';
            } else {
                try {
                    // Verify products exist and prices match
                    $product_ids = array_column($cart, 'id');
                    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
                    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
                    $stmt->execute($product_ids);
                    $db_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $product_lookup = [];
                    foreach ($db_products as $product) {
                        $product_lookup[$product['id']] = $product;
                    }
                    
                    $total = 0;
                    $validated_cart = [];
                    
                    foreach ($cart as $item) {
                        if (!isset($product_lookup[$item['id']])) {
                            $err = 'Some products in your cart are no longer available.';
                            break;
                        }
                        
                        $db_product = $product_lookup[$item['id']];
                        if ($db_product['price'] != $item['price']) {
                            $err = 'Product prices have changed. Please refresh your cart.';
                            break;
                        }
                        
                        $line_total = $item['price'] * $item['qty'];
                        $total += $line_total;
                        $validated_cart[] = $item;
                    }
                    
                    if (!$err && $total > 0) {
                        $order_code = gen_code();
                        
                        $pdo->beginTransaction();
                        try {
                            $status = ($method === 'VISA') ? 'paid' : 'awaiting_mobile_money';
                            $stmt = safe_query($pdo, 
                                "INSERT INTO orders(order_code,customer_name,customer_phone,customer_email,payment_method,status,total_amount) VALUES(?,?,?,?,?,?,?)",
                                [$order_code, $name, $phone, $email ?: null, $method, $status, $total]
                            );
                            
                            $order_id = $pdo->lastInsertId();
                            
                            foreach ($validated_cart as $item) {
                                safe_query($pdo, 
                                    "INSERT INTO order_items(order_id,product_id,name,price,qty) VALUES(?,?,?,?,?)",
                                    [$order_id, $item['id'], $item['name'], $item['price'], $item['qty']]
                                );
                            }
                            
                            $pdo->commit();
                            
                            // Regenerate CSRF token
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                            
                            if ($method === 'VISA') {
                                header('Location: '.BASE_URL.'/order_success.php?code='.$order_code);
                                exit;
                            } else {
                                // For mobile money, redirect to payment page with PIN prompt
                                header('Location: '.BASE_URL.'/mobile_payment.php?code='.$order_code);
                                exit;
                            }
                        } catch (Exception $e) {
                            $pdo->rollBack();
                            error_log("Checkout error: " . $e->getMessage());
                            $err = 'Unable to process your order. Please try again.';
                        }
                    }
                } catch (Exception $e) {
                    error_log("Checkout validation error: " . $e->getMessage());
                    $err = 'Unable to validate your order. Please try again.';
                }
            }
        }
    }
}
?>
<?php require_once __DIR__.'/includes/header.php'; ?>
<section class="py-5">
  <div class="container">
    <h3 class="mb-3">Checkout</h3>
    <?php if($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-3">Customer details</h5>
            <form method="post" id="checkout-form">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
              <input type="hidden" name="cart_json" id="cart-json">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Full name</label>
                  <input type="text" name="customer_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone (MTN/Airtel)</label>
                  <input type="tel" name="customer_phone" class="form-control" required>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Email (optional)</label>
                  <input type="email" name="customer_email" class="form-control">
                </div>
                <div class="col-md-12">
                  <label class="form-label">Payment method</label>
                  <div class="d-flex gap-3">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="payment_method" id="pay_mtn" value="MTN_MOMO" required>
                      <label class="form-check-label" for="pay_mtn">MTN MoMo</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="payment_method" id="pay_airtel" value="AIRTEL_MONEY" required>
                      <label class="form-check-label" for="pay_airtel">Airtel Money</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="payment_method" id="pay_visa" value="VISA" required>
                      <label class="form-check-label" for="pay_visa">VISA</label>
                    </div>
                  </div>
                  <div class="form-text">MoMo will prompt on your phone. VISA is confirmed instantly (simulation).</div>
                </div>
              </div>
              <div class="mt-3 d-flex justify-content-between">
                <a href="<?php echo BASE_URL; ?>/cart.php" class="btn btn-outline-secondary">Back to Cart</a>
                <button class="btn btn-accent" type="submit">Place Order</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-3">Order summary</h5>
            <div id="checkout-summary"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
(function(){
  const CART_KEY='uhome_cart_v1';
  const cart=JSON.parse(localStorage.getItem(CART_KEY)||'[]');
  document.getElementById('cart-json').value=JSON.stringify(cart);
  let total=0; let html='';
  if(cart.length===0){html='<div class="alert alert-info">Your cart is empty.</div>';}
  else{
    html+='<ul class="list-group mb-3">';
    cart.forEach(it=>{ const line=(it.price*(it.qty||1)); total+=line; html+=`<li class="list-group-item d-flex justify-content-between align-items-center"><span>${it.name} <span class="text-muted">x ${it.qty||1}</span></span><strong>UGX ${line.toLocaleString()}</strong></li>`; });
    html+='</ul>';
    html+=`<div class="d-flex justify-content-between"><span class="fw-semibold">Total</span><span class="fw-bold text-navy">UGX ${total.toLocaleString()}</span></div>`;
  }
  document.getElementById('checkout-summary').innerHTML=html;
  const form=document.getElementById('checkout-form');
  form.addEventListener('submit',function(){ localStorage.setItem(CART_KEY, '[]'); });
})();
</script>
<?php require_once __DIR__.'/includes/footer.php'; ?>
