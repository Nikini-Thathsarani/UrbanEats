<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

if (isset($_GET['delete_id'])) {
    $delete_id = (int) $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("UPDATE dishes SET is_active = 0 WHERE dish_id = ?");
        $stmt->execute([$delete_id]);
        
        log_action('DISH_DELETED', "Dish ID #{$delete_id} deactivated");
        
        header("Location: manage.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $error = "Failed to delete dish: " . $e->getMessage();
    }
}

$query = "SELECT d.*, c.category_name, COALESCE(i.quantity_in_stock, 0) as quantity_in_stock, 
                 COALESCE(i.reorder_level, 5) as reorder_level 
          FROM dishes d 
          LEFT JOIN categories c ON d.category_id = c.category_id 
          LEFT JOIN inventory i ON d.dish_id = i.dish_id 
          WHERE d.is_active = 1 
          ORDER BY d.dish_id DESC";
$dishes = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="hero-section">
    <h1 class="hero-title">Stock Dashboard & Menu Management</h1>
    <p class="hero-subtitle">Monitor Inventory Levels and Manage Dishes</p>
</div>

<div class="container" style="max-width: 1200px; margin: 30px auto; padding: 20px; color: #fff;">
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <p style="background: rgba(81,207,102,0.2); color: #51cf66; padding: 10px; border-radius: 4px;">Dish successfully deactivated!</p>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Kitchen Inventory & Menu</h2>
        <a href="add.php" class="btn-gold" style="padding: 10px 15px; background: #d4af37; color: #000; text-decoration: none; border-radius: 4px; font-weight: bold;">+ Add New Dish</a>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; background: #1a1a1a; text-align: left;">
            <thead>
                <tr style="background: #2a2a2a; border-bottom: 2px solid #444;">
                    <th style="padding: 12px;">Image</th>
                    <th style="padding: 12px;">Dish Name</th>
                    <th style="padding: 12px;">Category</th>
                    <th style="padding: 12px;">Price (LKR)</th>
                    <th style="padding: 12px;">Stock Status</th>
                    <th style="padding: 12px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dishes as $dish): ?>
                    <?php 
                        // Low stock highlighting logic
                        $stock = (int)$dish['quantity_in_stock'];
                        $reorder = (int)$dish['reorder_level'];
                        $stock_bg = 'transparent';
                        $stock_text_color = '#51cf66'; // Normal (Green)
                        
                        if ($stock <= 0) {
                            $stock_text_color = '#ff6b6b'; // Out of stock (Red)
                            $stock_status = 'Out of Stock';
                        } elseif ($stock <= $reorder) {
                            $stock_text_color = '#f59f00'; // Low stock (Orange)
                            $stock_status = "Low Stock ({$stock})";
                        } else {
                            $stock_status = "In Stock ({$stock})";
                        }
                        
                        $imgName = !empty($dish['image_url']) ? $dish['image_url'] : 'bg1.jpg';
                    ?>
                    <tr style="border-bottom: 1px solid #333;">
                        <td style="padding: 10px;">
                            <img src="../uploads/<?= htmlspecialchars($imgName) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" onerror="this.src='../uploads/bg1.jpg';">
                        </td>
                        <td style="padding: 10px; font-weight: bold;"><?= htmlspecialchars($dish['dish_name']) ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($dish['category_name']) ?></td>
                        <td style="padding: 10px;">LKR <?= number_format($dish['price'], 2) ?></td>
                        <td style="padding: 10px; color: <?= $stock_text_color ?>; font-weight: bold;">
                            <?= $stock_status ?>
                        </td>
                        <td style="padding: 10px;">
                            <a href="edit.php?id=<?= $dish['dish_id'] ?>" style="color: #3bc9db; margin-right: 10px; text-decoration: none;">Edit</a>
                            <a href="manage.php?delete_id=<?= $dish['dish_id'] ?>" onclick="return confirm('Are you sure you want to delete this dish?');" style="color: #ff6b6b; text-decoration: none;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>