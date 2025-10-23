<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Basic validation
    if (empty($username) || empty($password) || empty($email)) {
        echo "<div style='color: red;'>All fields are required.</div>";
        include 'register.php';
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div style='color: red;'>Invalid email format.</div>";
        include 'register.php';
        exit();
    }

    // Check if username or email already exists
    $check_query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            echo "<div style='color: red;'>Username or email already exists.</div>";
            include 'register.php';
            exit();
        }
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the user
    $insert_query = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $email);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php?success=1");
            exit();
        } else {
            echo "<div style='color: red;'>Error creating account: " . mysqli_error($conn) . "</div>";
            include 'register.php';
            exit();
        }
    } else {
        echo "<div style='color: red;'>Database error. Please try again later.</div>";
        include 'register.php';
        exit();
    }
} else {
    // If not POST request, show register form
    include 'register.php';
}
?>