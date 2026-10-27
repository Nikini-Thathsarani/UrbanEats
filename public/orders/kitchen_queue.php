<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role('staff');


$allowed_statuses = ['Placed', 'Preparing', 'Ready', 'Out for Delivery', 'Collected', 'Completed', 'Cancelled'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    csrf_require();

    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['new_status'];

    if (in_array($new_status, $allowed_statuses, true)) {
        try {
            $pdo->beginTransaction();

            // 1. Update orders table status
            $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
            $stmt->execute([$new_status, $order_id]);

            // 2. Log in status history table
            $stmt_hist = $pdo->prepare("INSERT INTO order_status_history (order_id, status) VALUES (?, ?)");
            $stmt_hist->execute([$order_id, $new_status]);

            log_action('ORDER_STATUS_CHANGED', "Order #{$order_id} status changed to {$new_status}");

            $pdo->commit();
            header("Location: kitchen_queue.php?msg=updated");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}


$stmt = $pdo->query("
    SELECT o.*, u.full_name AS username 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    WHERE o.order_status NOT IN ('Completed', 'Cancelled')
    ORDER BY o.created_at ASC
");
$active_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="hero-section">
    <h1 class="hero-title">Kitchen Order Queue</h1>
    <p class="hero-subtitle">Process incoming orders through preparation stages</p>
</div>

<div class="container" style="max-width: 1100px; margin: 30px auto; padding: 20px; color: #fff;">
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
        <p style="background: rgba(81,207,102,0.2); color: #51cf66; padding: 10px; border-radius: 4px;">Order status updated successfully!</p>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <p role="alert" style="background: rgba(255,107,107,0.2); color: #ff6b6b; padding: 10px; border-radius: 4px;"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if (empty($active_orders)): ?>
        <p style="text-align: center; color: #aaa; font-size: 1.2rem;">No active kitchen orders right now.</p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
            <?php foreach ($active_orders as $ord): 
                
                $stmt_i = $pdo->prepare("SELECT oi.*, d.dish_name FROM order_items oi JOIN dishes d ON oi.dish_id = d.dish_id WHERE oi.order_id = ?");
                $stmt_i->execute([$ord['order_id']]);
                $o_items = $stmt_i->fetchAll(PDO::FETCH_ASSOC);
            ?>
                <div style="background: #1a1a1a; border-left: 4px solid #d4af37; padding: 15px; border-radius: 6px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #333; padding-bottom: 8px; margin-bottom: 10px;">
                        <strong>Order #<?= $ord['order_id'] ?> (<?= strtoupper($ord['order_type']) ?>)</strong>
                        <span style="color: #d4af37; font-weight: bold;"><?= htmlspecialchars($ord['order_status']) ?></span>
                    </div>

                    <p style="margin: 5px 0; font-size: 0.85rem; color: #aaa;">Customer: <?= htmlspecialchars($ord['username']) ?> | Phone: <?= htmlspecialchars($ord['phone_number']) ?></p>
                    <p style="margin: 5px 0; font-size: 0.85rem; color: #aaa;">Time: <?= date('h:i A', strtotime($ord['created_at'])) ?></p>

                    <ul style="margin: 10px 0; padding-left: 20px; font-size: 0.95rem;">
                        <?php foreach ($o_items as $it): ?>
                            <li><strong><?= $it['quantity'] ?>x</strong> <?= htmlspecialchars($it['dish_name']) ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <form method="POST" style="margin-top: 15px; display: flex; gap: 8px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= $ord['order_id'] ?>">
                        <select name="new_status" style="flex: 1; padding: 6px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                            <?php foreach ($allowed_statuses as $st): ?>
                                <option value="<?= $st ?>" <?= $ord['order_status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" style="padding: 6px 12px; background: #d4af37; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; color: #000;">Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>