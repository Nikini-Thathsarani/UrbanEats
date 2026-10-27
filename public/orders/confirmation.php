<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_login();

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id) {
    header('Location: ../menu/index.php');
    exit;
}


$stmt = $pdo->prepare("SELECT o.*, u.full_name AS username, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ? AND o.user_id = ?");
$stmt->execute([$order_id, current_user_id()]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: ../menu/index.php');
    exit;
}


$stmt_items = $pdo->prepare("SELECT oi.*, d.dish_name FROM order_items oi JOIN dishes d ON oi.dish_id = d.dish_id WHERE oi.order_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
@page {
    size: A4;
    margin: 15mm;
}

@media print {
    .no-print,
    .main-header,
    .main-footer {
        display: none !important;
    }

    body,
    .page-content {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        color: #000 !important;
    }

    .invoice-card {
        max-width: 100% !important;
        border: 1px solid #ccc !important;
        color: #000 !important;
        background: #fff !important;
        box-shadow: none !important;
        break-inside: avoid;
    }

    .invoice-card th,
    .invoice-card td,
    .invoice-card p,
    .invoice-card strong {
        color: #000 !important;
    }

    .invoice-card tr {
        break-inside: avoid;
    }
}
</style>

<div class="hero-section no-print">
    <h1 class="hero-title">Order Confirmation</h1>
    <p class="hero-subtitle">Thank you! Your order has been placed successfully.</p>
</div>

<div class="container" style="max-width: 750px; margin: 30px auto; padding: 20px;">
    
    <div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between;">
        <a href="history.php" style="color: #d4af37; text-decoration: none;">&larr; Order History &amp; Tracking</a>
        <button onclick="window.print()" class="btn-gold" style="padding: 8px 16px; background: #d4af37; border: none; font-weight: bold; cursor: pointer; border-radius: 4px;">Print / Save as PDF</button>
    </div>

    <div class="invoice-card" style="background: #1a1a1a; padding: 30px; border-radius: 8px; color: #fff;">
        <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 15px;">
            <div>
                <h2 style="color: #d4af37; margin: 0;">UrbanEats Cloud Kitchen</h2>
                <p style="margin: 5px 0 0 0; color: #aaa; font-size: 0.9rem;">Order Invoice #<?= $order['order_id'] ?></p>
            </div>
            <div style="text-align: right;">
                <p style="margin: 0; font-weight: bold;">Date: <?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></p>
                <p style="margin: 5px 0 0 0; color: #51cf66; font-weight: bold;">Status: <?= htmlspecialchars($order['order_status']) ?></p>
            </div>
        </div>

        <div style="margin: 20px 0; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 0.95rem;">
            <div>
                <strong>Customer Details:</strong>
                <p style="margin: 5px 0; color: #ccc;"><?= htmlspecialchars($order['username']) ?> (<?= htmlspecialchars($order['email']) ?>)</p>
                <p style="margin: 5px 0; color: #ccc;">Phone: <?= htmlspecialchars($order['phone_number']) ?></p>
            </div>
            <div>
                <strong>Order Info:</strong>
                <p style="margin: 5px 0; color: #ccc;">Type: <?= strtoupper($order['order_type']) ?></p>
                <p style="margin: 5px 0; color: #ccc;">Payment: <?= str_replace('_', ' ', strtoupper($order['payment_method'])) ?></p>
                <?php if ($order['order_type'] === 'delivery'): ?>
                    <p style="margin: 5px 0; color: #ccc;">Address: <?= htmlspecialchars($order['delivery_address']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background: #2a2a2a; border-bottom: 1px solid #444; text-align: left;">
                    <th style="padding: 10px;">Item</th>
                    <th style="padding: 10px;">Qty</th>
                    <th style="padding: 10px;">Unit Price</th>
                    <th style="padding: 10px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr style="border-bottom: 1px solid #333;">
                        <td style="padding: 10px;"><?= htmlspecialchars($item['dish_name']) ?></td>
                        <td style="padding: 10px;"><?= $item['quantity'] ?></td>
                        <td style="padding: 10px;">LKR <?= number_format($item['price_at_purchase'], 2) ?></td>
                        <td style="padding: 10px; text-align: right;">LKR <?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="text-align: right; margin-top: 20px; font-size: 1.2rem; font-weight: bold;">
            <span>Grand Total: </span>
            <span style="color: #d4af37;">LKR <?= number_format($order['total_amount'], 2) ?></span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>