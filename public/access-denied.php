<?php
declare(strict_types=1);

$pageTitle = '403 Access Denied';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container container-sm">
    <div class="card error-card text-center">
        <div class="error-badge">403</div>
        <h1>Access Denied</h1>
        <p class="error-lead">You do not have permission to view or access this page.</p>
        <p class="text-muted">
            If you believe you should have access to this resource, please contact the system administrator.
        </p>

        <div class="btn-group mt-20">
            <a href="<?= url('public/index.php') ?>" class="btn btn-secondary">Go to Home</a>
            <?php if (is_logged_in() && current_role() === 'administrator'): ?>
                <a href="<?= url('admin/dashboard.php') ?>" class="btn btn-primary">Admin Dashboard</a>
            <?php elseif (is_logged_in()): ?>
                <a href="<?= url('auth/profile.php') ?>" class="btn btn-primary">My Profile</a>
            <?php else: ?>
                <a href="<?= url('auth/login.php') ?>" class="btn btn-primary">Login with another account</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
