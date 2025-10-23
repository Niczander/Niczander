<?php
require_once('config/DBClass.php');

class StudentDashboard {
    private $db;
    private $studentId;

    public function __construct($studentId) {
        $this->db = new DBClass();
        $this->studentId = $studentId;
    }

    // Get student information
    public function getStudentInfo() {
        return $this->db->readone('userdetails', ['id' => $this->studentId]);
    }

    // Get student grades
    public function getStudentGrades() {
        // In a real implementation, this would join scores with courseunits
        // For demo, we'll use the scores table directly
        $sql = "SELECT s.*, cu.courseunitname 
                FROM scores s 
                JOIN courseunits cu ON s.courseunitid = cu.courseunitcode
                WHERE s.studentid = :studentid";
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->execute([':studentid' => $this->studentId]);
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate totals and grades if no data exists (for demo)
        if (empty($grades)) {
            $subjects = ['Mathematics', 'Physics', 'Chemistry', 'Biology'];
            foreach ($subjects as $subject) {
                $grades[] = [
                    'courseunitname' => $subject,
                    'cwmarks' => rand(60, 95),
                    'finalexam' => rand(50, 100),
                    'total' => 0,
                    'grade' => ''
                ];
            }
        }
        
        // Calculate totals and grades
        foreach ($grades as &$grade) {
            if (!isset($grade['total'])) {
                $grade['total'] = ($grade['cwmarks'] * 0.4) + ($grade['finalexam'] * 0.6);
            }
            
            if (!isset($grade['grade'])) {
                if ($grade['total'] >= 80) $grade['grade'] = 'A';
                elseif ($grade['total'] >= 70) $grade['grade'] = 'B';
                elseif ($grade['total'] >= 60) $grade['grade'] = 'C';
                elseif ($grade['total'] >= 50) $grade['grade'] = 'D';
                else $grade['grade'] = 'F';
            }
        }
        
        return $grades;
    }

    // Get student attendance (simulated as real table doesn't exist)
    public function getStudentAttendance() {
        $months = ['January', 'February', 'March', 'April', 'May', 'June'];
        $attendance = [];
        
        foreach ($months as $month) {
            $present = rand(15, 20);
            $total = 20;
            $attendance[] = [
                'month' => $month,
                'present' => $present,
                'absent' => $total - $present,
                'percentage' => round(($present / $total) * 100)
            ];
        }
        
        return $attendance;
    }

    // Get student fees (simulated as real table doesn't exist)
    public function getStudentFees() {
        return [
            'total_fees' => 2000,
            'paid' => rand(1500, 2000),
            'due' => 2000 - rand(1500, 2000),
            'last_payment' => date('Y-m-d', strtotime('-'.rand(1, 30).' days')),
            'payment_history' => [
                ['date' => date('Y-m-d', strtotime('-60 days')), 'amount' => 500, 'method' => 'Bank Transfer'],
                ['date' => date('Y-m-d', strtotime('-30 days')), 'amount' => 700, 'method' => 'Credit Card'],
            ]
        ];
    }

    // Calculate overall performance
    public function getOverallPerformance() {
        $grades = $this->getStudentGrades();
        $total = 0;
        
        foreach ($grades as $grade) {
            $total += $grade['total'];
        }
        
        return count($grades) > 0 ? $total / count($grades) : 0;
    }
}

