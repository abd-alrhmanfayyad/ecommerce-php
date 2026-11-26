<nav class="navbar navbar-expand navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>index.php"><?= e(SITE_NAME) ?></a>
        <div class="navbar-nav ms-auto align-items-center">
            <a class="nav-link" href="<?= BASE_URL ?>shop.php">Shop</a>
            <a class="nav-link" href="<?= BASE_URL ?>cart.php">Cart</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/index.php">Admin</a>
                <?php endif; ?>
                <span class="navbar-text ms-2">Hi, <?= e($_SESSION['user_name']) ?></span>
                <form method="POST" action="<?= BASE_URL ?>auth/logout.php" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-link nav-link">Logout</button>
                </form>
            <?php else: ?>
                <a class="nav-link" href="<?= BASE_URL ?>auth/login.php">Login</a>
                <a class="nav-link" href="<?= BASE_URL ?>auth/register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>