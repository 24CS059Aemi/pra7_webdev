<?php
session_start();
include "users.php";

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (isset($users[$username]) && $users[$username] === $password) {
    // Set session
    $_SESSION['username'] = $username;
    $_SESSION['login_time'] = date("Y-m-d H:i:s");

    // Remember me with cookie
    if (!empty($_POST['remember'])) {
        setcookie("rememberme", $username, time() + 3600, "/", "", false, true);
    }

    header("Location: dashboard.php");
    exit;
} else {
    echo "❌ Invalid username or password. <a href='login.php'>Try again</a>";
}
