<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$rawToken = trim((string)($_GET['token'] ?? ($_POST['token'] ?? '')));
$error = '';
$validToken = false;
$resetRecord = null;

if ($rawToken === '') {
    $error = 'Missing reset token. Please request a new password reset link.';
} else {
    $tokenHash = hash('sha256', $rawToken);

    $stmt = $pdo->prepare(
        'SELECT pr.reset_id, pr.user_id, pr.expires_at, pr.used_at, u.email, u.full_name
         FROM password_resets pr
         JOIN users u ON u.user_id = pr.user_id
         WHERE pr.token_hash = ?'
    );
    $stmt->execute([$tokenHash]);
    $resetRecord = $stmt->fetch();

    if (!$resetRecord) {
        $error = 'Invalid reset token. The link may be broken or incorrect.';
    } elseif ($resetRecord['used_at'] !== null) {
        $error = 'This reset token has already been used. Please request a new password reset link if needed.';
    } elseif (strtotime($resetRecord['expires_at']) < time()) {
        $error = 'This reset link has expired (tokens are valid for 1 hour). Please request a new one.';
    } else {
        $validToken = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    csrf_require();

    $new_password     = (string)($_POST['new_password'] ?? '');
    $confirm_password = (string)($_POST['confirm_password'] ?? '');

    if (strlen($new_password) < 8) {
        $error = 'New password must be at least 8 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $userId = (int) $resetRecord['user_id'];
        $newHash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update user's password and mark token used
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?');
            $stmt->execute([$newHash, $userId]);

            $stmt = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE reset_id = ?');
            $stmt->execute([(int) $resetRecord['reset_id']]);

            $pdo->commit();

            log_action('PASSWORD_RESET_COMPLETED', 'Password reset successfully via token', $userId);
            redirect('auth/login.php?reset=1');
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = 'Failed to reset password. Please try again.';
            app_log('Password reset transaction failed: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Set New Password';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Set New Password</h2>
            <?php if ($validToken && $resetRecord): ?>
                <p class="text-muted">Reset password for <strong><?= e($resetRecord['email']) ?></strong></p>
            <?php else: ?>
                <p class="text-muted">Password reset verification</p>
            <?php endif; ?>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($validToken): ?>
            <form method="POST" action="<?= url('auth/reset-password.php') ?>" class="auth-form">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($rawToken) ?>">

                <div class="form-group">
                    <label for="new_password">New Password (min 8 chars) <span class="required">*</span></label>
                    <input type="password" id="new_password" name="new_password" minlength="8" required
                           placeholder="••••••••" autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" required
                           placeholder="••••••••" autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Change Password & Log In</button>
            </form>
        <?php else: ?>
            <div class="mt-20 text-center">
                <a href="<?= url('auth/forgot-password.php') ?>" class="btn btn-primary">Request New Reset Link</a>
                <p class="mt-15"><a href="<?= url('auth/login.php') ?>">Back to Login</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
