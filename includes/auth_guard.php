<?php
// includes/auth_guard.php
// Put it at the top of every admin page, after the 3 require lines:
//     require_once __DIR__ . '/../includes/auth_guard.php';
// Only logged-in admins get past this file.

// Not logged in (authentication) → go log in
if (!isset($_SESSION['user_id'])) {
    set_flash('danger', 'Please log in first.');
    redirect('auth/login.php');
}

// Logged in, but not an admin (authorization) → go home
if ($_SESSION['user_role'] !== 'admin') {
    set_flash('danger', "You don't have access to that page.");
    redirect('index.php');
}