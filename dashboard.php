<?php
session_start();

// If no session, check remember-me cookie
if (!isset($_SESSION['username']) && isset($_COOKIE['rememberme'])) {
    $_SESSION['username'] = $_COOKIE['rememberme'];
    $_SESSION['login_time'] = date("Y-m-d H:i:s");
}

// Redirect to login if not authenticated
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$login_time = $_SESSION['login_time'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    <p>Logged in at: <?php echo $login_time; ?></p>

    <a href="logout.php">Logout</a>
</body>
</html>
