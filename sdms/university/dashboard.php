<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

// Fetch data for charts
$students_query = "SELECT COUNT(*) as total_students FROM students_data";
$students_result = mysqli_query($conn, $students_query);
$total_students = mysqli_fetch_assoc($students_result)['total_students'];

$programs_query = "SELECT COUNT(*) as total_programs FROM program_data";
$programs_result = mysqli_query($conn, $programs_query);
$total_programs = mysqli_fetch_assoc($programs_result)['total_programs'];

$staff_query = "SELECT COUNT(*) as total_staff FROM staff";
$staff_result = mysqli_query($conn, $staff_query);
$total_staff = mysqli_fetch_assoc($staff_result)['total_staff'];

$departments_query = "SELECT COUNT(*) as total_departments FROM department_data";
$departments_result = mysqli_query($conn, $departments_query);
$total_departments = mysqli_fetch_assoc($departments_result)['total_departments'];

$students_by_program_query = "SELECT p.program_name, COUNT(s.students_id) as student_count FROM program_data p LEFT JOIN students_data s ON p.program_id = s.program_id GROUP BY p.program_name";
$students_by_program_result = mysqli_query($conn, $students_by_program_query);
$students_by_program = mysqli_fetch_all($students_by_program_result, MYSQLI_ASSOC);

$program_labels = array_map(function($item) { return $item['program_name']; }, $students_by_program);
$program_student_counts = array_map(function($item) { return $item['student_count']; }, $students_by_program);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Management System - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            display: flex;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 280px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h1>Dashboard</h1>
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_students; ?></h5>
                        <p class="card-text">Total Students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_programs; ?></h5>
                        <p class="card-text">Total Programs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_staff; ?></h5>
                        <p class="card-text">Total Staff</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_departments; ?></h5>
                        <p class="card-text">Total Departments</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Students vs Programs</div>
                    <div class="card-body">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Students per Program</div>
                    <div class="card-body">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pie Chart
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        const pieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Students', 'Programs'],
                datasets: [{
                    label: 'Total',
                    data: [<?php echo $total_students; ?>, <?php echo $total_programs; ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        });

        // Bar Chart
        const barCtx = document.getElementById('barChart').getContext('2d');
        const barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($program_labels); ?>,
                datasets: [{
                    label: '# of Students',
                    data: <?php echo json_encode($program_student_counts); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
