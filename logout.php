<?php
// CRITICAL: Must start the session to be able to destroy it.
session_start();

// 1. Destroy all variables in the session
$_SESSION = array();

// 2. Destroy the session cookie by setting a past expiration time
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session itself
session_destroy();

// Redirect the user to the home page
header("Location: index.php");
exit;
?>
