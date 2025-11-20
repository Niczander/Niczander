<?php 
session_start();
require_once __DIR__.'/includes/db.php'; 

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Simplified validation functions - more permissive
function validate_contact_name($name) {
    $name = trim($name);
    return !empty($name) && strlen($name) >= 1 && strlen($name) <= 100;
}

function validate_contact_phone($phone) {
    $phone = trim($phone);
    return !empty($phone) && strlen($phone) >= 3; // Very basic phone validation
}

function validate_message_content($message) {
    $message = trim($message);
    return !empty($message) && strlen($message) >= 1 && strlen($message) <= 2000;
}

$sent=false;$err='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Direct processing without restrictions
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        // Very basic validation - only check if required fields are not empty
        if (empty($name)) {
            $err = 'Please enter your name.';
        } elseif (empty($phone)) {
            $err = 'Please enter your phone number.';
        } elseif (empty($message)) {
            $err = 'Please enter your message.';
        } else {
            try {
                // Basic sanitization
                $name = trim($name);
                $email = trim($email);
                $phone = trim($phone);
                $subject = trim($subject);
                $reason = $reason ?: 'General Inquiry';
                $message = trim($message);
                
                $stmt = $pdo->prepare("INSERT INTO contact_messages(name,email,phone,subject,reason,message) VALUES(?,?,?,?,?,?)");
                $stmt->execute([$name, $email, $phone, $subject, $reason, $message]);
                
                $sent = true;
            } catch (Exception $e) {
                error_log("Contact form error: " . $e->getMessage());
                $err = 'Unable to send your message. Please try again later.';
            }
        }
}
?>
<?php require_once __DIR__.'/includes/header.php'; ?>
<section class="py-5">
  <div class="container">
    <h3 class="mb-3">Contact Us</h3>
    <?php if($sent): ?>
      <div class="alert alert-success">Thank you. Your message was received. Our team will get back to you shortly.</div>
    <?php endif; ?>
    <?php if($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card">
          <div class="card-body">
            <form method="post" class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email (optional)</label>
                <input type="email" name="email" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="tel" name="phone" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Subject (optional)</label>
                <input type="text" name="subject" class="form-control">
              </div>
              <div class="col-12">
                <label class="form-label">Reason</label>
                <select name="reason" class="form-select">
                  <option value="General Inquiry">General Inquiry</option>
                  <option value="Store Feedback">Store Feedback</option>
                  <option value="Orders & Delivery">Orders & Delivery</option>
                  <option value="Suppliers & Partnerships">Suppliers & Partnerships</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="6" required></textarea>
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button class="btn btn-accent" type="submit">Send Message</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title">Customer Care</h5>
            <p class="mb-2">Open 8am – 10pm daily</p>
            <ul class="list-unstyled small">
              <li>Phone: +256 700 000 000</li>
              <li>Email: info@uhome.ug</li>
              <li>Payments: MTN MoMo, Airtel Money, VISA</li>
            </ul>
            <a href="<?php echo BASE_URL; ?>/branches.php" class="btn btn-outline-light">Find a branch</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__.'/includes/footer.php'; ?>
