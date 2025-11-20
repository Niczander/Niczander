<?php require_once __DIR__.'/includes/db.php'; ?>
<?php require_once __DIR__.'/includes/header.php'; ?>
<?php
$q=trim($_GET['q']??'');
$products=[];$posts=[];
if($q!==''){
  $like='%'.$q.'%';
  $ps=$pdo->prepare("SELECT p.id,p.name,p.price,p.image_url,c.name AS category FROM products p JOIN categories c ON p.category_id=c.id WHERE p.name LIKE ? OR c.name LIKE ? ORDER BY p.id DESC LIMIT 50");
  $ps->execute([$like,$like]);
  $products=$ps->fetchAll();
  $as=$pdo->prepare("SELECT id,title,excerpt,image_url,created_at FROM posts WHERE title LIKE ? OR excerpt LIKE ? ORDER BY created_at DESC LIMIT 20");
  $as->execute([$like,$like]);
  $posts=$as->fetchAll();
}
?>
<section class="py-5">
  <div class="container">
    <h3 class="mb-3">Search results for: "<?php echo htmlspecialchars($q); ?>"</h3>
    <div class="row g-4">
      <div class="col-lg-8">
        <h5 class="mb-3">Products (<?php echo count($products); ?>)</h5>
        <div class="row g-4">
          <?php foreach($products as $p): ?>
          <div class="col-6 col-md-4">
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
          <?php if($q!=='' && empty($products)): ?>
          <div class="col-12"><div class="alert alert-info">No products found.</div></div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-4">
        <h5 class="mb-3">Posts (<?php echo count($posts); ?>)</h5>
        <div class="row g-3">
          <?php foreach($posts as $p): ?>
          <div class="col-12">
            <div class="card h-100">
              <div class="row g-0">
                <div class="col-4"><img src="<?php echo htmlspecialchars($p['image_url']); ?>" class="img-fluid rounded-start" alt=""></div>
                <div class="col-8">
                  <div class="card-body py-2">
                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($p['title']); ?></h6>
                    <div class="small text-muted"><?php echo htmlspecialchars($p['excerpt']); ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if($q!=='' && empty($posts)): ?>
          <div class="col-12"><div class="alert alert-info">No posts found.</div></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__.'/includes/footer.php'; ?>
