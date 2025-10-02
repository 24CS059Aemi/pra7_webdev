<?php
session_start();
session_unset();
session_destroy();

// Remove cookies
setcookie("rememberme", "", time() - 3600, "/");

header("Location: login.php");
exit;
