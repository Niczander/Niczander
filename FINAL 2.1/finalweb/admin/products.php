<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/config.php';
// ensure tables
$pdo->exec("CREATE TABLE IF NOT EXISTS collections(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,description TEXT)");
$pdo->exec("CREATE TABLE IF NOT EXISTS products(id INT AUTO_INCREMENT PRIMARY KEY,collection_id INT NOT NULL,name VARCHAR(150) NOT NULL,price INT NOT NULL DEFAULT 0,image_url VARCHAR(255) NOT NULL,FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE)");

// helper
function post($k,$d=''){return isset($_POST[$k])?trim($_POST[$k]):$d;}
$msg = '';

try{
  if(isset($_POST['action'])){
    switch($_POST['action']){
      case 'add_product':
        $imageUrl = post('image_url');
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
              $imageUrl = BASE_URL.'/assets/uploads/'.$new;
            }
          }
        }
        $st=$pdo->prepare('INSERT INTO products(collection_id,name,price,image_url) VALUES(?,?,?,?)');
        $st->execute([post('collection_id'),post('name'),(int)post('price'),$imageUrl]);
        header('Location: products.php?msg='.urlencode('Product added'));
        exit;
      case 'update_product_price':
        $st=$pdo->prepare('UPDATE products SET price=? WHERE id=?');
        $st->execute([(int)post('price'), post('id')]);
        header('Location: products.php?msg='.urlencode('Product price updated'));
        exit;
      case 'delete_product':
        $pdo->prepare('DELETE FROM products WHERE id=?')->execute([post('id')]);
        header('Location: products.php?msg='.urlencode('Product deleted'));
        exit;
    }
  }
}catch(Exception $e){ $msg = 'Error: '.$e->getMessage(); }

if(!$msg && isset($_GET['msg'])){ $msg = trim($_GET['msg']); }

$cats=$pdo->query('SELECT * FROM collections ORDER BY name ASC')->fetchAll();
$products=$pdo->query('SELECT p.id,p.name,p.price,p.image_url,p.collection_id,c.name AS category FROM products p JOIN collections c ON c.id=p.collection_id ORDER BY p.id DESC LIMIT 100')->fetchAll();

require_once __DIR__.'/admin_header.php';
?>
<?php if($msg): ?><div class="alert alert-info py-2"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<div id="updateStatus" class="alert d-none" role="alert"></div>

<?php require_once __DIR__.'/admin_sidebar.php'; ?>
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card shadow-sm"><div class="card-body">
        <h6 class="card-title">Add Product</h6>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="action" value="add_product">
          <div class="mb-2">
            <label class="form-label">Category</label>
            <select name="collection_id" class="form-select" required>
              <option value="">Select...</option>
              <?php foreach($cats as $c): ?>
                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Price (UGX)</label><input type="number" name="price" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Image URL</label><input name="image_url" class="form-control" placeholder="https://..."></div>
          <div class="mb-2"><label class="form-label">Or Upload Image</label><input type="file" name="image_file" accept="image/*" class="form-control"></div>
          <button class="btn btn-navy btn-sm">Save</button>
        </form>
      </div></div>
    </div>
    <div class="col-lg-8">
      <div class="card shadow-sm"><div class="card-body">
        <h6 class="card-title">Latest Products</h6>
        <div class="table-responsive">
          <table class="table table-sm table-striped align-middle">
            <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price (UGX)</th><th>Image</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($products as $p): ?>
              <tr>
                <td><?php echo $p['id']; ?></td>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo htmlspecialchars($p['category']); ?></td>
                <td>
                  <form method="post" class="d-flex gap-1 align-items-center">
                    <input type="hidden" name="action" value="update_product_price">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <input type="number" name="price" class="form-control form-control-sm" style="max-width:140px" value="<?php echo (int)$p['price']; ?>" required>
                    <button class="btn btn-sm btn-navy">Save</button>
                  </form>
                </td>
                <td><img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="" style="height:40px"></td>
                <td class="d-flex gap-1">
                  <a class="btn btn-outline-primary btn-sm" href="edit_product.php?id=<?php echo $p['id']; ?>">Edit</a>
                  <form method="post" onsubmit="return confirm('Delete product?');" class="d-inline">
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <button class="btn btn-outline-danger btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div></div>
    </div>

<?php require_once __DIR__.'/admin_sidebar_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const btn = document.getElementById('update-site-btn');
  const statusBox = document.getElementById('updateStatus');
  if (btn) {
    btn.addEventListener('click', async () => {
      btn.disabled = true; btn.textContent = 'Updating...';
      statusBox.className = 'alert alert-info';
      statusBox.textContent = 'Updating website content...';
      statusBox.classList.remove('d-none');
      try {
        const res = await fetch('seed.php', { cache: 'no-store' });
        const text = await res.text();
        statusBox.className = res.ok ? 'alert alert-success' : 'alert alert-warning';
        statusBox.textContent = text || (res.ok ? 'Update complete.' : 'Update finished with warnings.');
        if (res.ok) setTimeout(()=>window.location.reload(), 800);
      } catch (e) {
        statusBox.className = 'alert alert-danger';
        statusBox.textContent = 'Update failed. Please try again.';
      } finally {
        btn.disabled = false; btn.textContent = 'Update Website';
      }
    });
  }
</script>
</body>
</html>
