<?php 
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/header.php'; 
?>
<?php
// Load branches directly from database
try {
    $branches = $pdo->query("SELECT * FROM branches ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $branches = [];
    error_log("Error loading branches: " . $e->getMessage());
}
?>
<section class="py-5">
  <div class="container">
    <h3 class="mb-4">Branches in the Central Region</h3>
    <div class="row g-4">
      <?php foreach($branches as $b): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title mb-1"><?php echo htmlspecialchars($b['name']); ?></h5>
            <div class="small text-muted mb-2"><?php echo htmlspecialchars($b['district']); ?></div>
            <div class="mb-2"><?php echo htmlspecialchars($b['address']); ?></div>
            <div class="mb-2">Phone: <?php echo htmlspecialchars($b['phone']); ?></div>
            <div>Hours: <?php echo htmlspecialchars($b['opening_hours']); ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(empty($branches)): ?>
      <div class="col-12"><div class="alert alert-info">No branches yet. Add them in the admin panel.</div></div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require_once __DIR__.'/includes/footer.php'; ?>
