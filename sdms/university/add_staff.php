<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

// Get departments for dropdown
$query = "SELECT * FROM department_data ORDER BY department_name";
$result = mysqli_query($conn, $query);
$departments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $department_id = intval($_POST['department_id'] ?? 0);

    if (!empty($first_name) && !empty($last_name) && !empty($email) && $department_id > 0) {
        // Using NOW() for hire_date, assuming staff is added on the day they are hired.
        $query = "INSERT INTO staff (first_name, last_name, email, phone_number, position, department_id, hire_date) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssi", $first_name, $last_name, $email, $phone, $position, $department_id);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: add_staff.php?added=1");
            exit();
        } else {
            $error_message = "Error adding staff: " . mysqli_stmt_error($stmt);
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}

// Get all staff
$staff = []; // Initialize as an empty array
$query = "SELECT s.*, d.department_name, CONCAT(s.first_name, ' ', s.last_name) AS full_name FROM staff s LEFT JOIN department_data d ON s.department_id = d.department_id ORDER BY s.first_name, s.last_name";
$result = mysqli_query($conn, $query);
if ($result) {
    $staff = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    // This error message will be displayed if the query fails (e.g., table doesn't exist)
    $error_message = "Error fetching staff data: " . mysqli_error($conn);
}

$success = isset($_GET['added']) ? "Staff member added successfully!" : null;
$error = isset($error_message);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Management System - Add Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { display: flex; }
        .main-content { flex-grow: 1; padding: 20px; margin-left: 280px; }
        .card { border: none; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; }
        .btn-primary { width: 100%; padding: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h1 class="mb-4"><i class="fas fa-user-plus me-2"></i>Add Staff</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Add Staff Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Staff Information</h5>
            </div>
            <div class="card-body">
                <form action="add_staff.php" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-6 mb-3">
                            <label for="position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="position" name="position">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select" id="department_id" name="department_id" required>
                                <option value="0">Select a department</option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?php echo htmlspecialchars($department['department_id']); ?>">
                                        <?php echo htmlspecialchars($department['department_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Staff Member</button>
                </form>
            </div>
        </div>

        <!-- Staff Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Current Staff</h5>
                    <div class="d-flex gap-2">
                        <input type="text" id="staffSearch" class="form-control" placeholder="Search staff..." style="width: 250px;">
                        <select id="positionFilter" class="form-select" style="width: 200px;">
                            <option value="">All Positions</option>
                            <?php 
                            $unique_positions = array_unique(array_column($staff, 'position'));
                            foreach ($unique_positions as $pos): 
                                if ($pos): ?>
                                    <option value="<?php echo htmlspecialchars($pos); ?>">
                                        <?php echo htmlspecialchars($pos); ?>
                                    </option>
                                <?php endif;
                            endforeach; ?>
                        </select>
                        <select id="departmentFilter" class="form-select" style="width: 200px;">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept['department_name']); ?>">
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="staffTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staff as $staff_member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($staff_member['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($staff_member['email']); ?></td>
                                    <td><?php echo htmlspecialchars($staff_member['phone_number']); ?></td>
                                    <td><?php echo htmlspecialchars($staff_member['position']); ?></td>
                                    <td><?php echo htmlspecialchars($staff_member['department_name'] ?? 'N/A'); ?></td>
                                    <td class="text-center">
                                        <a href='#' class="btn btn-sm btn-primary disabled"><i class="fas fa-edit"></i> Edit</a>
                                        <a href='#' class="btn btn-sm btn-danger disabled"><i class="fas fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div id="noResults" class="text-center text-muted py-4" style="display: none;">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>No staff members found matching your search criteria.</p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('staffSearch');
            const positionFilter = document.getElementById('positionFilter');
            const departmentFilter = document.getElementById('departmentFilter');
            const table = document.getElementById('staffTable');
            const noResults = document.getElementById('noResults');
            
            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedPosition = positionFilter.value.toLowerCase();
                const selectedDepartment = departmentFilter.value.toLowerCase();
                const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                let visibleRows = 0;
                
                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    const name = row.cells[0].textContent.toLowerCase();
                    const email = row.cells[1].textContent.toLowerCase();
                    const phone = row.cells[2].textContent.toLowerCase();
                    const position = row.cells[3].textContent.toLowerCase();
                    const department = row.cells[4].textContent.toLowerCase();
                    
                    const matchesSearch = name.includes(searchTerm) || 
                                        email.includes(searchTerm) || 
                                        phone.includes(searchTerm);
                    const matchesPosition = selectedPosition === '' || position.includes(selectedPosition);
                    const matchesDepartment = selectedDepartment === '' || department.includes(selectedDepartment);
                    
                    if (matchesSearch && matchesPosition && matchesDepartment) {
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
            positionFilter.addEventListener('change', filterTable);
            departmentFilter.addEventListener('change', filterTable);
        });
    </script>
</body>
</html>
