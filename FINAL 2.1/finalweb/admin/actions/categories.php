<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }

require_once __DIR__.'/../../includes/db.php';
// Ensure table exists for stability
$pdo->exec("CREATE TABLE IF NOT EXISTS collections(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,description TEXT)");

function post($k,$d=''){return isset($_POST[$k])?trim($_POST[$k]):$d;}

// Simplified validation functions
function validate_category_name($name) {
    $name = trim($name);
    return !empty($name) && strlen($name) >= 1 && strlen($name) <= 100;
}

function validate_category_description($desc) {
    return strlen(trim($desc)) <= 500;
}

// Helper to update the site data
function update_site_data() {
    try {
        ob_start();
        include __DIR__ . '/../seed.php';
        ob_get_clean();
        return true;
    } catch (Exception $e) {
        error_log("Site data update error: " . $e->getMessage());
        return false;
    }
}

$msg = '';
$error = false;

try {
    if(isset($_POST['action'])){
        $action = $_POST['action'];
        
        switch($action){
            case 'add_category':
                $name = post('name');
                $description = post('description');
                
                if (!validate_category_name($name)) {
                    throw new Exception('Please enter a category name');
                }
                if (!validate_category_description($description)) {
                    throw new Exception('Description is too long');
                }
                // Prevent duplicates
                $dup = $pdo->prepare('SELECT 1 FROM collections WHERE name = ? LIMIT 1');
                $dup->execute([$name]);
                if ($dup->fetch()) {
                    throw new Exception('Category name already exists');
                }
                
                $st = $pdo->prepare('INSERT INTO collections(name,description) VALUES(?,?)');
                $st->execute([$name, $description]);
                $msg = 'Category added successfully';
                break;
                
            case 'delete_category':
                $id = (int)post('id');
                if ($id <= 0) {
                    throw new Exception('Invalid collection ID');
                }
                
                // Check if collection has products
                $check = $pdo->prepare('SELECT COUNT(*) FROM products WHERE collection_id = ?');
                $check->execute([$id]);
                if ($check->fetchColumn() > 0) {
                    throw new Exception('Cannot delete collection with existing products');
                }
                
                $st = $pdo->prepare('DELETE FROM collections WHERE id=?');
                $st->execute([$id]);
                $msg = 'Collection deleted successfully';
                break;
                
            case 'update_category':
                $id = (int)post('id');
                $name = post('name');
                $description = post('description');
                
                if ($id <= 0) {
                    throw new Exception('Invalid collection ID');
                }
                
                if (!validate_category_name($name)) {
                    throw new Exception('Please enter a collection name');
                }
                if (!validate_category_description($description)) {
                    throw new Exception('Description is too long');
                }
                // Prevent duplicates excluding current
                $dup = $pdo->prepare('SELECT 1 FROM collections WHERE name = ? AND id <> ? LIMIT 1');
                $dup->execute([$name, $id]);
                if ($dup->fetch()) {
                    throw new Exception('Category name already exists');
                }
                
                $st = $pdo->prepare('UPDATE collections SET name=?, description=? WHERE id=?');
                $st->execute([$name, $description, $id]);
                $msg = 'Collection updated successfully';
                break;
        }
    }
} catch(Exception $e) {
    $msg = 'Error: ' . $e->getMessage();
    $error = true;
    error_log("Admin category action error: " . $e->getMessage());
}

header('Location: ../categories.php?msg='.urlencode($msg));
exit;
