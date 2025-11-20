<?php 
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/header.php'; 
?>
<?php
// Get collections from database
try {
    $collections = $pdo->query("SELECT * FROM collections ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $collections = [];
    error_log("Error fetching collections: " . $e->getMessage());
}

$cat_name = trim($_GET['cat'] ?? '');

// Get products from database
try {
    if ($cat_name) {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category FROM products p JOIN collections c ON p.collection_id=c.id WHERE c.name = ? ORDER BY p.id DESC LIMIT 96");
        $stmt->execute([$cat_name]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $products = $pdo->query("SELECT p.*, c.name as category FROM products p JOIN collections c ON p.collection_id=c.id ORDER BY p.id DESC LIMIT 96")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $products = [];
    error_log("Error fetching products: " . $e->getMessage());
}
?>
<section class="py-5">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h3 class="mb-0">Collections <?php echo $cat_name ? ('— '.htmlspecialchars($cat_name)) : '' ;?></h3>
      <div class="text-muted small">Showing <?php echo count($products); ?> items</div>
    </div>
    <div class="mb-4">
      <?php foreach($collections as $c): ?>
        <a class="category-badge me-2 mb-2 d-inline-block text-decoration-none" href="<?php echo BASE_URL; ?>/collections.php?cat=<?php echo urlencode($c['name']); ?>"><?php echo htmlspecialchars($c['name']); ?></a>
      <?php endforeach; ?>
      <?php if(empty($collections)): ?>
        <div class="alert alert-info">No collections yet. Add them in the admin panel.</div>
      <?php endif; ?>
    </div>
    <div class="row g-4">
      <?php foreach($products as $p): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100 product-card">
          <img src="<?php echo htmlspecialchars($p['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['name']); ?>">
          <div class="card-body">
            <h6 class="card-title mb-1"><?php echo htmlspecialchars($p['name']); ?></h6>
            <div class="small text-muted mb-2"><?php echo htmlspecialchars($p['category']); ?></div>
            <div class="d-flex justify-content-between align-items-center">
              <div class="fw-bold text-navy">UGX <?php echo number_format($p['price']); ?></div>
              <button class="btn btn-sm btn-accent add-to-cart"
                data-id="<?php echo (int)$p['id']; ?>"
                data-name="<?php echo htmlspecialchars($p['name'],ENT_QUOTES); ?>"
                data-price="<?php echo (int)$p['price']; ?>"
                data-image="<?php echo htmlspecialchars($p['image_url'],ENT_QUOTES); ?>">
                Add
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(empty($products)): ?>
      <div class="col-12"><div class="alert alert-info">No products yet. Seed the database.</div></div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require_once __DIR__.'/includes/footer.php'; ?>
