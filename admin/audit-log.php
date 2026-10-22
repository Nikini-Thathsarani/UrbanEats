<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_role('administrator');

// Filter parameters
$search = trim((string)($_GET['q'] ?? ''));
$filterAction = trim((string)($_GET['action'] ?? 'all'));
$page = max(1, (int)($_GET['p'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

// Fetch unique action types for filter dropdown
$distinctActions = $pdo->query('SELECT DISTINCT action FROM audit_log ORDER BY action ASC')->fetchAll(PDO::FETCH_COLUMN);

// Build query
$where = 'WHERE 1=1';
$params = [];

if ($search !== '') {
    $where .= ' AND (a.details LIKE ? OR a.ip_address LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($filterAction !== 'all' && $filterAction !== '') {
    $where .= ' AND a.action = ?';
    $params[] = $filterAction;
}

// Count total
$countStmt = $pdo->prepare(
    "SELECT COUNT(*)
     FROM audit_log a
     LEFT JOIN users u ON u.user_id = a.user_id
     $where"
);
$countStmt->execute($params);
$totalRecords = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRecords / $limit));

// Fetch page records
$sql = "SELECT a.log_id, a.action, a.details, a.ip_address, a.created_at, u.user_id, u.full_name, u.email, r.role_name
        FROM audit_log a
        LEFT JOIN users u ON u.user_id = a.user_id
        LEFT JOIN roles r ON r.role_id = u.role_id
        $where
        ORDER BY a.created_at DESC, a.log_id DESC
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

$pageTitle = 'Audit Log';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <div>
            <h1>Security & Audit Log</h1>
            <p class="text-muted">Track system events, logins, role modifications, and operations across components.</p>
        </div>
        <div class="header-actions">
            <a href="<?= url('admin/dashboard.php') ?>" class="btn btn-secondary">&larr; Back to Dashboard</a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card filter-card mb-20">
        <form method="GET" action="<?= url('admin/audit-log.php') ?>" class="filter-form">
            <div class="filter-group">
                <label for="q">Search</label>
                <input type="text" id="q" name="q" placeholder="Action, details, user, IP..." value="<?= e($search) ?>">
            </div>

            <div class="filter-group">
                <label for="action">Action Type</label>
                <select id="action" name="action">
                    <option value="all" <?= $filterAction === 'all' ? 'selected' : '' ?>>All Actions</option>
                    <?php foreach ($distinctActions as $act): ?>
                        <option value="<?= e($act) ?>" <?= $filterAction === $act ? 'selected' : '' ?>>
                            <?= e($act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= url('admin/audit-log.php') ?>" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Audit Log Table -->
    <div class="card">
        <div class="card-header-flex">
            <h3>Audit Records (<?= $totalRecords ?> total)</h3>
            <span class="text-sm text-muted">Showing page <?= $page ?> of <?= $totalPages ?></span>
        </div>

        <?php if (empty($logs)): ?>
            <div class="empty-state">
                <p>No audit log events match your filter criteria.</p>
                <a href="<?= url('admin/audit-log.php') ?>" class="btn btn-sm btn-secondary">Clear Filters</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $row): ?>
                            <tr>
                                <td>#<?= e((string)$row['log_id']) ?></td>
                                <td>
                                    <span class="nowrap"><?= date('Y-m-d H:i:s', strtotime($row['created_at'])) ?></span>
                                </td>
                                <td>
                                    <?php if ($row['user_id']): ?>
                                        <strong><?= e($row['full_name']) ?></strong><br>
                                        <small class="text-muted"><?= e($row['email']) ?></small>
                                        <?php if ($row['role_name']): ?>
                                            <span class="badge badge-sm badge-role role-<?= e($row['role_name']) ?>">
                                                <?= e($row['role_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-sm badge-secondary">Guest / System</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-action badge-action-<?= e(strtolower(explode('_', (string)$row['action'])[0])) ?>">
                                        <?= e($row['action']) ?>
                                    </span>
                                </td>
                                <td class="log-details-col">
                                    <?= e($row['details'] ?? '-') ?>
                                </td>
                                <td>
                                    <code><?= e($row['ip_address'] ?? 'N/A') ?></code>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('admin/audit-log.php?' . http_build_query(array_merge($_GET, ['p' => $page - 1]))) ?>" class="page-link">&laquo; Prev</a>
                    <?php endif; ?>

                    <span class="page-current">Page <?= $page ?> of <?= $totalPages ?></span>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= url('admin/audit-log.php?' . http_build_query(array_merge($_GET, ['p' => $page + 1]))) ?>" class="page-link">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
