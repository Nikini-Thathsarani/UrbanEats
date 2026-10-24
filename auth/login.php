<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to appropriate landing page
if (is_logged_in()) {
    if (current_role() === 'administrator') {
        redirect('admin/dashboard.php');
    } elseif (current_role() === 'staff') {
        redirect('public/menu/manage.php');
    } else {
        redirect('public/index.php');
    }
}

$error   = '';
$email   = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = 'Registration successful! You can now log in with your credentials.';
} elseif (isset($_GET['logged_out'])) {
    $success = 'You have been safely logged out.';
} elseif (isset($_GET['reset'])) {
    $success = 'Your password has been reset successfully. Please log in with your new password.';
} elseif (isset($_GET['timeout'])) {
    $error = 'Your session expired due to inactivity. Please log in again.';
} elseif (isset($_GET['deactivated'])) {
    $error = 'Your account has been deactivated. Please contact support or administrator.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $email    = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $ip       = $_SERVER['REMOTE_ADDR'] ?? null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150 || $password === '') {
        $error = 'Please enter a valid email and password.';
    } else {
        // Count CONSECUTIVE failed attempts in the last 15 minutes
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE email = ?
               AND was_successful = 0
               AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
               AND attempt_id > COALESCE(
                    (SELECT MAX(attempt_id) FROM login_attempts
                     WHERE email = ? AND was_successful = 1), 0)'
        );
        $stmt->execute([$email, $email]);
        $failedAttempts = (int) $stmt->fetchColumn();

        if ($failedAttempts >= 5) {
            $error = 'Too many failed login attempts. For security, your account is temporarily locked. Please try again in 15 minutes.';
            log_action('LOGIN_THROTTLED', 'Temporary lockout hit for ' . $email);
        } else {
            $stmt = $pdo->prepare(
                'SELECT u.user_id, u.full_name, u.email,
                        u.password_hash, u.is_active,
                        r.role_name
                 FROM users u
                 JOIN roles r ON u.role_id = r.role_id
                 WHERE u.email = ?
                 LIMIT 1'
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            $isValid = $user
                && password_verify($password, $user['password_hash'])
                && (int) $user['is_active'] === 1;

            // Record login attempt
            $stmt = $pdo->prepare(
                'INSERT INTO login_attempts (email, ip_address, was_successful)
                 VALUES (?, ?, ?)'
            );
            $stmt->execute([$email, $ip, $isValid ? 1 : 0]);

            if ($isValid) {
                session_regenerate_id(true);

                $_SESSION['user_id']       = (int) $user['user_id'];
                $_SESSION['full_name']     = $user['full_name'];
                $_SESSION['email']         = $user['email'];
                $_SESSION['role']          = $user['role_name'];
                $_SESSION['last_activity'] = time();

                log_action('LOGIN', 'User logged in successfully', (int) $user['user_id']);

                if ($user['role_name'] === 'administrator') {
                    redirect('admin/dashboard.php');
                } elseif ($user['role_name'] === 'staff') {
                    redirect('public/menu/manage.php');
                } else {
                    redirect('public/index.php');
                }
            } else {
                if ($user && (int) $user['is_active'] === 0) {
                    $error = 'This account has been deactivated. Please contact the administrator.';
                    log_action('LOGIN_DEACTIVATED', 'Login attempted on deactivated account: ' . $email, (int)$user['user_id']);
                } else {
                    $error = 'Invalid email address or password.';
                    log_action('LOGIN_FAILED', 'Failed login attempt for ' . $email);
                }
            }
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Welcome Back</h2>
            <p class="text-muted">Sign in to your UrbanEats account</p>
        </div>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('auth/login.php') ?>" class="auth-form" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" maxlength="150" required
                       placeholder="you@example.com"
                       value="<?= e($email) ?>" autocomplete="email">
            </div>

            <div class="form-group">
                <div class="label-with-link">
                    <label for="password">Password</label>
                    <a href="<?= url('auth/forgot-password.php') ?>" class="text-sm">Forgot password?</a>
                </div>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••" autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account yet? <a href="<?= url('auth/register.php') ?>">Create an account</a></p>
            <div class="demo-credentials">
                <strong>Test Accounts (Password: <code>Test@1234</code>)</strong>
                <div>Admin: <code>admin@urbaneats.lk</code></div>
                <div>Staff: <code>staff1@urbaneats.lk</code></div>
                <div>Customer: <code>customer1@urbaneats.lk</code></div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>