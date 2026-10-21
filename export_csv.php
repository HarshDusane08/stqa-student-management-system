<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/config/db.php';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

if ($filter === 'defaulter') {
    // Defaulters (< 75% attendance)
    $stmt = $conn->query("SELECT *, ROUND((attended_classes / NULLIF(total_classes, 0)) * 100, 2) AS att_pct FROM students WHERE total_classes > 0 AND (attended_classes / total_classes) * 100 < 75 ORDER BY id DESC");
    $filename = "defaulter_students_report_" . date('Y-m-d') . ".csv";
} else {
    $stmt = $conn->query("SELECT *, ROUND((attended_classes / NULLIF(total_classes, 0)) * 100, 2) AS att_pct FROM students ORDER BY id DESC");
    $filename = "all_students_report_" . date('Y-m-d') . ".csv";
}

$students = $stmt->fetchAll();

// HTTP Headers for direct file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// CSV Column Headers
fputcsv($output, ['Student ID', 'Full Name', 'Email', 'Phone', 'Course', 'Year/Sem', 'Total Classes', 'Attended', 'Attendance %', 'Status']);

foreach ($students as $row) {
    $pct = $row['total_classes'] > 0 ? round(($row['attended_classes'] / $row['total_classes']) * 100, 1) : 0;
    $status = ($pct >= 75) ? 'Eligible' : 'Defaulter';

    fputcsv($output, [
        $row['student_id'],
        $row['full_name'],
        $row['email'],
        $row['phone'],
        $row['course'],
        $row['year_semester'],
        $row['total_classes'],
        $row['attended_classes'],
        $pct . '%',
        $status
    ]);
}

fclose($output);
exit;
?>