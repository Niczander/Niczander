<?php
session_start();
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/config.php';

// Ensure users table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS users( id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP )");

// Create default admin if not present (username: admin, password: zander)
$st=$pdo->prepare("SELECT COUNT(*) FROM users WHERE username='admin'");
if($st->execute() && !$st->fetchColumn()){
  $hash=password_hash('zander', PASSWORD_DEFAULT);
  $ins=$pdo->prepare("INSERT INTO users(username,password_hash) VALUES(?,?)");
  $ins->execute(['admin',$hash]);
}

$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $username=trim($_POST['username']??'');
  $password=$_POST['password']??'';
  $q=$pdo->prepare('SELECT id,username,password_hash FROM users WHERE username=?');
  $q->execute([$username]);
  $u=$q->fetch();
  if($u && password_verify($password,$u['password_hash'])){
    $_SESSION['user_id']=$u['id'];
    $_SESSION['user_name']=$u['username'];
    header('Location: index.php');
    exit;
  } else {
    $msg='Invalid credentials';
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login · <?php echo SITE_NAME; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>:root{--navy:#0a2540}.bg-navy{background:var(--navy)!important}</style>
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow-sm">
          <div class="card-header bg-navy text-white">Admin Login</div>
          <div class="card-body">
            <?php if($msg): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
            <form method="post" autocomplete="off">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <button class="btn btn-primary w-100" style="background:#0a2540;border-color:#0a2540">Sign in</button>
            </form>
            <div class="mt-3 text-center">
              <a href="register.php">Create an account</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
