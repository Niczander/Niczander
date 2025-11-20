<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }

require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/config.php';

// Load dashboard statistics
try {
    $stats = [];
    $stats['products'] = $pdo->query('SELECT COUNT(*) as count FROM products')->fetch()['count'];
    $stats['categories'] = $pdo->query('SELECT COUNT(*) as count FROM collections')->fetch()['count'];
    $stats['posts'] = $pdo->query('SELECT COUNT(*) as count FROM posts')->fetch()['count'];
    $stats['slides'] = $pdo->query('SELECT COUNT(*) as count FROM slides')->fetch()['count'];
    $stats['branches'] = $pdo->query('SELECT COUNT(*) as count FROM branches')->fetch()['count'];
    $stats['orders'] = $pdo->query('SELECT COUNT(*) as count FROM orders')->fetch()['count'];
    $stats['messages'] = $pdo->query('SELECT COUNT(*) as count FROM contact_messages')->fetch()['count'];
    
    // Recent activity
    $recent_orders = $pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 5')->fetchAll();
    $recent_messages = $pdo->query('SELECT * FROM contact_messages ORDER BY id DESC LIMIT 5')->fetchAll();
    $recent_products = $pdo->query('SELECT p.*, c.name as category FROM products p LEFT JOIN collections c ON p.collection_id=c.id ORDER BY p.id DESC LIMIT 5')->fetchAll();
    
    // Revenue stats
    $revenue_today = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status='paid'")->fetch()['total'] ?? 0;
    $revenue_month = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status='paid'")->fetch()['total'] ?? 0;
    $revenue_total = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status='paid'")->fetch()['total'] ?? 0;
    
} catch(Exception $e) {
    $stats = ['products' => 0, 'categories' => 0, 'posts' => 0, 'slides' => 0, 'branches' => 0, 'orders' => 0, 'messages' => 0];
    $recent_orders = [];
    $recent_messages = [];
    $recent_products = [];
    $revenue_today = $revenue_month = $revenue_total = 0;
}

require_once __DIR__.'/admin_header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Dashboard</h2>
                <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>!</p>
            </div>
            <div class="text-end">
                <small class="text-muted"><?php echo date('l, F j, Y'); ?></small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/admin_sidebar.php'; ?>
    
    <!-- Dashboard Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-box text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1"><?php echo number_format($stats['products']); ?></h3>
                            <p class="text-muted mb-0">Products</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-shopping-cart text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1"><?php echo number_format($stats['orders']); ?></h3>
                            <p class="text-muted mb-0">Orders</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-envelope text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1"><?php echo number_format($stats['messages']); ?></h3>
                            <p class="text-muted mb-0">Messages</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-map-marker-alt text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1"><?php echo number_format($stats['branches']); ?></h3>
                            <p class="text-muted mb-0">Branches</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-1">UGX <?php echo number_format($revenue_today); ?></h4>
                            <p class="mb-0 opacity-75">Today's Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-1">UGX <?php echo number_format($revenue_month); ?></h4>
                            <p class="mb-0 opacity-75">This Month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-1">UGX <?php echo number_format($revenue_total); ?></h4>
                            <p class="mb-0 opacity-75">Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Orders</h5>
                        <a href="orders.php" class="btn btn-outline-primary btn-sm">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(!empty($recent_orders)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($recent_orders as $order): ?>
                            <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($order['order_code']); ?></h6>
                                        <p class="mb-1 text-muted small"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?php echo $order['status'] === 'paid' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                        <div class="mt-1">
                                            <strong>UGX <?php echo number_format($order['total_amount']); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No orders yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Messages</h5>
                        <a href="messages.php" class="btn btn-outline-primary btn-sm">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(!empty($recent_messages)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($recent_messages as $message): ?>
                            <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($message['name']); ?></h6>
                                        <p class="mb-1 text-muted small"><?php echo htmlspecialchars($message['subject']); ?></p>
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($message['created_at'])); ?></small>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <span class="badge bg-info">New</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No messages yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Products -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Products</h5>
                        <a href="products.php" class="btn btn-outline-primary btn-sm">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(!empty($recent_products)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_products as $product): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="rounded" 
                                                 style="width: 50px; height: 50px; object-fit: cover;"
                                                 onerror="this.src='https://via.placeholder.com/50x50?text=No+Image'">
                                        </td>
                                        <td>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($product['name']); ?></h6>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($product['category'] ?? 'No Category'); ?></span>
                                        </td>
                                        <td>
                                            <strong>UGX <?php echo number_format($product['price']); ?></strong>
                                        </td>
                                        <td>
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-box fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No products yet</p>
                            <a href="products.php" class="btn btn-primary">Add Your First Product</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="products.php" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-plus-circle mb-2 d-block fa-2x"></i>
                                Add Product
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="categories.php" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-tags mb-2 d-block fa-2x"></i>
                                Manage Categories
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="orders.php" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-shopping-cart mb-2 d-block fa-2x"></i>
                                View Orders
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="metrics.php" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-chart-bar mb-2 d-block fa-2x"></i>
                                Manage Metrics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__.'/admin_sidebar_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-refresh dashboard stats every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);

// Add some interactivity to cards
document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
        this.style.transition = 'transform 0.2s ease';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});
</script>

</body>
</html>