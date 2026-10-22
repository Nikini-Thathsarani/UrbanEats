<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect away
if (is_logged_in()) {
    redirect('public/index.php');
}

$errors = [];
$full_name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $full_name        = trim((string)($_POST['full_name'] ?? ''));
    $email            = strtolower(trim((string)($_POST['email'] ?? '')));
    $phone            = trim((string)($_POST['phone'] ?? ''));
    $password         = (string)($_POST['password'] ?? '');
    $confirm_password = (string)($_POST['confirm_password'] ?? '');

    if ($full_name === '' || strlen($full_name) > 100) {
        $errors[] = 'Please enter your full name (up to 100 characters).';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if ($phone !== '' && (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone) || strlen($phone) > 20)) {
        $errors[] = 'Please enter a valid phone number (up to 20 digits).';
    }

    if (!$errors) {
        // Check for duplicate email
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'An account with this email address already exists. Please log in.';
        } else {
            // Find customer role id
            $stmt = $pdo->prepare("SELECT role_id FROM roles WHERE role_name = 'customer' LIMIT 1");
            $stmt->execute();
            $customer_role = $stmt->fetch();

            if (!$customer_role) {
                $errors[] = 'System configuration error: Customer role not found.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    'INSERT INTO users (role_id, full_name, email, password_hash, phone, is_active)
                     VALUES (?, ?, ?, ?, ?, 1)'
                );
                $stmt->execute([
                    (int) $customer_role['role_id'],
                    $full_name,
                    $email,
                    $hash,
                    $phone !== '' ? $phone : null
                ]);

                $newUserId = (int) $pdo->lastInsertId();

                log_action('REGISTER', 'New customer registration: ' . $email, $newUserId);

                redirect('auth/login.php?registered=1');
            }
        }
    }
}

$pageTitle = 'Create Account';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Create Account</h2>
            <p class="text-muted">Register to order fresh meals from UrbanEats</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 pl-15">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('auth/register.php') ?>" class="auth-form" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="full_name">Full Name <span class="required">*</span></label>
                <input id="full_name" name="full_name" maxlength="100" required
                       placeholder="e.g. Kasun Perera"
                       value="<?= e($full_name) ?>" autocomplete="name">
            </div>

            <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <input id="email" type="email" name="email" maxlength="150" required
                       placeholder="you@example.com"
                       value="<?= e($email) ?>" autocomplete="email">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input id="phone" type="tel" name="phone" maxlength="20"
                       placeholder="e.g. 0712345678"
                       value="<?= e($phone) ?>" autocomplete="tel">
            </div>

            <div class="form-row">
                <div class="form-group col">
                    <label for="password">Password <span class="required">*</span></label>
                    <input id="password" type="password" name="password" minlength="8" required
                           placeholder="At least 8 chars" autocomplete="new-password">
                </div>

                <div class="form-group col">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input id="confirm_password" type="password" name="confirm_password" minlength="8" required
                           placeholder="Re-type password" autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="<?= url('auth/login.php') ?>">Sign in here</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>