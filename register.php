<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/config/db.php';

$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $err = "All fields are strictly mandatory.";
    } elseif (strlen($username) < 3) {
        $err = "Username must be at least 3 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $err = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $err = "Passwords do not match.";
    } else {
        // Check duplicate username or email
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        
        if ($checkStmt->rowCount() > 0) {
            $err = "Username or Email is already registered.";
        } else {
            // Secure Password Hashing using BCRYPT
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $insertStmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($insertStmt->execute([$username, $email, $hashedPassword])) {
                header("Location: login.php?msg=" . urlencode("Registration successful! Please login."));
                exit;
            } else {
                $err = "Database error: Registration failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration - Student Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container" style="max-width: 440px; margin-top: 50px;">
    <h2 style="text-align: center; margin-bottom: 6px;">Create Admin Account</h2>
    <p class="subtitle" style="text-align: center; margin-bottom: 20px;">STQA Mini Project - User Registration</p>

    <?php if ($err): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Choose a username" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" placeholder="e.g. admin@college.edu" required>
        </div>

        <div class="form-group">
            <label for="password">Password (min 6 characters):</label>
            <input type="password" id="password" name="password" placeholder="Create password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-type password" required>
        </div>

        <button type="submit" class="btn" style="width: 100%; margin-top: 8px;">Register Account</button>
    </form>

    <div style="text-align: center; margin-top: 18px; font-size: 13px;">
        Already have an account? <a href="login.php" style="color: #2563eb; font-weight: bold; text-decoration: none;">Login here</a>
    </div>
</div>
</body>
</html>