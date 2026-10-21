<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: index.php?msg=" . urlencode("Student record deleted successfully."));
        exit;
    } else {
        header("Location: index.php?err=" . urlencode("Database error: Could not delete record."));
        exit;
    }
} else {
    header("Location: index.php?err=" . urlencode("Invalid Student ID."));
    exit;
}
?>