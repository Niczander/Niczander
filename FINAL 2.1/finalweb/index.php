<?php 
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/header.php'; 
?>
<?php
// Initialize variables to prevent undefined errors
$collections = [];
$products = [];
$posts = [];
$slides = [];
$stats = ['branches'=>6, 'products'=>6000, 'customers'=>250000, 'years'=>12];

// Load data directly from database
try {
    $posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    $slides = $pdo->query("SELECT * FROM slides ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if collections table exists, if not create it
    try {
        $collections = $pdo->query('SELECT * FROM collections ORDER BY id DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Collections table might not exist, create it and initialize empty
        $pdo->exec("CREATE TABLE IF NOT EXISTS collections(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,description TEXT)");
        $collections = [];
    }
    
    try {
        // Alias collection name as 'category' to match template usage
        $products = $pdo->query("SELECT p.*, c.name as category FROM products p LEFT JOIN collections c ON p.collection_id=c.id ORDER BY p.id DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Products table might not have collection_id column yet
        $products = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Metrics removed from frontend - now only managed in admin panel
} catch (Exception $e) {
    error_log("Error loading homepage data: " . $e->getMessage());
    $posts = [];
    $slides = [];
    $collections = [];
    $products = [];
    $stats = ['branches'=>6, 'products'=>6000, 'customers'=>250000, 'years'=>12];
}
?>
<section class="hero py-5">
  <div class="container-fluid px-0">
    <div id="heroSlider" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <?php foreach($slides as $i=>$s): ?>
          <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $i===0?'active':''; ?>" aria-current="true"></button>
        <?php endforeach; ?>
      </div>
      <div class="carousel-inner">
        <?php foreach($slides as $i=>$s): ?>
        <div class="carousel-item <?php echo $i===0?'active':''; ?>">
          <?php if (!empty($s['video_url'])): ?>
            <video src="<?php echo htmlspecialchars($s['video_url']); ?>" class="d-block w-100" autoplay muted loop></video>
          <?php else: ?>
            <img src="<?php echo htmlspecialchars($s['image_url']); ?>" class="d-block w-100" alt="Slide">
          <?php endif; ?>
          <div class="carousel-caption text-start">
            <h2 class="fw-bold"><?php echo htmlspecialchars($s['title']); ?></h2>
            <p class="lead"><?php echo htmlspecialchars($s['subtitle']); ?></p>
            <a href="<?php echo BASE_URL; ?>/collections.php" class="btn btn-accent btn-lg">Shop Collections</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
      <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-3">
      <h3 class="mb-0">Shop Collections</h3>
      <a href="<?php echo BASE_URL; ?>/collections.php" class="text-navy">View All</a>
    </div>
    <div class="row g-3">
      <?php foreach($collections as $c): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="<?php echo BASE_URL; ?>/collections.php?cat=<?php echo urlencode($c['name']); ?>" class="card text-decoration-none text-center cat-card">
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($c['name']); ?></h5>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<section class="py-5 bg-light">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-3">
      <h3 class="mb-0">Featured Products</h3>
      <a href="<?php echo BASE_URL; ?>/collections.php" class="text-navy">View All</a>
    </div>
    <div class="row g-4">
      <?php foreach($products as $p): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100 product-card">
          <img src="<?php echo htmlspecialchars($p['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['name']); ?>">
          <div class="card-body">
            <span class="category-badge mb-2 d-inline-block"><?php echo htmlspecialchars($p['category']); ?></span>
            <h5 class="card-title"><?php echo htmlspecialchars($p['name']); ?></h5>
            <p class="card-text fw-bold text-navy">UGX <?php echo number_format($p['price']); ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5 bg-light">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-3">
      <h3 class="mb-0">Latest Posts</h3>
      <a href="<?php echo BASE_URL; ?>/collections.php" class="text-navy">View Collections</a>
    </div>
    <div class="row g-4">
      <?php foreach($posts as $p): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 product-card">
          <img src="<?php echo htmlspecialchars($p['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['title']); ?>">
          <div class="card-body">
            <span class="category-badge mb-2 d-inline-block">Update</span>
            <h5 class="card-title"><?php echo htmlspecialchars($p['title']); ?></h5>
            <p class="card-text text-muted"><?php echo htmlspecialchars($p['excerpt']); ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(empty($posts)): ?>
      <div class="col-12"><div class="alert alert-info">No posts yet. Seed the database.</div></div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require_once __DIR__.'/includes/footer.php'; ?>
