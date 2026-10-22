<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$resetLink = '';
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $email = strtolower(trim((string)($_POST['email'] ?? '')));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare('SELECT user_id, full_name, is_active FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && (int) $user['is_active'] === 1) {
            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);
            $userId = (int) $user['user_id'];

            // Invalidate any earlier unused tokens for this user
            $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL')
                ->execute([$userId]);

            // Insert new reset token valid for 1 hour
            $stmt = $pdo->prepare(
                'INSERT INTO password_resets (user_id, token_hash, expires_at)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
            );
            $stmt->execute([$userId, $tokenHash]);

            log_action('PASSWORD_RESET_REQUESTED', 'Password reset token requested for ' . $email, $userId);

            // In local/coursework environment, display reset link directly on screen
            $resetLink = url('auth/reset-password.php?token=' . $rawToken);
        } else {
            // Friendly message
            $error = 'No active account found with this email address.';
        }
    }
}

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Reset Password</h2>
            <p class="text-muted">Enter your registered email to receive a password reset link</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($resetLink !== ''): ?>
            <div class="alert alert-success">
                <strong>Password Reset Link Generated!</strong>
                <p class="mt-10 mb-10 text-sm">
                    In a production system, this link is emailed. For testing & viva evaluation, click the link below to set your new password (valid for 1 hour):
                </p>
                <div class="token-box">
                    <a href="<?= e($resetLink) ?>" class="token-link">Click here to Reset Password &rarr;</a>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('auth/forgot-password.php') ?>" class="auth-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email">Registered Email Address</label>
                <input type="email" id="email" name="email" maxlength="150" required
                       placeholder="you@example.com"
                       value="<?= e($email) ?>" autocomplete="email">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Generate Reset Link</button>
        </form>

        <div class="auth-footer">
            <p>Remember your password? <a href="<?= url('auth/login.php') ?>">Back to Login</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
