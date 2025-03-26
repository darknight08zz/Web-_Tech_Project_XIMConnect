<?php
session_start();

// Check if user is logged in
function checkLogin() {
    // If on admin dashboard, verify login status
    if (strpos($_SERVER['PHP_SELF'], 'admin-dashboard.php') !== false) {
        if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
            // Redirect to login page if not logged in
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
            // Set session to remember login
            $_SESSION['adminLoggedIn'] = true;
            $_SESSION['adminUsername'] = $username;
            
            // Redirect to admin dashboard
            header('Location: admin-dashboard.php');
            exit();
        } else {
            $loginError = 'Invalid username or password';
        }
    }
    
    return $loginError ?? null;
}

// Handle logout
function handleLogout() {
    // Unset all session variables
    session_unset();
    session_destroy();
    
    // Redirect to index page
    header('Location: index.php');
    exit();
}
?>