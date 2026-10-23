<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$dish_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$dish_id) {
    header('Location: manage.php');
    exit;
}

$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT d.*, i.quantity_in_stock FROM dishes d LEFT JOIN inventory i ON d.dish_id = i.dish_id WHERE d.dish_id = ?");
$stmt->execute([$dish_id]);
$dish = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dish) {
    header('Location: manage.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dish_name = trim($_POST['dish_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $stock_qty = filter_input(INPUT_POST, 'quantity_in_stock', FILTER_VALIDATE_INT);

    if (empty($dish_name) || empty($description) || $price === false || $category_id === false) {
        $error = 'Please fill in all required fields correctly.';
    } else {
        $image_url = $dish['image_url']; 
      
        if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $image_url = upload_dish_image($_FILES['dish_image']); // includes/functions.php හි ඇති secure function එක භාවිත වේ
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        if (empty($error)) {
            try {
                $pdo->beginTransaction();

               
                $stmt_update = $pdo->prepare("UPDATE dishes SET dish_name = ?, description = ?, price = ?, category_id = ?, image_url = ? WHERE dish_id = ?");
                $stmt_update->execute([$dish_name, $description, $price, $category_id, $image_url, $dish_id]);

                
                $stmt_inv = $pdo->prepare("UPDATE inventory SET quantity_in_stock = ? WHERE dish_id = ?");
                $stmt_inv->execute([$stock_qty, $dish_id]);

                $pdo->commit();
                log_action('DISH_UPDATED', "Updated dish ID #{$dish_id} ({$dish_name})");
                
                $success = 'Dish updated successfully!';
                
               
                $stmt->execute([$dish_id]);
                $dish = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="hero-section">
    <h1 class="hero-title">Edit Dish</h1>
    <p class="hero-subtitle">Update Menu Item Details</p>
</div>

<div class="form-container" style="max-width: 600px; margin: 30px auto; padding: 20px; background: #1a1a1a; border-radius: 8px; color: #fff;">
    <?php if (!empty($error)): ?>
        <p style="color: #ff6b6b; background: rgba(255,107,107,0.1); padding: 10px; border-radius: 4px;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p style="color: #51cf66; background: rgba(81,207,102,0.1); padding: 10px; border-radius: 4px;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Dish Name:</label>
            <input type="text" name="dish_name" value="<?= htmlspecialchars($dish['dish_name']) ?>" required style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Category:</label>
            <select name="category_id" required style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= $dish['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Description:</label>
            <textarea name="description" rows="4" required style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;"><?= htmlspecialchars($dish['description']) ?></textarea>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Price (LKR):</label>
            <input type="number" step="0.01" name="price" value="<?= $dish['price'] ?>" required style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Stock Quantity:</label>
            <input type="number" name="quantity_in_stock" value="<?= $dish['quantity_in_stock'] ?? 0 ?>" required style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Current Image:</label>
            <img src="../uploads/<?= htmlspecialchars($dish['image_url']) ?>" alt="" style="width: 80px; height: 80px; object-fit: cover; display: block; margin-bottom: 10px; border-radius: 4px;" onerror="this.src='../uploads/bg1.jpg';">
            <label style="display: block; margin-bottom: 5px;">Change Dish Image (Optional):</label>
            <input type="file" name="dish_image" accept="image/jpeg, image/png, image/webp" style="color: #fff;">
        </div>

        <button type="submit" class="btn-gold" style="padding: 10px 20px; background: #d4af37; border: none; color: #000; font-weight: bold; cursor: pointer; border-radius: 4px;">Update Dish</button>
        <a href="manage.php" style="margin-left: 10px; color: #aaa; text-decoration: none;">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>