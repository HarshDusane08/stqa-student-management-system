<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: index.php?err=" . urlencode("Record not found."));
    exit;
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $course = trim($_POST['course']);
    $year_semester = trim($_POST['year_semester']);
    $total_classes = (int)$_POST['total_classes'];
    $attended_classes = (int)$_POST['attended_classes'];

    if (empty($full_name) || empty($email) || empty($phone) || empty($course) || empty($year_semester)) {
        $err = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid Email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $err = "Phone number must be exactly 10 digits.";
    } elseif ($total_classes < 0 || $attended_classes < 0) {
        $err = "Attendance values cannot be negative.";
    } elseif ($attended_classes > $total_classes) {
        $err = "Attended classes cannot exceed total classes conducted.";
    } else {
        $updateStmt = $conn->prepare("UPDATE students SET full_name = ?, email = ?, phone = ?, course = ?, year_semester = ?, total_classes = ?, attended_classes = ? WHERE id = ?");
        if ($updateStmt->execute([$full_name, $email, $phone, $course, $year_semester, $total_classes, $attended_classes, $id])) {
            header("Location: index.php?msg=" . urlencode("Student record updated successfully!"));
            exit;
        } else {
            $err = "Failed to update record.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
</head>
<body>
<div class="container" style="max-width: 580px;">
    <h2>Edit Student Record</h2>
    <p class="subtitle" style="margin-bottom: 16px;">Updating record for ID: <strong><?php echo htmlspecialchars($student['student_id']); ?></strong></p>

    <?php if ($err): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <form method="POST" action="edit_student.php?id=<?php echo $id; ?>">
        <div class="form-group">
            <label>Student ID (Fixed):</label>
            <input type="text" id="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
        </div>

        <div class="form-group">
            <label for="course">Course:</label>
            <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($student['course']); ?>" required>
        </div>

        <div class="form-group">
            <label for="year_semester">Year / Semester:</label>
            <select id="year_semester" name="year_semester" required>
                <option value="TE - Sem 5" <?php echo ($student['year_semester'] == 'TE - Sem 5') ? 'selected' : ''; ?>>TE - Sem 5</option>
                <option value="TE - Sem 6" <?php echo ($student['year_semester'] == 'TE - Sem 6') ? 'selected' : ''; ?>>TE - Sem 6</option>
                <option value="BE - Sem 7" <?php echo ($student['year_semester'] == 'BE - Sem 7') ? 'selected' : ''; ?>>BE - Sem 7</option>
                <option value="BE - Sem 8" <?php echo ($student['year_semester'] == 'BE - Sem 8') ? 'selected' : ''; ?>>BE - Sem 8</option>
            </select>
        </div>

        <div style="display: flex; gap: 12px;">
            <div class="form-group" style="flex: 1;">
                <label for="total_classes">Total Classes:</label>
                <input type="number" id="total_classes" name="total_classes" value="<?php echo htmlspecialchars($student['total_classes']); ?>" min="0" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="attended_classes">Attended Classes:</label>
                <input type="number" id="attended_classes" name="attended_classes" value="<?php echo htmlspecialchars($student['attended_classes']); ?>" min="0" required>
            </div>
        </div>

        <div style="margin-top: 18px;">
            <button type="submit" class="btn">Update Record</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>