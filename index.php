<?php
// index.php
// Home page (a placeholder until the storefront is built).

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Home';
require __DIR__ . '/includes/header.php';
?>
<h1>Welcome to <?= e(SITE_NAME) ?></h1>
<p class="text-muted">The store is coming soon.</p>
<?php require __DIR__ . '/includes/footer.php'; ?>