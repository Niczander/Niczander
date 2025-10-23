<?php
session_start();
require_once('DBClass.php'); // Your existing DBClass

// Check login (example - replace with your auth logic)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user role (1=Admin, 2=Lecturer, 3=Student)
$role = $_SESSION['role_id'] ?? 1; 
$user_id = $_SESSION['user_id'];

// Fetch data based on role
$stats = [];
if ($role == 1) { // Admin
    $stats['total_students'] = $db->query("SELECT COUNT(*) FROM userdetails WHERE isStudent=1")->fetchColumn();
    $stats['total_fees'] = $db->query("SELECT SUM(amount) FROM fees")->fetchColumn();
    $stats['attendance'] = $db->query("SELECT AVG(attendance_rate) FROM attendance")->fetchColumn();
} elseif ($role == 2) { // Lecturer
    $classes = $db->query("SELECT class_name FROM classes WHERE lecturer_id=?", [$user_id])->fetchAll();
} elseif ($role == 3) { // Student
    $grades = $db->query("SELECT course, marks FROM grades WHERE student_id=?", [$user_id])->fetchAll();
    $attendance = $db->query("SELECT date, status FROM attendance WHERE student_id=?", [$user_id])->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js">
    <style>
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .sidebar {
            background: #343a40;
            min-height: 100vh;
        }
        .sidebar a {
            color: rgba(255, 255, 255, 0.8);
        }
        .sidebar a:hover {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar p-4 text-white">
                <h3 class="text-center mb-4"><?php echo ($role == 1) ? "Admin" : (($role == 2) ? "Lecturer" : "Student"); ?> Dashboard</h3>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a class="nav-link active" href="dashboard.php">Overview</a></li>
                    <?php if ($role == 1): ?>
                        <li class="nav-item mb-2"><a class="nav-link" href="students.php">Manage Students</a></li>
                        <li class="nav-item mb-2"><a class="nav-link" href="fees.php">Fee Records</a></li>
                    <?php elseif ($role == 2): ?>
                        <li class="nav-item mb-2"><a class="nav-link" href="attendance.php">Take Attendance</a></li>
                        <li class="nav-item mb-2"><a class="nav-link" href="grades.php">Enter Grades</a></li>
                    <?php else: ?>
                        <li class="nav-item mb-2"><a class="nav-link" href="my_grades.php">My Grades</a></li>
                        <li class="nav-item mb-2"><a class="nav-link" href="my_attendance.php">My Attendance</a></li>
                    <?php endif; ?>
                    <li class="nav-item mb-2"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 p-4">
                <h2 class="mb-4">Dashboard Overview</h2>
                
                <!-- Role-Specific Stats -->
                <?php if ($role == 1): ?>
                    <!-- Admin Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white p-3">
                                <h5>Total Students</h5>
                                <h3><?php echo $stats['total_students']; ?></h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white p-3">
                                <h5>Fees Collected</h5>
                                <h3>$<?php echo number_format($stats['total_fees'], 2); ?></h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white p-3">
                                <h5>Avg Attendance</h5>
                                <h3><?php echo round($stats['attendance'], 2); ?>%</h3>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>

                <?php elseif ($role == 2): ?>
                    <!-- Lecturer Stats -->
                    <h4>Your Classes</h4>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Students</th>
                                <th>Avg Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><?php echo $class['class_name']; ?></td>
                                <td><?php echo $db->query("SELECT COUNT(*) FROM student_class WHERE class_id=?", [$class['id']])->fetchColumn(); ?></td>
                                <td><?php echo $db->query("SELECT AVG(grade) FROM grades WHERE class_id=?", [$class['id']])->fetchColumn(); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php else: ?>
                    <!-- Student Stats -->
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Your Grades</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $grade): ?>
                                    <tr>
                                        <td><?php echo $grade['course']; ?></td>
                                        <td><?php echo $grade['marks']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4>Attendance (Last 5 Records)</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($attendance, 0, 5) as $record): ?>
                                    <tr>
                                        <td><?php echo $record['date']; ?></td>
                                        <td><?php echo ($record['status'] == 1) ? "Present" : "Absent"; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Export Buttons -->
                <div class="mt-4">
                    <h4>Export Data</h4>
                    <a href="export.php?type=pdf" class="btn btn-danger">PDF Report</a>
                    <a href="export.php?type=excel" class="btn btn-success">Excel Export</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Admin Attendance Chart
        <?php if ($role == 1): ?>
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                datasets: [{
                    label: 'Monthly Attendance %',
                    data: [85, 79, 92, 88, 90],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)'
                }]
            },
            options: { scales: { y: { beginAtZero: true, max: 100 } } }
        });
        <?php endif; ?>
    </script>
</body>
</html>