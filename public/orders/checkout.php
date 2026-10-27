<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php?msg=login_required');
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: ../menu/index.php');
    exit;
}

$error = '';
$success_order_id = null;


$cart_dish_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($cart_dish_ids), '?'));
$stmt = $pdo->prepare("SELECT dish_id, dish_name, price FROM dishes WHERE dish_id IN ($placeholders) AND is_active = 1");
$stmt->execute($cart_dish_ids);
$db_dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_amount = 0.00;
$dishes_by_id = [];
foreach ($db_dishes as $d) {
    $dishes_by_id[$d['dish_id']] = $d;
    $qty = (int)$_SESSION['cart'][$d['dish_id']];
    $total_amount += $d['price'] * $qty;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_type = $_POST['order_type'] ?? 'delivery';
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cash_on_delivery';
    $user_id = (int)$_SESSION['user_id'];

    if ($order_type === 'delivery' && empty($delivery_address)) {
        $error = 'Delivery address is required for delivery orders.';
    } elseif (empty($phone_number)) {
        $error = 'Phone number is required.';
    } else {
        try {
            // ---- SINGLE DATABASE TRANSACTION BEGINS ----
            $pdo->beginTransaction();

            // 1. Check Stock & Reduce Stock for each item using B component function
            foreach ($_SESSION['cart'] as $dish_id => $qty) {
                $dish_id = (int)$dish_id;
                $qty = (int)$qty;

                
                if (!reduce_stock($pdo, $dish_id, $qty)) {
                    $dish_name = $dishes_by_id[$dish_id]['dish_name'] ?? "Item #$dish_id";
                    throw new Exception("Insufficient stock for '$dish_name'. Order cancelled.");
                }
            }

            // 2. Insert into orders table
            $stmt_order = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, order_type, delivery_address, phone_number, payment_method, order_status)
                VALUES (?, ?, ?, ?, ?, ?, 'Placed')
            ");
            $stmt_order->execute([
                $user_id,
                $total_amount,
                $order_type,
                $order_type === 'delivery' ? $delivery_address : null,
                $phone_number,
                $payment_method
            ]);
            $order_id = (int)$pdo->lastInsertId();

            // 3. Insert order items into order_items table (with price at purchase)
            $stmt_item = $pdo->prepare("
                INSERT INTO order_items (order_id, dish_id, quantity, price_at_purchase)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($_SESSION['cart'] as $dish_id => $qty) {
                $dish_id = (int)$dish_id;
                $qty = (int)$qty;
                $price_at_purchase = $dishes_by_id[$dish_id]['price'];

                $stmt_item->execute([$order_id, $dish_id, $qty, $price_at_purchase]);
            }

            // 4. Record order status history
            $stmt_history = $pdo->prepare("
                INSERT INTO order_status_history (order_id, status)
                VALUES (?, 'Placed')
            ");
            $stmt_history->execute([$order_id]);

            
            log_action('ORDER_PLACED', "Order #{$order_id} placed for LKR {$total_amount}");

    
            $pdo->commit();

            unset($_SESSION['cart']);

            
            header("Location: confirmation.php?order_id=" . $order_id);
            exit;

        } catch (Exception $e) {
            // Any failure -> Rollback completely
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="hero-section">
    <h1 class="hero-title">Checkout</h1>
    <p class="hero-subtitle">Complete your order details below</p>
</div>

<div class="container" style="max-width: 800px; margin: 30px auto; padding: 20px; color: #fff;">
    <?php if (!empty($error)): ?>
        <p style="background: rgba(255,107,107,0.2); color: #ff6b6b; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <!-- Order Summary -->
        <div style="background: #1a1a1a; padding: 20px; border-radius: 8px;">
            <h3 style="border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 15px; color: #d4af37;">Order Summary</h3>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($db_dishes as $item): 
                    $qty = $_SESSION['cart'][$item['dish_id']];
                    $item_total = $item['price'] * $qty;
                ?>
                    <li style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span><?= htmlspecialchars($item['dish_name']) ?> x <?= $qty ?></span>
                        <span>LKR <?= number_format($item_total, 2) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <hr style="border: 0; border-top: 1px solid #333; margin: 15px 0;">
            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2rem;">
                <span>Total:</span>
                <span style="color: #51cf66;">LKR <?= number_format($total_amount, 2) ?></span>
            </div>
        </div>

        <!-- Checkout Form -->
        <div style="background: #1a1a1a; padding: 20px; border-radius: 8px;">
            <h3 style="border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 15px; color: #d4af37;">Delivery Details</h3>
            <form method="POST">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Order Type:</label>
                    <select name="order_type" id="order_type" style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;" onchange="toggleAddress(this.value)">
                        <option value="delivery">Delivery</option>
                        <option value="pickup">Pickup</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;" id="address_group">
                    <label style="display: block; margin-bottom: 5px;">Delivery Address:</label>
                    <textarea name="delivery_address" rows="3" style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;" placeholder="Enter full delivery address..."></textarea>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Phone Number:</label>
                    <input type="text" name="phone_number" required style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;" placeholder="07XXXXXXXX">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px;">Payment Method:</label>
                    <select name="payment_method" style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                        <option value="cash_on_delivery">Cash on Delivery / Pickup</option>
                        <option value="card">Card Payment (Simulated)</option>
                        <option value="bank_transfer">Bank Transfer (Simulated)</option>
                    </select>
                </div>

                <button type="submit" style="width: 100%; padding: 12px; background: #d4af37; color: #000; border: none; font-weight: bold; font-size: 1rem; border-radius: 4px; cursor: pointer;">
                    Place Order (LKR <?= number_format($total_amount, 2) ?>)
                </button>
            </form>
        </div>

    </div>
</div>

<script>
function toggleAddress(type) {
    const addressGroup = document.getElementById('address_group');
    if (type === 'pickup') {
        addressGroup.style.display = 'none';
    } else {
        addressGroup.style.display = 'block';
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>