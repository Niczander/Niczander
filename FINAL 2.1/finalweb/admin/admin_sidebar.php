<?php
// Get current page name for active state
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<div class="row g-0" style="margin: 0; width: 100vw;">
  <!-- Admin Sidebar -->
  <aside style="position: relative;">
    <div class="card position-fixed" style="width: 280px; height: calc(100vh - 56px); overflow-y: hidden; z-index: 1000; top: 56px; left: 0; border: none; border-radius: 0;">
      <div class="card-header" style="background:#0a2540;color:#fff">
        <h6 class="mb-0">Admin Navigation</h6>
      </div>
      <div class="list-group list-group-flush" style="height: calc(100vh - 116px); overflow-y: auto;">
        <a class="list-group-item list-group-item-action <?php echo $current_page === 'index' ? 'active' : ''; ?>" href="index.php">
          <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a class="list-group-item list-group-item-action <?php echo $current_page === 'categories' ? 'active' : ''; ?>" href="categories.php">
          <i class="fas fa-tags me-2"></i>Categories
        </a>
        <a class="list-group-item list-group-item-action <?php echo $current_page === 'products' ? 'active' : ''; ?>" href="products.php">
          <i class="fas fa-box me-2"></i>Products
        </a>
        <a class="list-group-item list-group-item-action <?php echo $current_page === 'posts' ? 'active' : ''; ?>" href="posts.php">
          <i class="fas fa-newspaper me-2"></i>Posts
        </a>
        <a class="list-group-item list-group-item-action <?php echo $current_page === 'slides' ? 'active' : ''; ?>" href="slides.php">
          <i class="fas fa-images me-2"></i>Slides
        </a>
        <a class="list-group-item list-group-item-action <?php echo $current_page === 'branches' ? 'active' : ''; ?>" href="branches.php">
          <i class="fas fa-map-marker-alt me-2"></i>Branches
        </a>
        <a class="list-group-item list-group-item-action <?php echo $current_page === 'metrics' ? 'active' : ''; ?>" href="metrics.php">
          <i class="fas fa-chart-bar me-2"></i>Metrics
        </a>
        <a class="list-group-item list-group-item-action <?php echo $current_page === 'orders' ? 'active' : ''; ?>" href="orders.php">
          <i class="fas fa-shopping-cart me-2"></i>Orders
        </a>
        <a class="list-group-item list-group-item-action <?php echo $current_page === 'messages' ? 'active' : ''; ?>" href="messages.php">
          <i class="fas fa-envelope me-2"></i>Messages
        </a>
        <div class="list-group-item bg-light">
          <small class="text-muted">Tools</small>
        </div>
        <a class="list-group-item list-group-item-action" href="#" id="update-site-btn">
          <i class="fas fa-sync me-2"></i>Update Website
        </a>
        <a class="list-group-item list-group-item-action" href="<?php echo BASE_URL; ?>/index.php" target="_blank">
          <i class="fas fa-external-link-alt me-2"></i>View Site
        </a>
        <a class="list-group-item list-group-item-action text-danger" href="logout.php">
          <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
      </div>
    </div>
  </aside>

  <!-- Main Content Area -->
  <div style="margin-left: 0; width: calc(100vw - 280px); height: calc(100vh - 56px); overflow-y: auto; padding: 20px 20px 20px 0; position: fixed; top: 56px; right: 0; left: 280px;">
