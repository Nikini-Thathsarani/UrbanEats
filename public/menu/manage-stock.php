<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_login();
require_role('staff', 'administrator');

$query = "SELECT d.dish_id, d.dish_name, i.quantity_in_stock, i.reorder_level 
          FROM dishes d 
          JOIN inventory i ON d.dish_id = i.dish_id 
          WHERE d.is_active = 1";
$items = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="max-width: 1000px; margin: 30px auto; padding: 20px; background: var(--card-bg); border-radius: 8px;">
    <h2>Stock Control Dashboard</h2>
    <table style="width: 100%; border-collapse: collapse; color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #333; text-align: left;">
                <th style="padding: 10px;">Dish Name</th>
                <th style="padding: 10px;">Stock Quantity</th>
                <th style="padding: 10px;">Reorder Level</th>
                <th style="padding: 10px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <?php 
                    $status = 'Normal';
                    $style = '';
                    if ($item['quantity_in_stock'] == 0) {
                        $status = 'Out of Stock';
                        $style = 'background-color: rgba(255, 0, 0, 0.2); color: #ff6b6b;';
                    } elseif ($item['quantity_in_stock'] <= $item['reorder_level']) {
                        $status = 'Low Stock';
                        $style = 'background-color: rgba(255, 165, 0, 0.2); color: #ffa500;';
                    }
                ?>
                <tr style="border-bottom: 1px solid #333; <?= $style ?>">
                    <td style="padding: 10px;"><?= htmlspecialchars($item['dish_name']) ?></td>
                    <td style="padding: 10px;"><?= $item['quantity_in_stock'] ?></td>
                    <td style="padding: 10px;"><?= $item['reorder_level'] ?></td>
                    <td style="padding: 10px; font-weight: bold;"><?= $status ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>