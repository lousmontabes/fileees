Deleting session data...

<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 4/4/18
 * Time: 22:01
 */

session_start();

// Clear session variables
$_SESSION = array();

// Delete session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

header("Location: ../");

?>