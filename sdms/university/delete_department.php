<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if department_id is provided
if (!isset($_GET['department_id'])) {
    header("Location: add_department.php?error=1&message=" . urlencode("Department ID not provided"));
    exit();
}

$department_id = intval($_GET['department_id']);

// First check if department exists
$query = "SELECT * FROM department_data WHERE department_id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: add_department.php?error=1&message=" . urlencode("Error preparing statement: " . mysqli_error($conn)));
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $department_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: add_department.php?error=1&message=" . urlencode("Department not found"));
    exit();
}

// Check if department has any programs before deleting
$query = "SELECT COUNT(*) as count FROM program_data WHERE department_id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: add_department.php?error=1&message=" . urlencode("Error preparing statement: " . mysqli_error($conn)));
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $department_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if ($row['count'] > 0) {
    header("Location: add_department.php?error=1&message=" . urlencode("Cannot delete department with associated programs"));
    exit();
}

// Delete department using prepared statement
$query = "DELETE FROM department_data WHERE department_id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: add_department.php?error=1&message=" . urlencode("Error preparing delete statement: " . mysqli_error($conn)));
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $department_id);

if (mysqli_stmt_execute($stmt)) {
    // Get all departments after delete
    $query = "SELECT * FROM department_data ORDER BY department_name";
    $result = mysqli_query($conn, $query);
    $departments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    // Store in session
    $_SESSION['departments'] = $departments;
    
    header("Location: add_department.php?deleted=1");
    exit();
} else {
    header("Location: add_department.php?error=1&message=" . urlencode("Error deleting department: " . mysqli_stmt_error($stmt)));
    exit();
}
?>