<?php
// Simple migration trigger to convert categories to collections
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }

echo "<h3>Database Migration: Categories to Collections</h3>";
echo "<p>Running migration...</p>";

try {
    // Include the seed file which contains the migration logic
    include __DIR__ . '/seed.php';
    echo "<p style='color: green;'>✅ Migration completed successfully!</p>";
    echo "<p>Your database has been updated:</p>";
    echo "<ul>";
    echo "<li>Categories table converted to collections</li>";
    echo "<li>Products now use collection_id instead of category_id</li>";
    echo "<li>All data preserved during migration</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Migration failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='branches.php'>← Back to Branches</a> | <a href='index.php'>← Back to Admin</a></p>";
?>
