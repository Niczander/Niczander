<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

// Get student ID from URL
$students_id = intval($_GET['students_id'] ?? 0);

// Validate student ID
if ($students_id <= 0) {
    header("Location: home.php");
    exit();
}

// Check if this is the confirmation page or actual delete
if (!isset($_POST['confirm'])) {
    // First time visiting - show confirmation page
    
    // Get student information
    $query = "SELECT s.*, p.program_name, d.department_name 
              FROM students_data s 
              LEFT JOIN program_data p ON s.program_id = p.program_id 
              LEFT JOIN department_data d ON p.department_id = d.department_id 
              WHERE s.students_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        die("Error preparing statement: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $students_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        header("Location: home.php");
        exit();
    }
    
    $student = mysqli_fetch_assoc($result);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirm Delete Student</title>
        <style>
            .warning { color: #ff6b6b; margin: 10px 0; }
            .success { color: green; margin: 10px 0; }
            .error { color: red; margin: 10px 0; }
            form {
                max-width: 600px;
                margin: 20px auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            table {
                width: 100%;
                margin: 20px 0;
                border-collapse: collapse;
            }
            th, td {
                padding: 8px;
                border: 1px solid #ddd;
            }
            th {
                background-color: #f4f4f4;
            }
            button {
                background-color: #ff6b6b;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            button:hover {
                background-color: #ff5252;
            }
            .cancel-button {
                background-color: #666;
                margin-left: 10px;
            }
            .cancel-button:hover {
                background-color: #444;
            }
        </style>
    </head>
    <body>
        <h1>Confirm Delete Student</h1>
        
        <form action="delete_student.php?students_id=<?php echo $students_id; ?>" method="post">
            <div class="warning">
                <strong>Warning:</strong> This action cannot be undone. Are you sure you want to delete this student?
            </div>
            
            <table>
                <tr>
                    <th>Name</th>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                </tr>
                <tr>
                    <th>Program</th>
                    <td><?php echo htmlspecialchars($student['program_name'] ?? 'Not assigned'); ?></td>
                </tr>
                <tr>
                    <th>Department</th>
                    <td><?php echo htmlspecialchars($student['department_name'] ?? 'Not assigned'); ?></td>
                </tr>
            </table>
            
            <input type="hidden" name="confirm" value="1">
            <button type="submit">Delete Student</button>
            <a href="home.php" class="cancel-button">Cancel</a>
        </form>
    </body>
    </html>
    <?php
    exit();
}

// If we're here, it means the user confirmed the deletion
$query = "DELETE FROM students_data WHERE students_id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    $error = "Error preparing delete statement: " . mysqli_error($conn);
} else {
    mysqli_stmt_bind_param($stmt, "i", $students_id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: home.php?deleted=1");
        exit();
    } else {
        $error = "Error deleting student: " . mysqli_stmt_error($stmt);
    }
}

// If we get here, there was an error
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Deleting Student</title>
    <style>
        .error { color: red; margin: 10px 0; }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Error Deleting Student</h1>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <p><a href="home.php" class="button">Back to Home</a></p>
</body>
</html>
