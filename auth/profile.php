<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login();

$userId = current_user_id();
$success = '';
$error = '';

// Fetch current user details
$stmt = $pdo->prepare(
    'SELECT u.user_id, u.full_name, u.email, u.phone, u.created_at, r.role_name
     FROM users u
     JOIN roles r ON r.role_id = u.role_id
     WHERE u.user_id = ?'
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    destroy_session();
    redirect('auth/login.php');
}

$full_name = $user['full_name'];
$phone = $user['phone'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $new_name  = trim((string)($_POST['full_name'] ?? ''));
    $new_phone = trim((string)($_POST['phone'] ?? ''));

    if ($new_name === '' || strlen($new_name) > 100) {
        $error = 'Please enter a valid full name (1 - 100 characters).';
    } elseif ($new_phone !== '' && (!preg_match('/^[0-9+\-\s()]{7,20}$/', $new_phone) || strlen($new_phone) > 20)) {
        $error = 'Please enter a valid phone number (up to 20 digits).';
    } else {
        $stmt = $pdo->prepare(
            'UPDATE users
             SET full_name = ?, phone = ?, updated_at = NOW()
             WHERE user_id = ?'
        );
        $stmt->execute([
            $new_name,
            $new_phone !== '' ? $new_phone : null,
            $userId
        ]);

        $_SESSION['full_name'] = $new_name;
        $full_name = $new_name;
        $phone = $new_phone;

        log_action('PROFILE_UPDATE', 'Updated profile information', $userId);
        $success = 'Your profile details have been updated successfully!';
    }
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container container-sm">
    <div class="card profile-card">
        <div class="card-header">
            <h2>User Profile</h2>
            <p class="text-muted">Manage your personal information</p>
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

        <div class="user-summary-box">
            <div class="summary-item">
                <span class="label">Account Role:</span>
                <span class="value badge badge-role role-<?= e($user['role_name']) ?>">
                    <?= e(ucfirst((string)$user['role_name'])) ?>
                </span>
            </div>
            <div class="summary-item">
                <span class="label">Member Since:</span>
                <span class="value"><?= date('F j, Y', strtotime($user['created_at'])) ?></span>
            </div>
        </div>

        <form method="POST" action="<?= url('auth/profile.php') ?>" class="profile-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" value="<?= e($user['email']) ?>" disabled class="input-disabled">
                <small class="text-muted">Email address cannot be changed for account security.</small>
            </div>

            <div class="form-group">
                <label for="full_name">Full Name <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" maxlength="100" required
                       value="<?= e($full_name) ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" maxlength="20"
                       placeholder="e.g. 0712345678"
                       value="<?= e($phone) ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= url('auth/change-password.php') ?>" class="btn btn-outline">Change Password</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
