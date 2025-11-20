<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id<=0){ http_response_code(400); echo 'Invalid slide ID'; exit; }

$st = $pdo->prepare('SELECT id,title,subtitle,image_url FROM slides WHERE id=?');
$st->execute([$id]);
$slide = $st->fetch();
if(!$slide){ http_response_code(404); echo 'Slide not found'; exit; }

$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='update_slide'){
  $title = trim($_POST['title'] ?? $slide['title']);
  $subtitle = trim($_POST['subtitle'] ?? $slide['subtitle']);
  $image_url = trim($_POST['image_url'] ?? $slide['image_url']);
  if($title && $subtitle && $image_url){
    $u = $pdo->prepare('UPDATE slides SET title=?, subtitle=?, image_url=? WHERE id=?');
    $u->execute([$title,$subtitle,$image_url,$id]);
    header('Location: index.php?msg='.urlencode('Slide updated successfully').'#pills-slides');
    exit;
  } else {
    $msg = 'Please fill all fields.';
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Slide · <?php echo SITE_NAME; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>:root{--navy:#0a2540}.bg-navy{background:var(--navy)!important}.btn-navy{background:var(--navy);color:#fff}.btn-navy:hover{filter:brightness(.95);color:#fff}</style>
</head>
<body>
<nav class="navbar navbar-dark bg-navy">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Admin · <?php echo SITE_NAME; ?></a>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-light btn-sm" href="index.php#pills-slides">Back to Slides</a>
      <a class="btn btn-outline-light btn-sm" target="_blank" href="<?php echo BASE_URL; ?>/index.php">View Site</a>
    </div>
  </div>
</nav>
<div class="container my-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Edit Slide #<?php echo $slide['id']; ?></h5>
          <?php if($msg): ?><div class="alert alert-info py-2"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
          <form method="post">
            <input type="hidden" name="action" value="update_slide">
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input name="title" class="form-control" value="<?php echo htmlspecialchars($slide['title']); ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Subtitle</label>
              <input name="subtitle" class="form-control" value="<?php echo htmlspecialchars($slide['subtitle']); ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Image URL</label>
              <input name="image_url" class="form-control" value="<?php echo htmlspecialchars($slide['image_url']); ?>" placeholder="https://..." required>
            </div>
            <div class="mt-3 d-flex gap-2">
              <button class="btn btn-navy">Save Changes</button>
              <a class="btn btn-outline-secondary" href="index.php#pills-slides">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h6 class="card-title">Preview</h6>
          <div class="ratio ratio-16x9 mb-2">
            <img src="<?php echo htmlspecialchars($slide['image_url']); ?>" alt="Preview" class="img-fluid rounded" onerror="this.src='https://via.placeholder.com/1200x675?text=No+Image';">
          </div>
          <div class="text-muted small">ID: <?php echo $slide['id']; ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
