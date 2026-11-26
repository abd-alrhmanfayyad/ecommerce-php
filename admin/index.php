<?php
// admin/index.php
// Admin dashboard (a placeholder until the dashboard is built).

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_guard.php';   // admins only

$page_title = 'Admin';
require __DIR__ . '/../includes/header.php';
?>
<h1>Admin dashboard</h1>
<p class="text-muted">Coming soon.</p>
<?php require __DIR__ . '/../includes/footer.php'; ?>