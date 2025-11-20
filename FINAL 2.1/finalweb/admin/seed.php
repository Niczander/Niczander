<?php
require_once __DIR__.'/../includes/config.php';
$dsn='mysql:host='.DB_HOST.';charset=utf8mb4';
try{$pdo=new PDO($dsn,DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);}catch(Exception $e){die('DB connect failed');}
$pdo->exec("CREATE DATABASE IF NOT EXISTS `".DB_NAME."` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `".DB_NAME."`");
$pdo->exec("CREATE TABLE IF NOT EXISTS collections(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,description TEXT)");

// Auto-migration: Convert categories to collections if needed
try {
    $result = $pdo->query("SHOW TABLES LIKE 'categories'");
    if($result->rowCount() > 0) {
        // Copy data from categories to collections
        $pdo->exec("INSERT IGNORE INTO collections (id, name, description) SELECT id, name, description FROM categories");
        
        // Check if products table has category_id column
        $result = $pdo->query("SHOW COLUMNS FROM products LIKE 'category_id'");
        if($result->rowCount() > 0) {
            // Add collection_id column if it doesn't exist
            try {
                $pdo->exec("ALTER TABLE products ADD COLUMN collection_id INT");
            } catch (Exception $e) {
                // Column might already exist
            }
            
            // Copy category_id values to collection_id
            $pdo->exec("UPDATE products SET collection_id = category_id WHERE collection_id IS NULL");
            
            // Drop old foreign key constraint
            try {
                $pdo->exec("ALTER TABLE products DROP FOREIGN KEY products_ibfk_1");
            } catch (Exception $e) {
                // Constraint might not exist or have different name
            }
            
            // Drop category_id column
            try {
                $pdo->exec("ALTER TABLE products DROP COLUMN category_id");
            } catch (Exception $e) {
                // Column might not exist
            }
        }
        
        // Drop categories table
        $pdo->exec("DROP TABLE categories");
    }
} catch (Exception $e) { 
    // Ignore migration errors and continue
}

// Handle existing products table that might have category_id instead of collection_id
try {
    $result = $pdo->query("SHOW COLUMNS FROM products LIKE 'category_id'");
    if($result->rowCount() > 0) {
        // Products table exists with category_id, need to migrate
        try {
            $pdo->exec("ALTER TABLE products ADD COLUMN collection_id INT");
        } catch (Exception $e) {
            // Column might already exist
        }
        
        // Copy category_id values to collection_id
        $pdo->exec("UPDATE products SET collection_id = category_id WHERE collection_id IS NULL");
        
        // Drop old foreign key constraint
        try {
            $pdo->exec("ALTER TABLE products DROP FOREIGN KEY products_ibfk_1");
        } catch (Exception $e) {
            // Constraint might not exist
        }
        
        // Drop category_id column
        try {
            $pdo->exec("ALTER TABLE products DROP COLUMN category_id");
        } catch (Exception $e) {
            // Column might not exist
        }
    }
} catch (Exception $e) {
    // Table might not exist yet
}

