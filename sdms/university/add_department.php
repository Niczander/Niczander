<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include database connection
include 'db.php';

// Handle success and error messages
$success = null;
$error = null;
$error_message = null;

if (isset($_GET['added'])) {
    $success = "Department added successfully!";
} elseif (isset($_GET['updated'])) {
    $success = "Department updated successfully!";
} elseif (isset($_GET['error'])) {
    $error = true;
    $error_message = isset($_GET['message']) ? urldecode($_GET['message']) : "An error occurred";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $department_name = trim($_POST['department_name'] ?? '');
    
    if (empty($department_name)) {
        header("Location: add_department.php?error=1&message=" . urlencode("Department name is required"));
        exit();
    }

    // Use prepared statement to prevent SQL injection
    $query = "INSERT INTO department_data (department_name) VALUES (?)";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $department_name);
        
        if (mysqli_stmt_execute($stmt)) {
            // Get all departments including the new one
            $query = "SELECT * FROM department_data ORDER BY department_name";
            $result = mysqli_query($conn, $query);
            $departments = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            // Store in session
            $_SESSION['departments'] = $departments;
            
            header("Location: add_department.php?added=1");
            exit();
        } else {
            header("Location: add_department.php?error=1&message=" . urlencode(mysqli_stmt_error($stmt)));
            exit();
        }
    } else {
        header("Location: add_department.php?error=1&message=" . urlencode(mysqli_error($conn)));
        exit();
    }
}

// Get all departments
$query = "SELECT * FROM department_data ORDER BY department_name";
$result = mysqli_query($conn, $query);
$departments = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Management System - Add Department</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .dashboard-container {
            padding: 20px;
        }
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .card-title {
            margin-bottom: 0;
        }
        .btn-primary {
            width: 100%;
            padding: 10px;
            font-weight: bold;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .table td {
            vertical-align: middle;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .action-buttons a {
            margin-right: 5px;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body style="display: flex;">
    <?php include 'sidebar.php'; ?>
    <div class="main-content" style="flex-grow: 1; padding: 20px; margin-left: 280px;">
        <h1 class="mb-4"><i class="fas fa-plus-circle me-2"></i>Add Department</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Add Department Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Department Information</h5>
            </div>
            <div class="card-body">
                <form action="add_department.php" method="post">
                    <div class="mb-3">
                        <label for="department_name" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="department_name" name="department_name" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Department</button>
                </form>
            </div>
        </div>

        <!-- Departments Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Departments</h5>
                    <div class="d-flex gap-2">
                        <input type="text" id="departmentSearch" class="form-control" placeholder="Search departments..." style="width: 250px;">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="departmentsTable">
                        <thead>
                            <tr>
                                <th>Department Name</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $department): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($department['department_name']); ?></td>
                                    <td class="action-buttons">
                                        <a href='edit_department.php?department_id=<?php echo $department['department_id']; ?>' 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href='delete_department.php?department_id=<?php echo $department['department_id']; ?>' 
                                           class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div id="noResults" class="text-center text-muted py-4" style="display: none;">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>No departments found matching your search criteria.</p>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('departmentSearch');
            const table = document.getElementById('departmentsTable');
            const noResults = document.getElementById('noResults');
            
            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                let visibleRows = 0;
                
                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    const departmentName = row.cells[0].textContent.toLowerCase();
                    
                    if (departmentName.includes(searchTerm)) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                }
                
                // Show/hide no results message
                if (visibleRows === 0 && searchTerm !== '') {
                    table.style.display = 'none';
                    noResults.style.display = 'block';
                } else {
                    table.style.display = 'table';
                    noResults.style.display = 'none';
                }
            }
            
            // Add event listener
            searchInput.addEventListener('input', filterTable);
        });
    </script>
</body>
</html>