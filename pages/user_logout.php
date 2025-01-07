<?php

require '../db/conn.php';

if (isset($_COOKIE['auth_token'])) {
    $stmt = $conn->prepare("UPDATE user_logins SET is_active = 0, logout_time = NOW() WHERE token = ?");
    $stmt->bind_param("s", $_COOKIE['auth_token']);
    $stmt->execute();
}


// Clear cookies
setcookie("user_id", "", time() - 3600, "/", "", true, true);
setcookie("auth_token", "", time() - 3600, "/", "", true, true);


header("Location: /view/user_login.php");
exit;
?>
