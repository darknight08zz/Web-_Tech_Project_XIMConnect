<?php
session_start();

// Check if user is logged in
function checkLogin() {
    if (strpos($_SERVER['PHP_SELF'], 'admin-dashboard.php') !== false) {
        if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
            header('Location: admin-login.php');
            exit();
        }
    }
}

// Handle login form submission
function handleLoginSubmission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Load admin users XML
        $xmlPath = 'admin-users.xml';
        $xml = simplexml_load_file($xmlPath);
        
        $authenticated = false;
        foreach ($xml->user as $user) {
            if ($username === (string)$user->username && $password === (string)$user->password) {
                $authenticated = true;
                break;
            }
        }
        
        if ($authenticated) {
            $_SESSION['adminLoggedIn'] = true;
            $_SESSION['adminUsername'] = $username;
            header('Location: admin-dashboard.php');
            exit();
        } else {
            $loginError = 'Invalid username or password';
        }
    }
    
    return $loginError ?? null;
}

function handleLogout() {
    session_unset();
    session_destroy();
    
    // Redirect to index page
    header('Location: index.php');
    exit();
}
?>
