<?php
// Create connection without selecting a database
$conn = mysqli_connect("localhost", "root", "");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$db_name = "university_database";
$query = "CREATE DATABASE IF NOT EXISTS $db_name";

if (mysqli_query($conn, $query)) {
    echo "Database created successfully\n";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "\n";
}

// Select the database
mysqli_select_db($conn, $db_name);

// Create users table if it doesn't exist
$query = "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $query)) {
    echo "Users table created successfully\n";
} else {
    echo "Error creating users table: " . mysqli_error($conn) . "\n";
}

// Insert a test user if none exist
$query = "SELECT COUNT(*) as count FROM users";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    $test_password = password_hash("test123", PASSWORD_DEFAULT);
    $query = "INSERT INTO users (username, password, email) VALUES 
        ('testuser', '$test_password', 'test@example.com')";
    
    if (mysqli_query($conn, $query)) {
        echo "Test user created successfully\n";
    } else {
        echo "Error creating test user: " . mysqli_error($conn) . "\n";
    }
}

mysqli_close($conn);
?>
