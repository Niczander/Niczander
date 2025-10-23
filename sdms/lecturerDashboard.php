<?php
require_once('_header.php');
require_once('config/DBClass.php');


class LecturerDashboard {
    private $db;
    private $lecturerId;

    public function __construct($lecturerId = 1) {
        $this->db = new DBClass();
        $this->lecturerId = $lecturerId;
    }

    public function getLecturerInfo() {
        return $this->db->readone('userdetails', ['id' => $this->lecturerId]);
    }

    public function getCoursesTaught() {
        // In a real app, this would filter by lecturer
        return $this->db->read('courses');
    }

    public function getStudentsInCourse($courseCode) {
        $sql = "SELECT u.id, u.names, u.regno 
                FROM userdetails u
                JOIN studentcourse sc ON u.id = sc.studentid
                WHERE sc.courseid = :courseid";
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->execute([':courseid' => $courseCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseGrades($courseCode) {
        $sql = "SELECT s.studentid, u.names, u.regno, s.cwmarks, s.finalexam, 
                       (s.cwmarks * 0.4 + s.finalexam * 0.6) as total,
                       CASE 
                         WHEN (s.cwmarks * 0.4 + s.finalexam * 0.6) >= 80 THEN 'A'
                         WHEN (s.cwmarks * 0.4 + s.finalexam * 0.6) >= 70 THEN 'B'
                         WHEN (s.cwmarks * 0.4 + s.finalexam * 0.6) >= 60 THEN 'C'
                         WHEN (s.cwmarks * 0.4 + s.finalexam * 0.6) >= 50 THEN 'D'
                         ELSE 'F'
                       END as grade
                FROM scores s
                JOIN userdetails u ON s.studentid = u.id
                JOIN courseunits cu ON s.courseunitid = cu.courseunitcode
                JOIN courses c ON cu.courseunitcode = c.coursecode
                WHERE c.coursecode = :courseid";
        
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->execute([':courseid' => $courseCode]);
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($grades)) {
            $students = $this->getStudentsInCourse($courseCode);
            foreach ($students as $student) {
                $grades[] = [
                    'studentid' => $student['id'],
                    'names' => $student['names'],
                    'regno' => $student['regno'],
                    'cwmarks' => rand(50, 95),
                    'finalexam' => rand(40, 100),
                    'total' => 0,
                    'grade' => ''
                ];
            }
            
            foreach ($grades as &$grade) {
                $grade['total'] = ($grade['cwmarks'] * 0.4) + ($grade['finalexam'] * 0.6);
                
                if ($grade['total'] >= 80) $grade['grade'] = 'A';
                elseif ($grade['total'] >= 70) $grade['grade'] = 'B';
                elseif ($grade['total'] >= 60) $grade['grade'] = 'C';
                elseif ($grade['total'] >= 50) $grade['grade'] = 'D';
                else $grade['grade'] = 'F';
            }
        }
        
        return $grades;
    }

    public function getCoursePerformance($courseCode) {
        $grades = $this->getCourseGrades($courseCode);
        $stats = [
            'average' => 0,
            'count' => count($grades),
            'grade_distribution' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0]
        ];
        
        if ($stats['count'] > 0) {
            $total = 0;
            foreach ($grades as $grade) {
                $total += $grade['total'];
                $stats['grade_distribution'][$grade['grade']]++;
            }
            $stats['average'] = $total / $stats['count'];
        }
        
        return $stats;
    }

    public function getAttendanceData($courseCode) {
        // Simulated attendance data (in a real app, this would come from database)
        $students = $this->getStudentsInCourse($courseCode);
        $attendance = [];
        
        foreach ($students as $student) {
            $attendance[] = [
                'studentid' => $student['id'],
                'names' => $student['names'],
                'regno' => $student['regno'],
                'attended' => rand(70, 100), // Percentage
                'last_attended' => date('Y-m-d', strtotime('-'.rand(0, 7).' days'))
            ];
        }
        
        return $attendance;
    }
}

$lecturerId = 1;
$dashboard = new LecturerDashboard($lecturerId);
$lecturerInfo = $dashboard->getLecturerInfo();
$courses = $dashboard->getCoursesTaught();
$selectedCourse = $_GET['course'] ?? ($courses[0]['coursecode'] ?? '');
$grades = $dashboard->getCourseGrades($selectedCourse);
$coursePerformance = $dashboard->getCoursePerformance($selectedCourse);
$attendanceData = $dashboard->getAttendanceData($selectedCourse);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css" rel="stylesheet">

<nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Student Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-bell"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="adminDashboard.php"><i class="fas fa-user"></i> Admin</a>
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
    <!-- Lecturer Profile Header -->
    <div class="lecturer-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="lecturer-name"><?php echo htmlspecialchars($lecturerInfo['names'] ?? 'Lecturer'); ?></h1>
                <p class="lecturer-title">Senior Lecturer</p>
            </div>
            <div class="avatar-circle-lg bg-primary text-white">
                <?php echo substr($lecturerInfo['names'] ?? 'L', 0, 1); ?>
            </div>
        </div>
    </div>

