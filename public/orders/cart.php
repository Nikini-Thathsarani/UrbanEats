<?php
require_once __DIR__ . '/../../includes/auth.php';


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $dish_id = (int)$_POST['dish_id'];
        
        if ($_POST['action'] === 'add') {
            $_SESSION['cart'][$dish_id] = ($_SESSION['cart'][$dish_id] ?? 0) + 1;
        } elseif ($_POST['action'] === 'update') {
            $qty = (int)$_POST['quantity'];
            if ($qty > 0) {
                $_SESSION['cart'][$dish_id] = $qty;
            } else {
                unset($_SESSION['cart'][$dish_id]);
            }
        } elseif ($_POST['action'] === 'remove') {
            unset($_SESSION['cart'][$dish_id]);
        }
        header("Location: cart.php");
        exit;
    }
}


$cart_items = [];
$subtotal = 0.00;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $stmt = $pdo->query("SELECT d.*, c.category_name FROM dishes d LEFT JOIN categories c ON d.category_id = c.category_id WHERE d.dish_id IN ($ids)");
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="hero-section">
    <h1 class="hero-title">Your Shopping Cart</h1>
    <p class="hero-subtitle">Review your selected dishes before checkout</p>
</div>

<div class="container" style="max-width: 900px; margin: 30px auto; padding: 20px; color: #fff;">
    <?php if (empty($_SESSION['cart'])): ?>
        <p style="text-align: center; font-size: 1.2rem; color: #aaa;">Your cart is empty. <a href="../menu/index.php" style="color: #d4af37;">Browse Menu</a></p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; background: #1a1a1a; margin-bottom: 20px;">
            <thead>
                <tr style="background: #2a2a2a; border-bottom: 2px solid #444;">
                    <th style="padding: 12px; text-align: left;">Dish</th>
                    <th style="padding: 12px;">Price</th>
                    <th style="padding: 12px;">Quantity</th>
                    <th style="padding: 12px;">Total</th>
                    <th style="padding: 12px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): 
                    $dish_id = $item['dish_id'];
                    $qty = $_SESSION['cart'][$dish_id];
                    $item_total = $item['price'] * $qty;
                    $subtotal += $item_total;
                ?>
                <tr style="border-bottom: 1px solid #333;">
                    <td style="padding: 12px; display: flex; align-items: center; gap: 10px;">
                        <img src="../uploads/<?= htmlspecialchars($item['image_url']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" onerror="this.src='../uploads/bg1.jpg';">
                        <span><?= htmlspecialchars($item['dish_name']) ?></span>
                    </td>
                    <td style="padding: 12px;">LKR <?= number_format($item['price'], 2) ?></td>
                    <td style="padding: 12px;">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="dish_id" value="<?= $dish_id ?>">
                            <input type="number" name="quantity" value="<?= $qty ?>" min="1" style="width: 60px; padding: 5px; background: #2a2a2a; color: #fff; border: 1px solid #444; border-radius: 4px;" onchange="this.form.submit()">
                        </form>
                    </td>
                    <td style="padding: 12px;">LKR <?= number_format($item_total, 2) ?></td>
                    <td style="padding: 12px;">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="dish_id" value="<?= $dish_id ?>">
                            <button type="submit" style="background: #ff6b6b; color: #fff; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer;">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="display: flex; justify-content: space-between; align-items: center; background: #1a1a1a; padding: 20px; border-radius: 8px;">
            <h2>Subtotal: LKR <?= number_format($subtotal, 2) ?></h2>
            <a href="checkout.php" style="background: #d4af37; color: #000; padding: 12px 25px; text-decoration: none; font-weight: bold; border-radius: 4px;">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>