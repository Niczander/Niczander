<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Load collections for navigation dropdown
try {
    $nav_collections = $pdo->query("SELECT * FROM collections ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $nav_collections = [];
    error_log("Error loading navigation collections: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo SITE_NAME; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
<link rel="icon" type="image/jpeg" href="<?php echo BASE_URL; ?>/assets/images/icon.jpg">
</head>
<body>
<div class="topbar py-1 small text-dark">
  <div class="container">
    <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between gap-2">
      <div class="d-flex align-items-center gap-3 order-1 flex-wrap">
        <span>Open daily 8am â€“ 10pm</span>
        <span class="d-none d-md-inline">â€¢</span>
        <span class="d-none d-md-inline">Central Region, Uganda</span>
      </div>
      <div class="d-flex align-items-center justify-content-center gap-3 order-2 flex-wrap text-center">
        <a href="<?php echo BASE_URL; ?>/branches.php" class="text-decoration-none">Find a branch</a>
        <span class="text-secondary d-none d-sm-inline">|</span>
        <a href="<?php echo BASE_URL; ?>/track_order.php" class="text-decoration-none">Track Order</a>
        <span class="text-secondary d-none d-sm-inline">|</span>
        <span>MTN MoMo & Airtel Money</span>
      </div>
      <div class="d-flex align-items-center gap-3 order-3 flex-wrap">
        <a href="tel:+256760845198" class="text-decoration-none">+256 760 845 198</a>
        <a href="mailto:info@uhome.ug" class="text-decoration-none">info@uhome.ug</a>
        <span class="d-none d-md-inline">|</span>
        <a class="text-decoration-none" href="#" aria-label="Facebook">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12 2.04c.486 0 .88.394.88.88v2.12a.88.88 0 0 1-.88.88H10.8V8h1.2a.8.8 0 0 1 .77 1l-.4 1.6a.8.8 0 0 1-.77.6H10.8V14a.8.8 0 0 1-.8.8H8.8a.8.8 0 0 1-.8-.8v-2.8H6.4a.8.8 0 0 1-.8-.8V8.8a.8.8 0 0 1 .8-.8H8V6a2 2 0 0 1 2-2h2z"/></svg>
        </a>
        <a class="text-decoration-none" href="#" aria-label="X">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6 1H15l-4.9 5.6L15.4 15H11.2L7.9 10.4 4 15H1.6l5.2-5.9L1.2 1H5.4l3 4.2L12.6 1z"/></svg>
        </a>
      </div>
    </div>
  </div>
  </div>
<nav class="navbar navbar-expand-lg navbar-dark bg-navy sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold me-lg-4" href="<?php echo BASE_URL; ?>/index.php">U Home Supermarkets</a>
    
    <form class="d-none d-lg-flex flex-grow-1 me-lg-4" role="search" method="get" action="<?php echo BASE_URL; ?>/search.php">
      <input class="form-control" type="search" name="q" placeholder="Search for products, brands and categories" aria-label="Search">
      <button class="btn btn-accent ms-2" type="submit">Search</button>
    </form>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbars" aria-controls="navbars" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbars">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Collections</a>
          <ul class="dropdown-menu">
            <?php if(!empty($nav_collections)): ?>
              <?php foreach($nav_collections as $c): ?>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/collections.php?cat=<?php echo urlencode($c['name']); ?>"><?php echo htmlspecialchars($c['name']); ?></a></li>
              <?php endforeach; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/collections.php">All Collections</a></li>
            <?php else: ?>
              <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/collections.php">All Collections</a></li>
            <?php endif; ?>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/branches.php">Branches</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/contact.php">Contact</a></li>
      </ul>
      
      <form class="d-flex d-lg-none my-2 my-lg-0" role="search" method="get" action="<?php echo BASE_URL; ?>/search.php">
        <input class="form-control me-2" type="search" name="q" placeholder="Search products..." aria-label="Search">
        <button class="btn btn-accent" type="submit">Search</button>
      </form>

      <a href="<?php echo BASE_URL; ?>/cart.php" class="btn position-relative ms-lg-3 text-white">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
          <path d="M0 1a1 1 0 0 1 1-1h1.11a1 1 0 0 1 .98.804L3.89 3H14a1 1 0 0 1 .98 1.196l-1.5 7A1 1 0 0 1 12.5 12H5a1 1 0 0 1-.98-.804L2.01 2H1a1 1 0 0 1-1-1z"/>
          <circle cx="6" cy="14" r="1"/>
          <circle cx="12" cy="14" r="1"/>
        </svg>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">0</span>
      </a>
    </div>
  </div>
</nav>

