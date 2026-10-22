<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_role('administrator');

// Fetch summary metrics
$totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$activeUsers = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE is_active = 1')->fetchColumn();
$inactiveUsers = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE is_active = 0')->fetchColumn();

// Role breakdowns
$roleStmt = $pdo->query(
    'SELECT r.role_name, COUNT(u.user_id) AS total
     FROM roles r
     LEFT JOIN users u ON u.role_id = r.role_id
     GROUP BY r.role_id, r.role_name'
);
$roleCounts = [];
while ($row = $roleStmt->fetch()) {
    $roleCounts[$row['role_name']] = (int) $row['total'];
}

// Logins today
$loginsToday = (int) $pdo->query(
    "SELECT COUNT(*) FROM login_attempts
     WHERE was_successful = 1 AND DATE(attempted_at) = CURRENT_DATE()"
)->fetchColumn();

// Recent 5 registered users
$recentUsers = $pdo->query(
    'SELECT u.user_id, u.full_name, u.email, u.is_active, u.created_at, r.role_name
     FROM users u
     JOIN roles r ON r.role_id = u.role_id
     ORDER BY u.created_at DESC
     LIMIT 5'
)->fetchAll();

// Recent 6 audit log entries
$recentLogs = $pdo->query(
    'SELECT a.log_id, a.action, a.details, a.ip_address, a.created_at, u.full_name, u.email
     FROM audit_log a
     LEFT JOIN users u ON u.user_id = a.user_id
     ORDER BY a.created_at DESC
     LIMIT 6'
)->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <div>
            <h1>Administrator Dashboard</h1>
            <p class="text-muted">Welcome, <?= e($_SESSION['full_name'] ?? 'Admin') ?>. Monitor users and platform activity.</p>
        </div>
        <div class="header-actions">
            <a href="<?= url('admin/users.php') ?>" class="btn btn-primary">Manage Users</a>
            <a href="<?= url('admin/audit-log.php') ?>" class="btn btn-secondary">View Audit Log</a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue">👥</div>
            <div class="stat-details">
                <span class="stat-value"><?= $totalUsers ?></span>
                <span class="stat-label">Total Users</span>
            </div>
            <div class="stat-sub">
                <span class="text-success"><?= $activeUsers ?> active</span> &bull; 
                <span class="text-danger"><?= $inactiveUsers ?> inactive</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-green">🛍️</div>
            <div class="stat-details">
                <span class="stat-value"><?= $roleCounts['customer'] ?? 0 ?></span>
                <span class="stat-label">Customers</span>
            </div>
            <div class="stat-sub">Registered food lovers</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-amber">🍳</div>
            <div class="stat-details">
                <span class="stat-value"><?= $roleCounts['staff'] ?? 0 ?></span>
                <span class="stat-label">Kitchen Staff</span>
            </div>
            <div class="stat-sub">Order & stock managers</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-purple">🔑</div>
            <div class="stat-details">
                <span class="stat-value"><?= $loginsToday ?></span>
                <span class="stat-label">Logins Today</span>
            </div>
            <div class="stat-sub">Active platform sessions</div>
        </div>
    </div>

    <!-- Dashboard Main Grid -->
    <div class="dashboard-grid mt-30">
        <!-- Recent Users Column -->
        <div class="card">
            <div class="card-header-flex">
                <h3>Recently Registered Users</h3>
                <a href="<?= url('admin/users.php') ?>" class="text-sm">View all &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                            <tr>
                                <td>
                                    <strong><?= e($u['full_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($u['email']) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-role role-<?= e($u['role_name']) ?>">
                                        <?= e(ucfirst((string)$u['role_name'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ((int)$u['is_active'] === 1): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= date('M j, Y', strtotime($u['created_at'])) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Audit Log Column -->
        <div class="card">
            <div class="card-header-flex">
                <h3>Recent System Activity</h3>
                <a href="<?= url('admin/audit-log.php') ?>" class="text-sm">View all &rarr;</a>
            </div>
            <div class="activity-feed">
                <?php foreach ($recentLogs as $log): ?>
                    <div class="activity-item">
                        <div class="activity-dot"></div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <span class="badge badge-action"><?= e($log['action']) ?></span>
                                <span class="activity-time"><?= date('M j, H:i', strtotime($log['created_at'])) ?></span>
                            </div>
                            <p class="activity-desc"><?= e($log['details'] ?? 'No extra details') ?></p>
                            <small class="text-muted">
                                By: <?= e($log['full_name'] ? $log['full_name'] . ' (' . $log['email'] . ')' : 'Guest / System') ?>
                                &bull; IP: <?= e($log['ip_address'] ?? 'Unknown') ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
