<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login();

$userId = current_user_id();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $current_password = (string)($_POST['current_password'] ?? '');
    $new_password     = (string)($_POST['new_password'] ?? '');
    $confirm_password = (string)($_POST['confirm_password'] ?? '');

    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
        $error = 'Please fill in all password fields.';
    } elseif (strlen($new_password) < 8) {
        $error = 'The new password must be at least 8 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } else {
        // Fetch current password hash
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE user_id = ?');
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($current_password, $hash)) {
            $error = 'The current password you entered is incorrect.';
            log_action('PASSWORD_CHANGE_FAILED', 'Failed password change: current password mismatch', $userId);
        } else {
            $newHash = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?');
            $stmt->execute([$newHash, $userId]);

            log_action('PASSWORD_CHANGE', 'Password successfully changed', $userId);
            $success = 'Your password has been changed successfully!';
        }
    }
}

$pageTitle = 'Change Password';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container container-sm">
    <div class="card auth-card">
        <div class="card-header">
            <h2>Change Password</h2>
            <p class="text-muted">Ensure your account is using a secure password</p>
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

        <form method="POST" action="<?= url('auth/change-password.php') ?>" class="auth-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="current_password">Current Password <span class="required">*</span></label>
                <input type="password" id="current_password" name="current_password" required
                       placeholder="••••••••" autocomplete="current-password">
            </div>

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

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Password</button>
                <a href="<?= url('auth/profile.php') ?>" class="btn btn-secondary">Back to Profile</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
