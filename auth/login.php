<?php

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Already logged in?
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$page_title = 'Login';
$errors = [];
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    verify_csrf();

   
    $old_email = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';

   
    if ($old_email === '' || $password === '') {
        $errors[] = 'Please enter your email and password.';
    }

    if (!$errors) {
        
        $stmt = $pdo->prepare('SELECT id, full_name, password_hash, role, status FROM users WHERE email = ?');
        $stmt->execute([$old_email]);
        $user = $stmt->fetch();

       
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Email or password is incorrect.';
        } elseif ($user['status'] === 'blocked') {
           
            $errors[] = 'Your account has been blocked.';
        } else {
            
            session_regenerate_id(true);
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect($user['role'] === 'admin' ? 'admin/index.php' : 'index.php');
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h1 class="h3 mb-4">Log in</h1>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                    value="<?= e($old_email) ?>" required autofocus>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-dark w-100">Log in</button>
        </form>

        <p class="mt-3 text-center">
            New here? <a href="<?= BASE_URL ?>auth/register.php">Create an account</a>
        </p>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>