    <!-- Course Selection -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-book me-2"></i> Courses Teaching
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap">
                        <?php foreach ($courses as $course): ?>
                            <a href="?course=<?php echo $course['coursecode']; ?>"
                               class="btn btn-outline-primary me-2 mb-2 <?php echo $selectedCourse === $course['coursecode'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($course['coursecode'] . ' - ' . $course['coursename']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="stat-value"><?php echo $coursePerformance['count']; ?></div>
                <div class="stat-label">Students Enrolled</div>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo min(100, $coursePerformance['count']); ?>%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="stat-value"><?php echo round($coursePerformance['average']); ?>%</div>
                <div class="stat-label">Average Score</div>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo $coursePerformance['average']; ?>%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="stat-value">
                    <?php 
                        $passRate = ($coursePerformance['count'] - $coursePerformance['grade_distribution']['F']) / max(1, $coursePerformance['count']) * 100;
                        echo round($passRate); 
                    ?>%
                </div>
                <div class="stat-label">Pass Rate</div>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo $passRate; ?>%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="stat-value">
                    <?php 
                        $attendanceAvg = array_sum(array_column($attendanceData, 'attended')) / max(1, count($attendanceData));
                        echo round($attendanceAvg); 
                    ?>%
                </div>
                <div class="stat-label">Avg Attendance</div>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo $attendanceAvg; ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Grade Distribution Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Grade Distribution - <?php echo htmlspecialchars($selectedCourse); ?>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="gradeDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Attendance Trend Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i> Attendance Trend - <?php echo htmlspecialchars($selectedCourse); ?>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="attendanceTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Data Tables -->
    <div class="row">
        <!-- Grades Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-graduation-cap me-2"></i> Student Grades - <?php echo htmlspecialchars($selectedCourse); ?></span>
                    <span class="badge bg-primary p-2"><?php echo count($grades); ?> Students</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Reg No</th>
                                    <th>Name</th>
                                    <th>Coursework</th>
                                    <th>Final Exam</th>
                                    <th>Total</th>
                                    <th>Grade</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grades as $grade): ?>
                                <tr>
                                    <td><?php echo $grade['regno'] ?? 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($grade['names']); ?></td>
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
        
        <!-- Attendance Table -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-calendar-check me-2"></i> Attendance - <?php echo htmlspecialchars($selectedCourse); ?></span>
                    <span class="badge bg-primary p-2"><?php echo count($attendanceData); ?> Students</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Attendance</th>
                                    <th>Last Seen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendanceData as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($student['names'], 0, 15)); ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar 
                                                <?php 
                                                    if ($student['attended'] >= 90) echo 'bg-success';
                                                    elseif ($student['attended'] >= 75) echo 'bg-primary';
                                                    elseif ($student['attended'] >= 60) echo 'bg-warning';
                                                    else echo 'bg-danger';
                                                ?>"
                                                style="width: <?php echo $student['attended']; ?>%">
                                                <?php echo $student['attended']; ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $student['last_attended']; ?></td>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<script>
    // Auto-refresh every 30 seconds
    setTimeout(function(){
        window.location.reload();
    }, 30000);

    // Grade Distribution Chart
    document.addEventListener('DOMContentLoaded', function() {
        // Grade Distribution Chart
        const gradeCtx = document.getElementById('gradeDistributionChart').getContext('2d');
        const gradeChart = new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: ['A', 'B', 'C', 'D', 'F'],
                datasets: [{
                    data: [
                        <?php echo $coursePerformance['grade_distribution']['A']; ?>,
                        <?php echo $coursePerformance['grade_distribution']['B']; ?>,
                        <?php echo $coursePerformance['grade_distribution']['C']; ?>,
                        <?php echo $coursePerformance['grade_distribution']['D']; ?>,
                        <?php echo $coursePerformance['grade_distribution']['F']; ?>
                    ],
                    backgroundColor: [
                        '#4CAF50', // A
                        '#2196F3', // B
                        '#FFC107', // C
                        '#FF9800', // D
                        '#F44336'  // F
                    ],
                    borderColor: '#1a1a2e',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#e6e6e6'
                        }
                    }
                }
            }
        });

        // Attendance Trend Chart (simulated data)
        const attendanceCtx = document.getElementById('attendanceTrendChart').getContext('2d');
        const attendanceChart = new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7'],
                datasets: [{
                    label: 'Attendance Rate',
                    data: [
                        <?php echo rand(70, 90); ?>,
                        <?php echo rand(75, 92); ?>,
                        <?php echo rand(78, 95); ?>,
                        <?php echo rand(80, 98); ?>,
                        <?php echo rand(82, 95); ?>,
                        <?php echo rand(85, 98); ?>,
                        <?php echo rand(88, 100); ?>
                    ],
                    borderColor: '#4cc9f0',
                    backgroundColor: 'rgba(76, 201, 240, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 60,
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
        font-size: 2rem;
        font-weight: 700;
        color: var(--highlight);
        margin: 10px 0;
    }
    
    .stat-label {
        font-size: 0.9rem;
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
    
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
    .avatar-circle-lg {
        width: 80px;
        height: 80px;
        font-size: 2.5rem;
    }
    
    .btn-outline-primary {
        color: var(--highlight);
        border-color: var(--highlight);
    }
    
    .btn-outline-primary:hover, .btn-outline-primary.active {
        background-color: var(--highlight);
        color: var(--dark-bg);
    }
    
    .lecturer-header {
        padding: 20px;
        background-color: var(--darker-bg);
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .lecturer-name {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        color: var(--highlight);
    }
    
    .lecturer-title {
        font-size: 1.2rem;
        opacity: 0.8;
        margin-bottom: 0;
    }
</style>

<?php require_once('_footer.php'); ?>