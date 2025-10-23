<?php
require_once 'config/DBClass.php';

class AdminDashboard {
    private $db;

    public function __construct() {
        $this->db = new DBClass();
    }

    // Get total number of students
    public function getTotalStudents(): int {
        $result = $this->db->read('userdetails');
        return count($result);
    }

    // Get student fees data (assuming this would come from a fees table)
    public function getStudentFees(): array {
        // In a real implementation, this would join with a fees table
        // For now, we'll simulate some data
        $students = $this->db->read('userdetails');
        $feesData = [];
        
        foreach ($students as $student) {
            $feesData[] = [
                'id' => $student['id'],
                'name' => $student['names'],
                'fees_paid' => rand(500, 2000), // Simulated data
                'fees_due' => rand(0, 500)       // Simulated data
            ];
        }
        
        return $feesData;
    }

    // Get student attendance data (simulated)
    public function getStudentAttendance(): array {
        $students = $this->db->read('userdetails');
        $attendanceData = [];
        
        foreach ($students as $student) {
            $attendanceData[] = [
                'id' => $student['id'],
                'name' => $student['names'],
                'present' => rand(70, 100), // Simulated attendance percentage
                'absent' => rand(0, 30)    // Simulated absence percentage
            ];
        }
        
        return $attendanceData;
    }

    // Calculate overall attendance percentage
    public function getOverallAttendance(): float {
        $attendance = $this->getStudentAttendance();
        $total = 0;
        
        foreach ($attendance as $record) {
            $total += $record['present'];
        }
        
        return count($attendance) > 0 ? $total / count($attendance) : 0;
    }
}

