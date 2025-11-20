<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id<=0){ http_response_code(400); echo 'Invalid product ID'; exit; }

// Load collections for select
$cats = $pdo->query('SELECT id,name FROM collections ORDER BY name ASC')->fetchAll();

// Load product
$st = $pdo->prepare('SELECT id,collection_id,name,price,image_url FROM products WHERE id=?');
$st->execute([$id]);
$product = $st->fetch();
if(!$product){ http_response_code(404); echo 'Product not found'; exit; }

$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='update_product'){
  $category_id = (int)($_POST['collection_id']??$product['collection_id']);
  $name = trim($_POST['name']??$product['name']);
  $price = (int)($_POST['price']??$product['price']);
  $image_url = trim($_POST['image_url']??$product['image_url']);
  // optional upload
  if(!empty($_FILES['image_file']['name'])){
    $dir = __DIR__.'/../assets/uploads';
    if(!is_dir($dir)) { @mkdir($dir, 0777, true); }
    $fname = $_FILES['image_file']['name'];
    $tmp = $_FILES['image_file']['tmp_name'];
    $size = (int)$_FILES['image_file']['size'];
    $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if(in_array($ext,$allowed) && $size>0 && $size<=5*1024*1024){
      $new = 'prod_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
      if(move_uploaded_file($tmp, $dir.'/'.$new)){
        $image_url = BASE_URL.'/assets/uploads/'.$new;
      }
    }
  }
  if($category_id && $name && $price>=0){
    $u = $pdo->prepare('UPDATE products SET collection_id=?, name=?, price=?, image_url=? WHERE id=?');
    $u->execute([$category_id,$name,$price,$image_url,$id]);
    header('Location: index.php?msg='.urlencode('Product updated successfully').'#pills-products');
    exit;
  } else {
    $msg='Please fill all required fields.';
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Product · <?php echo SITE_NAME; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>:root{--navy:#0a2540}.bg-navy{background:var(--navy)!important}.btn-navy{background:var(--navy);color:#fff}.btn-navy:hover{filter:brightness(.95);color:#fff}</style>
</head>
<body>
<nav class="navbar navbar-dark bg-navy">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Admin · <?php echo SITE_NAME; ?></a>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-light btn-sm" href="index.php">Back to Dashboard</a>
      <a class="btn btn-outline-light btn-sm" target="_blank" href="<?php echo BASE_URL; ?>/index.php">View Site</a>
    </div>
  </div>
</nav>
<div class="container my-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Edit Product #<?php echo $product['id']; ?></h5>
          <?php if($msg): ?><div class="alert alert-info py-2"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_product">
            <div class="mb-3">
              <label class="form-label">Category</label>
              <select name="collection_id" class="form-select" required>
                <?php foreach($cats as $c): ?>
                  <option value="<?php echo $c['id']; ?>" <?php echo ($c['id']==$product['collection_id'])?'selected':''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Price (UGX)</label>
                <input type="number" name="price" class="form-control" value="<?php echo (int)$product['price']; ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Image URL</label>
                <input name="image_url" class="form-control" value="<?php echo htmlspecialchars($product['image_url']); ?>" placeholder="https://...">
              </div>
            </div>
            <div class="mt-3">
              <label class="form-label">Or Upload Image</label>
              <input type="file" name="image_file" accept="image/*" class="form-control">
            </div>
            <div class="mt-3 d-flex gap-2">
              <button class="btn btn-navy">Save Changes</button>
              <a class="btn btn-outline-secondary" href="index.php#pills-products">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h6 class="card-title">Preview</h6>
          <div class="ratio ratio-4x3 mb-2">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Preview" class="img-fluid rounded" onerror="this.src='https://via.placeholder.com/800x600?text=No+Image';">
          </div>
          <div class="text-muted small">ID: <?php echo $product['id']; ?> • Category ID: <?php echo $product['collection_id']; ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