// In a real app, student ID would come from session
$studentId = 1; 
$dashboard = new StudentDashboard($studentId);
$studentInfo = $dashboard->getStudentInfo();
$grades = $dashboard->getStudentGrades();
$attendance = $dashboard->getStudentAttendance();
$fees = $dashboard->getStudentFees();
$overallPerformance = $dashboard->getOverallPerformance();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
        
        .badge-grade-a {
            background-color: #4caf50;
        }
        
        .badge-grade-b {
            background-color: #2196f3;
        }
        
        .badge-grade-c {
            background-color: #ffc107;
            color: #000;
        }
        
        .badge-grade-d {
            background-color: #ff9800;
        }
        
        .badge-grade-f {
            background-color: #f44336;
        }
        
        .student-profile {
            display: flex;
            align-items: center;
            padding: 15px;
            background-color: rgba(15, 52, 96, 0.3);
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .student-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 2rem;
            color: white;
        }
        
        .student-info h4 {
            margin-bottom: 5px;
        }
        
        .student-info p {
            margin-bottom: 3px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Student Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-bell"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-user"></i> <?php echo htmlspecialchars($studentInfo['names'] ?? 'Student'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-sign-out-alt"></i></a>
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
                    <a class="nav-link" href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
                    <a class="nav-link" href="courses.php"><i class="fas fa-book"></i> Courses</a>
                    <a class="nav-link" href="#"><i class="fas fa-clipboard-check"></i> Attendance</a>
                    <a class="nav-link" href="#"><i class="fas fa-chart-line"></i> Grades</a>
                    <a class="nav-link" href="#"><i class="fas fa-money-bill-wave"></i> Fees</a>
                    <a class="nav-link" href="#"><i class="fas fa-calendar-alt"></i> Schedule</a>
                </nav>
            </div>

            <div class="col-md-10 content">
                <!-- Student Profile Header -->
                <div class="student-profile">
                    <div class="student-avatar">
                        <?php echo substr($studentInfo['names'] ?? 'S', 0, 1); ?>
                    </div>
                    <div class="student-info">
                        <h4><?php echo htmlspecialchars($studentInfo['names'] ?? 'Student Name'); ?></h4>
                        <p>ID: <?php echo htmlspecialchars($studentInfo['id'] ?? 'N/A'); ?></p>
                        <p>Email: <?php echo htmlspecialchars($studentInfo['email'] ?? 'N/A'); ?></p>
                        <p>Registration No: <?php echo htmlspecialchars($studentInfo['regno'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="stat-value"><?php echo round($overallPerformance); ?>%</div>
                            <div class="stat-label">Overall Performance</div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo $overallPerformance; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="stat-value">
                                <?php 
                                    $overallAttendance = array_reduce($attendance, function($carry, $item) {
                                        return $carry + $item['percentage'];
                                    }, 0) / count($attendance);
                                    echo round($overallAttendance); 
                                ?>%
                            </div>
                            <div class="stat-label">Average Attendance</div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo $overallAttendance; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="stat-value">
                                <?php 
                                    $paidPercentage = ($fees['paid'] / $fees['total_fees']) * 100;
                                    echo round($paidPercentage); 
                                ?>%
                            </div>
                            <div class="stat-label">Fees Paid</div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo $paidPercentage; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Grades Chart -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-line me-2"></i> Grades Overview
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="gradesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Grades Table -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-book me-2"></i> My Grades</span>
                                <span class="badge p-2" style="background-color: var(--accent-color);">
                                    <?php echo count($grades); ?> Courses
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Coursework (40%)</th>
                                                <th>Final Exam (60%)</th>
                                                <th>Total Score</th>
                                                <th>Grade</th>
                                                <th>Performance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($grades as $grade): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($grade['courseunitname'] ?? $grade['course'] ?? 'N/A'); ?></td>
                                                <td><?php echo $grade['cwmarks']; ?></td>
                                                <td><?php echo $grade['finalexam']; ?></td>
                                                <td><?php echo round($grade['total']); ?></td>
                                                <td>
                                                    <span class="badge badge-grade-<?php echo strtolower($grade['grade']); ?>">
                                                        <?php echo $grade['grade']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar 
                                                            <?php 
                                                                if ($grade['total'] >= 80) echo 'bg-success';
                                                                elseif ($grade['total'] >= 60) echo 'bg-primary';
                                                                elseif ($grade['total'] >= 50) echo 'bg-warning';
                                                                else echo 'bg-danger';
                                                            ?>"
                                                            style="width: <?php echo $grade['total']; ?>%">
                                                            <?php echo round($grade['total']); ?>%
                                                        </div>
                                                    </div>
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
                
                <!-- Attendance and Fees Row -->
                <div class="row">
                    <!-- Attendance Table -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-clipboard-check me-2"></i> My Attendance</span>
                                <span class="badge p-2" style="background-color: #4caf50;">
                                    <?php echo round($overallAttendance); ?>% Overall
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                                <th>Attendance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendance as $record): ?>
                                            <tr>
                                                <td><?php echo $record['month']; ?></td>
                                                <td><?php echo $record['present']; ?></td>
                                                <td><?php echo $record['absent']; ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar 
                                                            <?php 
                                                                if ($record['percentage'] >= 90) echo 'bg-success';
                                                                elseif ($record['percentage'] >= 75) echo 'bg-primary';
                                                                elseif ($record['percentage'] >= 60) echo 'bg-warning';
                                                                else echo 'bg-danger';
                                                            ?>"
                                                            style="width: <?php echo $record['percentage']; ?>%">
                                                            <?php echo $record['percentage']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fees Information -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-money-bill-wave me-2"></i> My Fees</span>
                                <span class="badge p-2 
                                    <?php echo ($fees['due'] == 0) ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo ($fees['due'] == 0) ? 'Paid in Full' : 'Balance Due'; ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <h5>Fees Summary</h5>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Fees:</span>
                                        <strong>$<?php echo number_format($fees['total_fees'], 2); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Paid:</span>
                                        <strong>$<?php echo number_format($fees['paid'], 2); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Due:</span>
                                        <strong class="<?php echo ($fees['due'] > 0) ? 'text-danger' : 'text-success'; ?>">
                                            $<?php echo number_format($fees['due'], 2); ?>
                                        </strong>
                                    </div>
                                    <div class="progress mt-3" style="height: 10px;">
                                        <div class="progress-bar" style="width: <?php echo $paidPercentage; ?>%"></div>
                                    </div>
                                </div>
                                
                                <h5>Payment History</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($fees['payment_history'] as $payment): ?>
                                            <tr>
                                                <td><?php echo $payment['date']; ?></td>
                                                <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                                <td><?php echo $payment['method']; ?></td>
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
        
        // Grades Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('gradesChart').getContext('2d');
            const gradesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($grades, 'courseunitname')); ?>,
                    datasets: [{
                        label: 'Coursework (40%)',
                        data: <?php echo json_encode(array_column($grades, 'cwmarks')); ?>,
                        backgroundColor: 'rgba(33, 150, 243, 0.7)',
                        borderColor: 'rgba(33, 150, 243, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Final Exam (60%)',
                        data: <?php echo json_encode(array_column($grades, 'finalexam')); ?>,
                        backgroundColor: 'rgba(76, 175, 80, 0.7)',
                        borderColor: 'rgba(76, 175, 80, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Total Score',
                        data: <?php echo json_encode(array_map(function($g) { return round($g['total']); }, $grades)); ?>,
                        backgroundColor: 'rgba(255, 193, 7, 0.7)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1,
                        type: 'line',
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
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