<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/config/db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$err = isset($_GET['err']) ? $_GET['err'] : '';

// 1. Fetch Aggregated Metrics for Cards
$totalStudents = $conn->query("SELECT COUNT(*) FROM students")->fetchColumn();
$defaulterCount = $conn->query("SELECT COUNT(*) FROM students WHERE total_classes > 0 AND (attended_classes / total_classes) * 100 < 75")->fetchColumn();
$avgAttendance = $conn->query("SELECT ROUND(AVG((attended_classes / NULLIF(total_classes, 0)) * 100), 1) FROM students")->fetchColumn();
$avgAttendance = $avgAttendance ? $avgAttendance . '%' : '0%';
$activeCourses = $conn->query("SELECT COUNT(DISTINCT course) FROM students")->fetchColumn();

// 2. Filter & Search Query
if ($filter === 'defaulter') {
    if ($search !== '') {
        $stmt = $conn->prepare("SELECT * FROM students WHERE (student_id LIKE ? OR full_name LIKE ?) AND total_classes > 0 AND (attended_classes / total_classes) * 100 < 75 ORDER BY id DESC");
        $stmt->execute(["%{$search}%", "%{$search}%"]);
    } else {
        $stmt = $conn->query("SELECT * FROM students WHERE total_classes > 0 AND (attended_classes / total_classes) * 100 < 75 ORDER BY id DESC");
    }
} else {
    if ($search !== '') {
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id LIKE ? OR full_name LIKE ? ORDER BY id DESC");
        $stmt->execute(["%{$search}%", "%{$search}%"]);
    } else {
        $stmt = $conn->query("SELECT * FROM students ORDER BY id DESC");
    }
}
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Management Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
</head>
<body>
<div class="container">
    <header>
        <div>
            <h1>Student Management & Attendance Portal</h1>
            <p class="subtitle">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_user']); ?></strong> | STQA Mini Project</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="add_student.php" class="btn">+ Register Student</a>
            <a href="logout.php" class="btn btn-secondary">Logout</a>
        </div>
    </header>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <?php if ($err): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <!-- Metric Summary Cards -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-title">Total Registered</div>
            <div class="metric-value"><?php echo $totalStudents; ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-title">Defaulters (&lt; 75%)</div>
            <div class="metric-value" style="color: #dc2626;"><?php echo $defaulterCount; ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-title">Average Attendance</div>
            <div class="metric-value" style="color: #2563eb;"><?php echo $avgAttendance; ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-title">Active Courses</div>
            <div class="metric-value"><?php echo $activeCourses; ?></div>
        </div>
    </div>

    <!-- Toolbar: Search + Filter + Export -->
    <div class="toolbar">
        <form method="GET" action="index.php" class="search-form">
            <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
            <input type="text" name="search" placeholder="Search ID or Name..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn">Search</button>
            <?php if ($search !== ''): ?>
                <a href="index.php?filter=<?php echo htmlspecialchars($filter); ?>" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>

        <div style="display: flex; gap: 8px;">
            <?php if ($filter === 'defaulter'): ?>
                <a href="index.php" class="btn btn-secondary">Show All Records</a>
                <a href="export_csv.php?filter=defaulter" class="btn btn-success">Export Defaulters (CSV)</a>
            <?php else: ?>
                <a href="index.php?filter=defaulter" class="btn btn-danger">View Defaulters Only</a>
                <a href="export_csv.php?filter=all" class="btn btn-success">Export All (CSV)</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Course</th>
                <th>Year/Sem</th>
                <th>Attendance</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($students) > 0): ?>
                <?php foreach ($students as $row): 
                    $pct = $row['total_classes'] > 0 ? round(($row['attended_classes'] / $row['total_classes']) * 100, 1) : 0;
                    $isDefaulter = ($pct < 75);
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['student_id']); ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($row['full_name']); ?><br>
                        <small style="color: #64748b;"><?php echo htmlspecialchars($row['email']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                    <td><?php echo htmlspecialchars($row['year_semester']); ?></td>
                    <td>
                        <?php echo "{$row['attended_classes']} / {$row['total_classes']}"; ?>
                        <strong style="margin-left: 4px;"><?php echo "({$pct}%)"; ?></strong>
                    </td>
                    <td>
                        <?php if ($isDefaulter): ?>
                            <span class="badge badge-danger">Defaulter</span>
                        <?php else: ?>
                            <span class="badge badge-success">Eligible</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                        <a href="delete_student.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete('<?php echo htmlspecialchars($row['student_id']); ?>');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b; padding: 20px;">No student records found matching criteria.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>