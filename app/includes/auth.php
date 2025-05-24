<?php
// Extend session lifetime (7 days = 604800 seconds)
ini_set('session.gc_maxlifetime', 604800);
ini_set('session.cookie_lifetime', 604800);

session_set_cookie_params([
    'lifetime' => 604800,
    'path' => '/',
    'domain' => '', // leave empty for localhost
    'secure' => false, // set to true if using HTTPS
    'samesite' => 'Lax'
]);

// Start the session
session_start();
if (!isset($_SESSION['user'])) {
    echo "<script>
        alert('⚠️ You must log in first.');
        window.location.href = 'index.php';
    </script>";
    exit;
}

?>
