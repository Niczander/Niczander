<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';

$response = ['success' => false];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request');
    }

    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if (!in_array($action, ['create', 'update'])) {
        throw new Exception('Invalid action');
    }

    $database = new Database();
    $pdo = $database->getConnection();

    $first_name = htmlspecialchars(stripslashes(trim($_POST['first_name'] ?? '')));
    $last_name = htmlspecialchars(stripslashes(trim($_POST['last_name'] ?? '')));
    $reg_number = strtoupper(trim(htmlspecialchars(stripslashes(trim($_POST['reg_number'] ?? '')))));
    $gender = htmlspecialchars(stripslashes(trim($_POST['gender'] ?? '')));
    $email = htmlspecialchars(stripslashes(trim($_POST['email'] ?? '')));
    $phone = htmlspecialchars(stripslashes(trim($_POST['phone'] ?? '')));
    $year = (int)($_POST['year_of_study'] ?? 0);

    if (!$first_name || !$last_name || !$reg_number || !$gender || !$email || !$year) {
        throw new Exception('Please fill in all required fields.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address.');
    }

    if ($action === 'create') {
        // Duplicate reg check
        $dup = $pdo->prepare('SELECT COUNT(*) FROM students WHERE reg_number = ?');
        $dup->execute([$reg_number]);
        if ($dup->fetchColumn() > 0) {
            throw new Exception('Duplicate registration number. Please use a unique value.');
        }

        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO students (first_name, last_name, reg_number, gender, email, phone, year_of_study) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$first_name, $last_name, $reg_number, $gender, $email, $phone, $year]);
        $studentId = (int)$pdo->lastInsertId();

        // Enrollments
        if (!empty($_POST['courses']) && is_array($_POST['courses'])) {
            $ins = $pdo->prepare('INSERT IGNORE INTO student_courses (student_id, course_id, semester, academic_year) VALUES (?, ?, 1, YEAR(CURDATE()))');
            foreach ($_POST['courses'] as $cid) { $ins->execute([$studentId, (int)$cid]); }
        }

        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'Student added successfully!';
    } else {
        $studentId = (int)($_POST['id'] ?? 0);
        if (!$studentId) { throw new Exception('Missing student id.'); }

        // Duplicate reg excluding current
        $dup = $pdo->prepare('SELECT COUNT(*) FROM students WHERE reg_number = ? AND id <> ?');
        $dup->execute([$reg_number, $studentId]);
        if ($dup->fetchColumn() > 0) {
            throw new Exception('Duplicate registration number. Please use a unique value.');
        }

        $pdo->beginTransaction();
        $stmt = $pdo->prepare('UPDATE students SET first_name = ?, last_name = ?, reg_number = ?, gender = ?, email = ?, phone = ?, year_of_study = ? WHERE id = ?');
        $stmt->execute([$first_name, $last_name, $reg_number, $gender, $email, $phone, $year, $studentId]);

        // Sync enrollments
        $selected = [];
        if (!empty($_POST['courses']) && is_array($_POST['courses'])) {
            $selected = array_map('intval', $_POST['courses']);
        }

        if (empty($selected)) {
            $delAll = $pdo->prepare('DELETE FROM student_courses WHERE student_id = ?');
            $delAll->execute([$studentId]);
        } else {
            $placeholders = implode(',', array_fill(0, count($selected), '?'));
            $params = array_merge([$studentId], $selected);
            $del = $pdo->prepare("DELETE FROM student_courses WHERE student_id = ? AND course_id NOT IN ($placeholders)");
            $del->execute($params);

            $existing = $pdo->prepare('SELECT course_id FROM student_courses WHERE student_id = ?');
            $existing->execute([$studentId]);
            $existingIds = array_map('intval', array_column($existing->fetchAll(PDO::FETCH_ASSOC), 'course_id'));
            $toInsert = array_diff($selected, $existingIds);
            if (!empty($toInsert)) {
                $ins = $pdo->prepare('INSERT INTO student_courses (student_id, course_id, semester, academic_year) VALUES (?, ?, 1, YEAR(CURDATE()))');
                foreach ($toInsert as $cid) { $ins->execute([$studentId, (int)$cid]); }
            }
        }

        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'Student updated successfully!';
    }
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
    $response['message'] = $e->getCode() === '23000' ? 'Duplicate registration number. Please use a unique value.' : ('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
<?php


