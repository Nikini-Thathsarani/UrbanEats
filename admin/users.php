<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_role('administrator');

$currentAdminId = current_user_id();
$message = '';
$messageType = 'success';

// Handle POST actions: toggle status or change role
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $action = (string)($_POST['action'] ?? '');
    $targetUserId = (int)($_POST['target_user_id'] ?? 0);

    // Fetch target user
    $stmt = $pdo->prepare('SELECT user_id, full_name, email, role_id, is_active FROM users WHERE user_id = ?');
    $stmt->execute([$targetUserId]);
    $targetUser = $stmt->fetch();

    if (!$targetUser) {
        $message = 'Target user not found.';
        $messageType = 'danger';
    } else {
        if ($action === 'toggle_status') {
            if ($targetUserId === $currentAdminId) {
                $message = 'Security warning: You cannot deactivate your own administrator account.';
                $messageType = 'danger';
            } else {
                $newStatus = (int)$targetUser['is_active'] === 1 ? 0 : 1;
                $stmt = $pdo->prepare('UPDATE users SET is_active = ?, updated_at = NOW() WHERE user_id = ?');
                $stmt->execute([$newStatus, $targetUserId]);

                $actionName = $newStatus === 1 ? 'USER_ACTIVATED' : 'USER_DEACTIVATED';
                $details = sprintf(
                    'User #%d (%s) was %s by admin',
                    $targetUserId,
                    $targetUser['email'],
                    $newStatus === 1 ? 'activated' : 'deactivated'
                );
                log_action($actionName, $details);

                $message = sprintf('User "%s" has been %s.', $targetUser['full_name'], $newStatus === 1 ? 'activated' : 'deactivated');
                $messageType = 'success';
            }
        } elseif ($action === 'change_role') {
            $newRoleId = (int)($_POST['new_role_id'] ?? 0);

            if ($targetUserId === $currentAdminId) {
                $message = 'Security warning: You cannot change your own administrator role.';
                $messageType = 'danger';
            } else {
                $roleStmt = $pdo->prepare('SELECT role_name FROM roles WHERE role_id = ?');
                $roleStmt->execute([$newRoleId]);
                $roleName = $roleStmt->fetchColumn();

                if (!$roleName) {
                    $message = 'Invalid role selected.';
                    $messageType = 'danger';
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET role_id = ?, updated_at = NOW() WHERE user_id = ?');
                    $stmt->execute([$newRoleId, $targetUserId]);

                    log_action('ROLE_CHANGE', sprintf('User #%d (%s) role changed to %s', $targetUserId, $targetUser['email'], $roleName));

                    $message = sprintf('User "%s" role updated to %s.', $targetUser['full_name'], ucfirst((string)$roleName));
                    $messageType = 'success';
                }
            }
        }
    }
}

// Fetch all roles for dropdowns
$allRoles = $pdo->query('SELECT role_id, role_name FROM roles ORDER BY role_id ASC')->fetchAll();

// Search & Filter parameters
$search = trim((string)($_GET['q'] ?? ''));
$filterRole = trim((string)($_GET['role'] ?? 'all'));
$filterStatus = trim((string)($_GET['status'] ?? 'all'));

// Build filtered query
$sql = 'SELECT u.user_id, u.full_name, u.email, u.phone, u.is_active, u.created_at, r.role_id, r.role_name
        FROM users u
        JOIN roles r ON r.role_id = u.role_id
        WHERE 1=1';
$params = [];

if ($search !== '') {
    $sql .= ' AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($filterRole !== 'all' && $filterRole !== '') {
    $sql .= ' AND r.role_name = ?';
    $params[] = $filterRole;
}

if ($filterStatus === 'active') {
    $sql .= ' AND u.is_active = 1';
} elseif ($filterStatus === 'inactive') {
    $sql .= ' AND u.is_active = 0';
}

$sql .= ' ORDER BY u.user_id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'User Management';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <div>
            <h1>User Management</h1>
            <p class="text-muted">Search, filter, update user roles, and activate or deactivate accounts.</p>
        </div>
        <div class="header-actions">
            <a href="<?= url('admin/dashboard.php') ?>" class="btn btn-secondary">&larr; Back to Dashboard</a>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?= e($messageType) ?> mb-20">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <!-- Filter & Search Toolbar -->
    <div class="card filter-card mb-20">
        <form method="GET" action="<?= url('admin/users.php') ?>" class="filter-form">
            <div class="filter-group">
                <label for="q">Search</label>
                <input type="text" id="q" name="q" placeholder="Name, email, phone..." value="<?= e($search) ?>">
            </div>

            <div class="filter-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="all" <?= $filterRole === 'all' ? 'selected' : '' ?>>All Roles</option>
                    <?php foreach ($allRoles as $r): ?>
                        <option value="<?= e($r['role_name']) ?>" <?= $filterRole === $r['role_name'] ? 'selected' : '' ?>>
                            <?= e(ucfirst((string)$r['role_name'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Active Only</option>
                    <option value="inactive" <?= $filterStatus === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= url('admin/users.php') ?>" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Users Table Card -->
    <div class="card">
        <div class="card-header-flex">
            <h3>Registered Accounts (<?= count($users) ?> results)</h3>
        </div>

        <?php if (empty($users)): ?>
            <div class="empty-state">
                <p>No user accounts matched your search criteria.</p>
                <a href="<?= url('admin/users.php') ?>" class="btn btn-sm btn-secondary">Clear Filters</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User Details</th>
                            <th>Phone</th>
                            <th>Current Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr class="<?= (int)$u['is_active'] === 0 ? 'row-inactive' : '' ?>">
                                <td>#<?= e((string)$u['user_id']) ?></td>
                                <td>
                                    <strong><?= e($u['full_name']) ?></strong>
                                    <?php if ((int)$u['user_id'] === $currentAdminId): ?>
                                        <span class="badge badge-sm badge-info">You</span>
                                    <?php endif; ?><br>
                                    <small class="text-muted"><?= e($u['email']) ?></small>
                                </td>
                                <td><?= e($u['phone'] ?? '-') ?></td>
                                <td>
                                    <!-- Role Change Form -->
                                    <?php if ((int)$u['user_id'] === $currentAdminId): ?>
                                        <span class="badge badge-role role-<?= e($u['role_name']) ?>">
                                            <?= e(ucfirst((string)$u['role_name'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <form method="POST" action="<?= url('admin/users.php') ?>" class="inline-role-form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="change_role">
                                            <input type="hidden" name="target_user_id" value="<?= e((string)$u['user_id']) ?>">
                                            <select name="new_role_id" onchange="this.form.submit()" class="select-sm">
                                                <?php foreach ($allRoles as $r): ?>
                                                    <option value="<?= e((string)$r['role_id']) ?>" <?= (int)$u['role_id'] === (int)$r['role_id'] ? 'selected' : '' ?>>
                                                        <?= e(ucfirst((string)$r['role_name'])) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int)$u['is_active'] === 1): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Deactivated</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= date('M j, Y', strtotime($u['created_at'])) ?></small>
                                </td>
                                <td>
                                    <?php if ((int)$u['user_id'] !== $currentAdminId): ?>
                                        <form method="POST" action="<?= url('admin/users.php') ?>" class="inline-block" onsubmit="return confirm('Are you sure you want to <?= (int)$u['is_active'] === 1 ? 'deactivate' : 'activate' ?> this user?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="target_user_id" value="<?= e((string)$u['user_id']) ?>">
                                            <?php if ((int)$u['is_active'] === 1): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
                                            <?php endif; ?>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted text-sm">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
