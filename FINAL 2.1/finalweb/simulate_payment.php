<?php require_once __DIR__.'/includes/db.php'; ?>
<?php
// Developer utility to simulate mobile money callbacks locally
// Usage: /simulate_payment.php?code=UHS-XXXX&status=paid|failed
$code=trim($_GET['code']??'');
$status=trim($_GET['status']??'');
$allowed=['paid','failed','cancelled'];
if(!$code || !in_array($status,$allowed)){
  http_response_code(400);
  echo 'Usage: simulate_payment.php?code=ORDER_CODE&status=paid|failed|cancelled';
  exit;
}
$stmt=$pdo->prepare("UPDATE orders SET status=? WHERE order_code=?");
$stmt->execute([$status,$code]);
header('Location: '.BASE_URL.'/track_order.php?code='.urlencode($code));
