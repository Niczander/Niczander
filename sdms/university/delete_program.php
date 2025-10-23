<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if program_id is provided
if (!isset($_GET['program_id'])) {
    header("Location: add_program.php?error=1&message=" . urlencode("Program ID not provided"));
    exit();
}

$program_id = intval($_GET['program_id']);

// First check if program exists
$query = "SELECT * FROM program_data WHERE program_id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: add_program.php?error=1&message=" . urlencode("Error preparing statement: " . mysqli_error($conn)));
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $program_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: add_program.php?error=1&message=" . urlencode("Program not found"));
    exit();
}

// Get program name and department for confirmation page
$program = mysqli_fetch_assoc($result);
$program_name = $program['program_name'];
$department_id = $program['department_id'];

// Get all other programs in the same department
$query = "SELECT program_id, program_name FROM program_data WHERE department_id = ? AND program_id != ? ORDER BY program_name";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: add_program.php?error=1&message=" . urlencode("Error preparing statement: " . mysqli_error($conn)));
    exit();
}

mysqli_stmt_bind_param($stmt, "ii", $department_id, $program_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$other_programs = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Check if program has any students
$query = "SELECT COUNT(*) as count FROM students_data WHERE program_id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: add_program.php?error=1&message=" . urlencode("Error preparing statement: " . mysqli_error($conn)));
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $program_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$student_count = $row['count'];

// If we have a second GET parameter 'confirm', proceed with deletion
if (isset($_GET['confirm'])) {
    // First update students to be unassigned (program_id = NULL)
    $query = "UPDATE students_data SET program_id = NULL WHERE program_id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        header("Location: add_program.php?error=1&message=" . urlencode("Error preparing update statement: " . mysqli_error($conn)));
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $program_id);
    mysqli_stmt_execute($stmt);

    // Then delete the program
    $query = "DELETE FROM program_data WHERE program_id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        header("Location: add_program.php?error=1&message=" . urlencode("Error preparing delete statement: " . mysqli_error($conn)));
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $program_id);

    if (mysqli_stmt_execute($stmt)) {
        // Get all programs after delete
        $query = "SELECT * FROM program_data ORDER BY program_name";
        $result = mysqli_query($conn, $query);
        $programs = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        // Store in session
        $_SESSION['programs'] = $programs;
        
        header("Location: add_program.php?deleted=1&message=" . urlencode("Program deleted successfully. Students have been unassigned and need to be reassigned to a new program."));
        exit();
    } else {
        header("Location: add_program.php?error=1&message=" . urlencode("Error deleting program: " . mysqli_stmt_error($stmt)));
        exit();
    }
} else {
    // Show confirmation page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Delete Program Confirmation</title>
        <style>
            .warning { color: #ff9800; margin: 10px 0; }
            .success { color: green; margin: 10px 0; }
            .error { color: red; margin: 10px 0; }
            .container { max-width: 600px; margin: 20px auto; padding: 20px; }
            button {
                padding: 10px 20px;
                margin: 10px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            .confirm {
                background-color: #4CAF50;
                color: white;
            }
            .cancel {
                background-color: #f44336;
                color: white;
            }
            .program-list {
                margin: 20px 0;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .program-list h3 {
                margin: 10px 0;
                color: #333;
            }
            .program-list ul {
                list-style: none;
                padding: 0;
            }
            .program-list li {
                margin: 5px 0;
                padding: 5px;
                background-color: #f9f9f9;
                border-radius: 3px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Delete Program Confirmation</h1>
            <div class="warning">
                Warning: You are about to delete the program "<?php echo htmlspecialchars($program_name); ?>".
            </div>
            
            <?php if ($student_count > 0): ?>
                <div class="warning">
                    This program has <?php echo $student_count; ?> enrolled student(s). Deleting the program will:
                    <ul>
                        <li>Remove the program from the system</li>
                        <li>Unassign all students from this program</li>
                        <li>Students will need to be reassigned to a new program</li>
                        <li>This action cannot be undone</li>
                    </ul>
                </div>

                <div class="program-list">
                    <h3>Other Programs in this Department:</h3>
                    <ul>
                        <?php foreach ($other_programs as $other_program): ?>
                            <li><?php echo htmlspecialchars($other_program['program_name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div>
                <a href="add_program.php">Cancel</a>
                <a href="delete_program.php?program_id=<?php echo $program_id; ?>&confirm=1" class="confirm">Confirm Delete</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>
