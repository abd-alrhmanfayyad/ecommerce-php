
<?php
// auth/logout.php
// Logs the user out. POST only, with CSRF (a plain link could be triggered by another site).

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
verify_csrf();

// Empty the session and get a new id (the old session is deleted)
$_SESSION = [];
session_regenerate_id(true);

set_flash('success', 'You have been logged out.');
redirect('auth/login.php');