<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/header.php';

$dish_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$dish_id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT d.*, c.category_name, i.quantity_in_stock 
                       FROM dishes d 
                       JOIN categories c ON d.category_id = c.category_id 
                       JOIN inventory i ON d.dish_id = i.dish_id 
                       WHERE d.dish_id = ? AND d.is_active = 1");
$stmt->execute([$dish_id]);
$dish = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dish) {
    echo "<div class='container'><p>Dish not found.</p></div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}
?>

<div style="max-width: 800px; margin: 40px auto; padding: 20px; background: var(--card-bg); border-radius: 12px; display: flex; gap: 20px;">
    <img src="../uploads/<?= htmlspecialchars($dish['image_url']) ?>" style="width: 50%; border-radius: 8px; object-fit: cover;">
    <div>
        <h2 style="color: var(--accent-gold);"><?= htmlspecialchars($dish['dish_name']) ?></h2>
        <p><strong>Category:</strong> <?= htmlspecialchars($dish['category_name']) ?></p>
        <p><?= htmlspecialchars($dish['description']) ?></p>
        <p style="font-size: 1.4rem; color: #fff;"><strong>LKR <?= number_format($dish['price'], 2) ?></strong></p>
        <p><strong>Availability:</strong> 
            <?= $dish['quantity_in_stock'] > 0 ? '<span style="color:green;">In Stock</span>' : '<span style="color:red;">Out of Stock</span>' ?>
        </p>
        <?php if ($dish['quantity_in_stock'] > 0): ?>
            <form action="../orders/cart.php" method="POST">
                <input type="hidden" name="dish_id" value="<?= $dish['dish_id'] ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?= $dish['quantity_in_stock'] ?>" style="width: 60px; padding: 5px;">
                <button type="submit" class="btn-gold">Add to Cart</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>