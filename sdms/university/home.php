<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

// Get all programs for the form
$query = "SELECT p.*, d.department_name FROM program_data p LEFT JOIN department_data d ON p.department_id = d.department_id ORDER BY p.program_name";
$result = mysqli_query($conn, $query);
$programs = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get all students with their program and department information
$query = "SELECT s.*, p.program_name, d.department_name 
          FROM students_data s 
          LEFT JOIN program_data p ON s.program_id = p.program_id 
          LEFT JOIN department_data d ON p.department_id = d.department_id 
          ORDER BY s.name";
$result = mysqli_query($conn, $query);
$students = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Handle success and error messages
$success = null;
$error = null;
$error_message = null;

if (isset($_GET['added'])) {
    $success = "Student added successfully!";
} elseif (isset($_GET['updated'])) {
    $success = "Student updated successfully!";
} elseif (isset($_GET['deleted'])) {
    $success = "Student deleted successfully!";
} elseif (isset($_GET['error'])) {
    $error = true;
    $error_message = isset($_GET['message']) ? urldecode($_GET['message']) : "An error occurred";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Management System - Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .dashboard-container {
            padding: 20px;
        }
        .welcome-message {
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
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
        .select2-container {
            width: 100% !important;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body style="display: flex;">
    <?php include 'sidebar.php'; ?>
    <div class="main-content" style="flex-grow: 1; padding: 20px; margin-left: 280px;">
        <div class="welcome-message">
            <h1 class="mb-0"><i class="fas fa-user-circle me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Add Student Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Add New Student</h5>
            </div>
            <div class="card-body">
                <form action="add_student_process.php" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>

                    <div class="mb-3">
                        <label for="program_id" class="form-label">Program</label>
                        <select class="form-select" id="program_id" name="program_id" required>
                            <option value="0">Select a program</option>
                            <?php foreach ($programs as $program): ?>
                                <option value="<?php echo htmlspecialchars($program['program_id']); ?>">
                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                    <span class="text-muted small">
                                        (<?php echo htmlspecialchars($program['department_name']); ?>)
                                    </span>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Student</button>
                </form>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="d-flex gap-2 mb-4">
            <a href="add_program.php" class="btn btn-outline-primary">
                <i class="fas fa-plus-circle me-2"></i>Add Program
            </a>
            <a href="add_department.php" class="btn btn-outline-primary">
                <i class="fas fa-plus-circle me-2"></i>Add Department
            </a>
            <a href="logout.php" class="btn btn-outline-danger">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>

        <!-- Students Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Students</h5>
                    <div class="d-flex gap-2">
                        <input type="text" id="studentSearch" class="form-control" placeholder="Search students..." style="width: 250px;">
                        <select id="programFilter" class="form-select" style="width: 200px;">
                            <option value="">All Programs</option>
                            <?php foreach ($programs as $prog): ?>
                                <option value="<?php echo htmlspecialchars($prog['program_name']); ?>">
                                    <?php echo htmlspecialchars($prog['program_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select id="departmentFilter" class="form-select" style="width: 200px;">
                            <option value="">All Departments</option>
                            <?php 
                            $unique_departments = array_unique(array_column($students, 'department_name'));
                            foreach ($unique_departments as $dept): 
                                if ($dept): ?>
                                    <option value="<?php echo htmlspecialchars($dept); ?>">
                                        <?php echo htmlspecialchars($dept); ?>
                                    </option>
                                <?php endif;
                            endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="studentsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Program</th>
                                <th>Department</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($student['program_name'] ?? 'Not assigned'); ?></td>
                                    <td><?php echo htmlspecialchars($student['department_name'] ?? 'Not assigned'); ?></td>
                                    <td class="action-buttons">
                                        <a href='edit_student.php?students_id=<?php echo $student['students_id']; ?>' 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href='delete_student.php?students_id=<?php echo $student['students_id']; ?>' 
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
                    <p>No students found matching your search criteria.</p>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('studentSearch');
            const programFilter = document.getElementById('programFilter');
            const departmentFilter = document.getElementById('departmentFilter');
            const table = document.getElementById('studentsTable');
            const noResults = document.getElementById('noResults');
            
            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedProgram = programFilter.value.toLowerCase();
                const selectedDepartment = departmentFilter.value.toLowerCase();
                const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                let visibleRows = 0;
                
                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    const name = row.cells[0].textContent.toLowerCase();
                    const email = row.cells[1].textContent.toLowerCase();
                    const phone = row.cells[2].textContent.toLowerCase();
                    const program = row.cells[3].textContent.toLowerCase();
                    const department = row.cells[4].textContent.toLowerCase();
                    
                    const matchesSearch = name.includes(searchTerm) || 
                                        email.includes(searchTerm) || 
                                        phone.includes(searchTerm);
                    const matchesProgram = selectedProgram === '' || program.includes(selectedProgram);
                    const matchesDepartment = selectedDepartment === '' || department.includes(selectedDepartment);
                    
                    if (matchesSearch && matchesProgram && matchesDepartment) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                }
                
                // Show/hide no results message
                if (visibleRows === 0) {
                    table.style.display = 'none';
                    noResults.style.display = 'block';
                } else {
                    table.style.display = 'table';
                    noResults.style.display = 'none';
                }
            }
            
            // Add event listeners
            searchInput.addEventListener('input', filterTable);
            programFilter.addEventListener('change', filterTable);
            departmentFilter.addEventListener('change', filterTable);
        });
    </script>
</body>
</html>