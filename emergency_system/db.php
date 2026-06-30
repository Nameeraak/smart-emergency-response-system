<?php
// db.php — Secure Database Connection
// Place in ROOT of project

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'emerge_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('
    <div style="font-family:monospace;background:#1a1a2e;color:#e74c3c;
                padding:40px;text-align:center;min-height:100vh;display:flex;
                align-items:center;justify-content:center;flex-direction:column;">
        <div style="font-size:3rem;">⚠️</div>
        <h2 style="color:#fff;margin:12px 0 6px;">Database Connection Failed</h2>
        <p style="color:#aaa;">'.mysqli_connect_error().'</p>
        <p style="color:#666;font-size:.85rem;margin-top:12px;">
            Make sure XAMPP is running and database <strong style="color:#e74c3c;">emerge_db</strong> is created.
        </p>
    </div>');
}

mysqli_set_charset($conn, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 7200,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// CSRF token helper
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="'.csrf_token().'">';
}
function verify_csrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        die('<div style="color:red;padding:40px;text-align:center;">⚠️ Invalid request. Please go back and try again.</div>');
    }
}
?>
