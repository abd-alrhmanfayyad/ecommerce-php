<?php
// auth/register.php
// Customer registration: shows the form (GET) and creates the account (POST).

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Register';
$errors = [];
$old = ['full_name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Security first
    verify_csrf();

    // Read the inputs
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $old['phone']     = trim($_POST['phone'] ?? '');
    $password         = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validation
    if ($old['full_name'] === '') {
        $errors[] = 'Full name is required.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email.';
    }
    if (strlen($old['phone']) > 20) {
        $errors[] = 'Phone number is too long.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // Is the email already registered?
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already registered.';
        }
    }

    // Save the user, then flash + redirect
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $old['full_name'],
            $old['email'],
            $old['phone'] === '' ? null : $old['phone'],
            $hash,
        ]);

        set_flash('success', 'Account created! Please log in.');
        redirect('auth/login.php');
    }
}

// The form: shown on GET, or on POST with errors
require __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h1 class="h3 mb-4">Create an account</h1>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" >
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="full_name" class="form-label">Full name</label>
                <input type="text" id="full_name" name="full_name" class="form-control"
                    value="<?= e($old['full_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                    value="<?= e($old['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone <span class="text-muted">(optional)</span></label>
                <input type="tel" id="phone" name="phone" class="form-control"
                    value="<?= e($old['phone']) ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                    minlength="8" required>
            </div>
            <div class="mb-4">
                <label for="password_confirm" class="form-label">Confirm password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                    minlength="8" required>
            </div>

            <button type="submit" class="btn btn-dark w-100">Register</button>
        </form>

        <p class="mt-3 text-center">
            Already have an account? <a href="<?= BASE_URL ?>auth/login.php">Log in</a>
        </p>
    </div>
</div>  
<?php require __DIR__ . '/../includes/footer.php'; ?>