$dashboard = new AdminDashboard();
$totalStudents = $dashboard->getTotalStudents();
$feesData = $dashboard->getStudentFees();
$attendanceData = $dashboard->getStudentAttendance();
$overallAttendance = $dashboard->getOverallAttendance();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <style>
        :root {
            --dark-bg: #1a1a2e;
            --darker-bg: #16213e;
            --accent-color: #0f3460;
            --text-color: #e6e6e6;
            --highlight: #4cc9f0;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            background-color: var(--darker-bg);
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: var(--accent-color);
            color: white;
            border-bottom: none;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        
        .table {
            color: var(--text-color);
            margin-bottom: 0;
        }
        
        .table th {
            background-color: var(--accent-color);
            border-color: #2a3a5a;
        }
        
        .table td {
            border-color: #2a3a5a;
            vertical-align: middle;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(15, 52, 96, 0.2);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(15, 52, 96, 0.4);
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--highlight);
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.8;
        }
        
        .progress {
            background-color: #2a3a5a;
            height: 10px;
            border-radius: 5px;
        }
        
        .progress-bar {
            background-color: var(--highlight);
        }
        
        .navbar {
            background-color: var(--accent-color) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .navbar-brand {
            font-weight: 700;
        }
        
        .sidebar {
            background-color: var(--darker-bg);
            min-height: calc(100vh - 56px);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-link {
            color: var(--text-color);
            padding: 10px 15px;
            margin: 5px 10px;
            border-radius: 5px;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: var(--accent-color);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .content {
            padding: 20px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        
        .badge-attendance {
            background-color: #4caf50;
        }
        
        .badge-fees {
            background-color: #2196f3;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Student Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-bell"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-user"></i> Admin</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="lecturerDashboard.php"><i class="fas fa-user"></i> Lecturers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="studentDashboard.php"><i class="fas fa-user">Students</i> </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <nav class="nav flex-column pt-3">
                    <a class="nav-link active" href="adminDashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a class="nav-link" href="students.php"><i class="fas fa-users"></i> Students</a>
                    <a class="nav-link" href="courses.php"><i class="fas fa-book"></i> Courses</a>
                    <a class="nav-link" href="#"><i class="fas fa-money-bill-wave"></i> Fees</a>
                    <a class="nav-link" href="#"><i class="fas fa-clipboard-check"></i> Attendance</a>
                    <a class="nav-link" href="#"><i class="fas fa-chart-bar"></i> Reports</a>
                    <a class="nav-link" href="#"><i class="fas fa-cog"></i> Settings</a>
                </nav>
            </div>

            <div class="col-md-10 content">
                <h2 class="mb-4">Dashboard Overview</h2>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="stat-value"><?php echo $totalStudents; ?></div>
                            <div class="stat-label">Total Students</div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo min(100, $totalStudents); ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="stat-value"><?php echo round($overallAttendance); ?>%</div>
                            <div class="stat-label">Overall Attendance</div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo $overallAttendance; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="stat-value">
                                <?php 
                                    $totalFees = array_reduce($feesData, function($carry, $item) {
                                        return $carry + $item['fees_paid'];
                                    }, 0);
                                    echo '$' . number_format($totalFees);
                                ?>
                            </div>
                            <div class="stat-label">Total Fees Collected</div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo min(100, $totalFees/100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Attendance Chart -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-line me-2"></i> Attendance Overview
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="attendanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Fees Table -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-money-bill-wave me-2"></i> Student Fees</span>
                                <span class="badge badge-fees p-2"><?php echo count($feesData); ?> Records</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Student Name</th>
                                                <th>Fees Paid</th>
                                                <th>Fees Due</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($feesData as $fee): ?>
                                            <tr>
                                                <td><?php echo $fee['id']; ?></td>
                                                <td><?php echo $fee['name']; ?></td>
                                                <td>$<?php echo number_format($fee['fees_paid'], 2); ?></td>
                                                <td>$<?php echo number_format($fee['fees_due'], 2); ?></td>
                                                <td>
                                                    <?php if ($fee['fees_due'] == 0): ?>
                                                        <span class="badge bg-success">Paid</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Attendance Table -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-clipboard-check me-2"></i> Student Attendance</span>
                                <span class="badge badge-attendance p-2"><?php echo count($attendanceData); ?> Records</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Student Name</th>
                                                <th>Present (%)</th>
                                                <th>Absent (%)</th>
                                                <th>Performance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendanceData as $attendance): ?>
                                            <tr>
                                                <td><?php echo $attendance['id']; ?></td>
                                                <td><?php echo $attendance['name']; ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" style="width: <?php echo $attendance['present']; ?>%">
                                                            <?php echo $attendance['present']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-danger" style="width: <?php echo $attendance['absent']; ?>%">
                                                            <?php echo $attendance['absent']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($attendance['present'] >= 90): ?>
                                                        <span class="badge bg-success">Excellent</span>
                                                    <?php elseif ($attendance['present'] >= 75): ?>
                                                        <span class="badge bg-primary">Good</span>
                                                    <?php elseif ($attendance['present'] >= 60): ?>
                                                        <span class="badge bg-warning">Fair</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Poor</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-refresh script -->
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(function(){
            window.location.reload();
        }, 30000);
        
        // Attendance Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            const attendanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Excellent (90-100%)', 'Good (75-89%)', 'Fair (60-74%)', 'Poor (<60%)'],
                    datasets: [{
                        label: 'Number of Students',
                        data: [
                            <?php 
                                $excellent = count(array_filter($attendanceData, fn($a) => $a['present'] >= 90));
                                $good = count(array_filter($attendanceData, fn($a) => $a['present'] >= 75 && $a['present'] < 90));
                                $fair = count(array_filter($attendanceData, fn($a) => $a['present'] >= 60 && $a['present'] < 75));
                                $poor = count(array_filter($attendanceData, fn($a) => $a['present'] < 60));
                                echo "$excellent, $good, $fair, $poor";
                            ?>
                        ],
                        backgroundColor: [
                            'rgba(76, 175, 80, 0.7)',
                            'rgba(33, 150, 243, 0.7)',
                            'rgba(255, 193, 7, 0.7)',
                            'rgba(244, 67, 54, 0.7)'
                        ],
                        borderColor: [
                            'rgba(76, 175, 80, 1)',
                            'rgba(33, 150, 243, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(244, 67, 54, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#e6e6e6'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#e6e6e6'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#e6e6e6'
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>