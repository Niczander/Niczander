<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

// Get and sanitize input
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$program_id = intval($_POST['program_id'] ?? 0);

// Validate input
if (empty($name)) {
    header("Location: home.php?error=1&message=" . urlencode("Name is required"));
    exit();
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: home.php?error=1&message=" . urlencode("Invalid email format"));
    exit();
} elseif (empty($phone)) {
    header("Location: home.php?error=1&message=" . urlencode("Phone number is required"));
    exit();
} elseif ($program_id <= 0) {
    header("Location: home.php?error=1&message=" . urlencode("Program must be selected"));
    exit();
}

// Check if program exists
$query = "SELECT * FROM program_data WHERE program_id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: home.php?error=1&message=" . urlencode("Database error"));
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $program_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: home.php?error=1&message=" . urlencode("Invalid program selected"));
    exit();
}

// Insert new student using prepared statement
$query = "INSERT INTO students_data (name, email, phone, program_id) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: home.php?error=1&message=" . urlencode("Database error"));
    exit();
}

mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $phone, $program_id);

if (mysqli_stmt_execute($stmt)) {
    // Get all students after insertion
    $query = "SELECT s.*, p.program_name, d.department_name 
              FROM students_data s 
              LEFT JOIN program_data p ON s.program_id = p.program_id 
              LEFT JOIN department_data d ON p.department_id = d.department_id 
              ORDER BY s.name";
    $result = mysqli_query($conn, $query);
    $students = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    // Store in session
    $_SESSION['students'] = $students;
    
    header("Location: home.php?added=1");
    exit();
} else {
    $error = mysqli_stmt_error($stmt);
    
    // Check for specific duplicate errors
    if (strpos($error, 'Duplicate entry') !== false) {
        header("Location: home.php?error=1&message=" . urlencode("Student with this email or phone number already exists"));
    } else {
        header("Location: home.php?error=1&message=" . urlencode("Error adding student: " . $error));
    }
    exit();
}
?>