$pdo->exec("CREATE TABLE IF NOT EXISTS products(id INT AUTO_INCREMENT PRIMARY KEY,collection_id INT NOT NULL,name VARCHAR(150) NOT NULL,price INT NOT NULL DEFAULT 0,image_url VARCHAR(255) NOT NULL,FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE)");
$pdo->exec("CREATE TABLE IF NOT EXISTS posts(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(150) NOT NULL,excerpt VARCHAR(255),image_url VARCHAR(255) NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$pdo->exec("CREATE TABLE IF NOT EXISTS site_metrics(metric_key VARCHAR(50) PRIMARY KEY,metric_value INT NOT NULL DEFAULT 0)");
$pdo->exec("CREATE TABLE IF NOT EXISTS branches(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,district VARCHAR(120) NOT NULL,address VARCHAR(255) NOT NULL,phone VARCHAR(50) NOT NULL,opening_hours VARCHAR(120) NOT NULL)");
$pdo->exec("CREATE TABLE IF NOT EXISTS slides(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(150) NOT NULL,subtitle VARCHAR(255) NOT NULL,image_url VARCHAR(255) NOT NULL)");

// No reset functionality - preserve all admin data
$pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,email VARCHAR(150) NOT NULL,message TEXT NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
// align with enhanced contact fields
// MySQL compatibility: add columns only if missing
foreach ([
  ['phone','VARCHAR(50) NULL'],
  ['subject','VARCHAR(150) NULL'],
  ['reason','VARCHAR(80) NULL']
] as $col){
  $q=$pdo->prepare("SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'contact_messages' AND COLUMN_NAME = ?");
  $q->execute([DB_NAME,$col[0]]);
  $exists=(int)$q->fetchColumn()>0;
  if(!$exists){
    $pdo->exec("ALTER TABLE contact_messages ADD COLUMN ".$col[0]." ".$col[1]);
  }
}
// orders
$pdo->exec("CREATE TABLE IF NOT EXISTS orders( id INT AUTO_INCREMENT PRIMARY KEY, order_code VARCHAR(20) NOT NULL UNIQUE, customer_name VARCHAR(120) NOT NULL, customer_phone VARCHAR(50) NOT NULL, customer_email VARCHAR(150) NULL, payment_method ENUM('MTN_MOMO','AIRTEL_MONEY','VISA') NOT NULL, payment_reference VARCHAR(50) NULL, status ENUM('pending','awaiting_mobile_money','paid','failed','cancelled') NOT NULL DEFAULT 'pending', total_amount INT NOT NULL DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP )");
// Ensure VISA is included in enum for existing databases
try{
  $col=$pdo->query("SHOW COLUMNS FROM orders LIKE 'payment_method'")->fetch();
  if($col && strpos($col['Type'],'VISA')===false){
    $pdo->exec("ALTER TABLE orders MODIFY payment_method ENUM('MTN_MOMO','AIRTEL_MONEY','VISA') NOT NULL");
  }
}catch(Exception $e){/* ignore */}

// Add updated_at column to orders table if it doesn't exist
try{
  $col=$pdo->query("SHOW COLUMNS FROM orders LIKE 'updated_at'")->fetch();
  if(!$col){
    $pdo->exec("ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
  }
}catch(Exception $e){/* ignore */}

$pdo->exec("CREATE TABLE IF NOT EXISTS order_items( id INT AUTO_INCREMENT PRIMARY KEY, order_id INT NOT NULL, product_id INT NULL, name VARCHAR(150) NOT NULL, price INT NOT NULL, qty INT NOT NULL DEFAULT 1, FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE )");

// Create site_metrics table for admin panel metrics
$pdo->exec("CREATE TABLE IF NOT EXISTS site_metrics(
    metric_key VARCHAR(50) PRIMARY KEY,
    metric_value INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// No default data population - admin will upload everything manually

// Fetch all data from database and generate JSON for frontend
$data = [
    'collections' => $pdo->query("SELECT * FROM collections ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC),
    'products' => $pdo->query("SELECT p.*, c.name as collection FROM products p LEFT JOIN collections c ON p.collection_id=c.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC),
    'posts' => $pdo->query("SELECT * FROM posts ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC),
    'slides' => $pdo->query("SELECT * FROM slides ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC),
    'branches' => $pdo->query("SELECT * FROM branches ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC),
    'metrics' => $pdo->query("SELECT * FROM site_metrics")->fetchAll(PDO::FETCH_KEY_PAIR),
];

// Write to JSON file for any systems that might use it
$jsonFile = __DIR__.'/../includes/site_data.json';
file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));

echo "Website data updated successfully for ".DB_NAME." - ".count($data['collections'])." collections, ".count($data['products'])." products, ".count($data['posts'])." posts, ".count($data['slides'])." slides, ".count($data['branches'])." branches";
?>
