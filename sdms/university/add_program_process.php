<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $program_name = trim($_POST['program_name'] ?? '');
    
    if (empty($program_name)) {
        header("Location: add_program.php?error=1");
        exit();
    }

    // Use prepared statement to prevent SQL injection
    $query = "INSERT INTO program_data (program_name) VALUES (?)";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $program_name);
        
        if (mysqli_stmt_execute($stmt)) {
            // Get all programs including the new one
            $query = "SELECT * FROM program_data ORDER BY program_name";
            $result = mysqli_query($conn, $query);
            $programs = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            // Store in session
            $_SESSION['programs'] = $programs;
            
            header("Location: add_program.php?added=1");
            exit();
        } else {
            header("Location: add_program.php?error=1&message=" . urlencode(mysqli_stmt_error($stmt)));
            exit();
        }
    } else {
        header("Location: add_program.php?error=1&message=" . urlencode(mysqli_error($conn)));
        exit();
    }
} else {
    // If not POST request, redirect to add program page
    header("Location: add_program.php");
    exit();
}
?>