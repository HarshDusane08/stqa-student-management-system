<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/config/db.php';

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $course = trim($_POST['course']);
    $year_semester = trim($_POST['year_semester']);
    $total_classes = (int)$_POST['total_classes'];
    $attended_classes = (int)$_POST['attended_classes'];

    // Validation
    if (empty($student_id) || empty($full_name) || empty($email) || empty($phone) || empty($course) || empty($year_semester)) {
        $err = "All mandatory fields must be filled.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid Email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $err = "Phone number must be exactly 10 digits.";
    } elseif ($total_classes < 0 || $attended_classes < 0) {
        $err = "Attendance values cannot be negative.";
    } elseif ($attended_classes > $total_classes) {
        $err = "Attended classes cannot exceed total classes conducted.";
    } else {
        $check = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
        $check->execute([$student_id]);
        if ($check->rowCount() > 0) {
            $err = "Duplicate Record: Student ID '{$student_id}' already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO students (student_id, full_name, email, phone, course, year_semester, total_classes, attended_classes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$student_id, $full_name, $email, $phone, $course, $year_semester, $total_classes, $attended_classes])) {
                header("Location: index.php?msg=" . urlencode("Student registered successfully!"));
                exit;
            } else {
                $err = "Database error: Could not insert record.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Student</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
</head>
<body>
<div class="container" style="max-width: 580px;">
    <h2>Register New Student</h2>
    <p class="subtitle" style="margin-bottom: 16px;">Fill in student details and attendance metrics.</p>

    <?php if ($err): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <form method="POST" action="add_student.php" onsubmit="return validateStudentForm();">
        <div class="form-group">
            <label for="student_id">Student ID:</label>
            <input type="text" id="student_id" name="student_id" placeholder="e.g. BE-COMP-05" required>
        </div>

        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" placeholder="e.g. Aniket Deshmukh" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" placeholder="e.g. aniket@example.com" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number (10 Digits):</label>
            <input type="text" id="phone" name="phone" placeholder="e.g. 9822334455" required>
        </div>

        <div class="form-group">
            <label for="course">Course:</label>
            <input type="text" id="course" name="course" value="Computer Engineering" required>
        </div>

        <div class="form-group">
            <label for="year_semester">Year / Semester:</label>
            <select id="year_semester" name="year_semester" required>
                <option value="TE - Sem 5">TE - Sem 5</option>
                <option value="TE - Sem 6">TE - Sem 6</option>
                <option value="BE - Sem 7" selected>BE - Sem 7</option>
                <option value="BE - Sem 8">BE - Sem 8</option>
            </select>
        </div>

        <div style="display: flex; gap: 12px;">
            <div class="form-group" style="flex: 1;">
                <label for="total_classes">Total Classes:</label>
                <input type="number" id="total_classes" name="total_classes" value="40" min="0" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="attended_classes">Attended Classes:</label>
                <input type="number" id="attended_classes" name="attended_classes" value="32" min="0" required>
            </div>
        </div>

        <div style="margin-top: 18px;">
            <button type="submit" class="btn">Save Student Record</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>