<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/config/db.php';

$err = '';
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $err = "Both username and password are required.";
    } else {
        // Query user from database
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Password matches hash
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $err = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Student Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container" style="max-width: 440px; margin-top: 60px;">
    <h2 style="text-align: center; margin-bottom: 6px;">Portal Login</h2>
    <p class="subtitle" style="text-align: center; margin-bottom: 20px;">STQA Mini Project - Authentication Gateway</p>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <?php if ($err): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn" style="width: 100%; margin-top: 8px;">Login to Dashboard</button>
    </form>

    <div style="text-align: center; margin-top: 18px; font-size: 13px;">
        Don't have an account? <a href="register.php" style="color: #2563eb; font-weight: bold; text-decoration: none;">Register New Account</a>
    </div>
</div>
</body>
</html>