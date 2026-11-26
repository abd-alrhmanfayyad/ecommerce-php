<?php
// includes/header.php
// The top of every page: <head>, Bootstrap, navbar and the flash message.
// A page can set $page_title before including this file.
$flash = get_flash();   // (1) take the one-time message (or null)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title ?? SITE_NAME) ?></title>   <!-- (2) make it safe -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require __DIR__ . '/navbar.php'; ?>
<main class="container py-4">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>