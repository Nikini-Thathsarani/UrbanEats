<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_role('customer');

$userId = current_user_id();
$stmt = $pdo->prepare(
    'SELECT order_id, total_amount, order_type, order_status, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC, order_id DESC'
);
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$pageTitle = 'Order History & Tracking';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="hero-section">
    <h1 class="hero-title">Order History &amp; Tracking</h1>
    <p class="hero-subtitle">Review your previous orders and follow their current status</p>
</div>

<div class="container" style="max-width: 1000px; margin: 30px auto; padding: 20px; color: #fff;">
    <?php if (empty($orders)): ?>
        <div style="background: #1a1a1a; padding: 30px; border-radius: 8px; text-align: center;">
            <p style="color: #aaa; font-size: 1.1rem;">You have not placed any orders yet.</p>
            <a href="<?= url('public/menu/index.php') ?>" class="btn btn-primary">Explore Menu</a>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 20px;">
            <?php foreach ($orders as $order):
                $orderType = strtolower((string) $order['order_type']);
                $steps = $orderType === 'pickup'
                    ? ['Placed', 'Preparing', 'Ready', 'Collected', 'Completed']
                    : ['Placed', 'Preparing', 'Ready', 'Out for Delivery', 'Completed'];
                $currentStatus = (string) $order['order_status'];
                $currentStep = array_search($currentStatus, $steps, true);
                $isCancelled = $currentStatus === 'Cancelled';

                $itemsStmt = $pdo->prepare(
                    'SELECT d.dish_name, oi.quantity
                     FROM order_items oi
                     JOIN dishes d ON d.dish_id = oi.dish_id
                     WHERE oi.order_id = ?
                     ORDER BY d.dish_name'
                );
                $itemsStmt->execute([$order['order_id']]);
                $orderItems = $itemsStmt->fetchAll();
            ?>
                <article style="background: #1a1a1a; padding: 22px; border-radius: 8px; border-left: 4px solid <?= $isCancelled ? '#ff6b6b' : '#d4af37' ?>;">
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; border-bottom: 1px solid #333; padding-bottom: 14px;">
                        <div>
                            <h2 style="margin: 0 0 6px; color: #d4af37;">Order #<?= (int) $order['order_id'] ?></h2>
                            <span style="color: #aaa;"><?= e(date('Y-m-d h:i A', strtotime((string) $order['created_at']))) ?></span>
                            <span style="color: #aaa;">&middot; <?= e(ucfirst($orderType)) ?></span>
                        </div>
                        <div style="text-align: right;">
                            <strong style="display: block; color: <?= $isCancelled ? '#ff6b6b' : '#51cf66' ?>;"><?= e($currentStatus) ?></strong>
                            <strong>LKR <?= number_format((float) $order['total_amount'], 2) ?></strong>
                        </div>
                    </div>

                    <?php if ($isCancelled): ?>
                        <p style="margin: 16px 0 0; color: #ff6b6b;">This order was cancelled.</p>
                    <?php elseif ($currentStep === false): ?>
                        <p style="margin: 16px 0 0; color: #aaa;">Current order status: <?= e($currentStatus) ?></p>
                    <?php else: ?>
                        <ol aria-label="Order progress" style="display: flex; flex-wrap: wrap; gap: 8px; list-style: none; padding: 0; margin: 18px 0;">
                            <?php foreach ($steps as $stepIndex => $step): ?>
                                <li style="flex: 1 1 110px; padding: 9px 10px; text-align: center; border-radius: 4px; background: <?= $stepIndex <= $currentStep ? 'rgba(81,207,102,0.18)' : '#252525' ?>; color: <?= $stepIndex <= $currentStep ? '#51cf66' : '#999' ?>; border: 1px solid <?= $stepIndex === $currentStep ? '#51cf66' : '#383838' ?>;">
                                    <?= e($step) ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>

                    <?php if (!empty($orderItems)): ?>
                        <p style="margin: 12px 0 0; color: #ccc;">
                            <?php foreach ($orderItems as $index => $item): ?>
                                <?= $index > 0 ? '&middot; ' : '' ?><?= (int) $item['quantity'] ?> &times; <?= e($item['dish_name']) ?>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>

                    <div style="margin-top: 16px;">
                        <a href="<?= url('public/orders/confirmation.php?order_id=' . (int) $order['order_id']) ?>" style="color: #d4af37; text-decoration: none;">View invoice</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
