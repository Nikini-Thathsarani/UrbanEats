<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/header.php';


$error = '';
$success = '';


$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dish_name = trim($_POST['dish_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $initial_stock = filter_input(INPUT_POST, 'initial_stock', FILTER_VALIDATE_INT) ?? 0;
    
    if (empty($dish_name) || empty($description) || $price === false || $category_id === false) {
        $error = 'Please fill in all required fields correctly.';
    } else {
        $image_url = 'bg1.jpg'; // Default fallback image
        
        
        if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] !== UPLOAD_ERR_NO_FILE) {
           
            $target_dir = __DIR__ . '/../uploads/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $file = $_FILES['dish_image'];
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (in_array($mime_type, $allowed_types) && $file['size'] <= 2 * 1024 * 1024) {
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $image_url = uniqid('dish_', true) . '.' . $extension;
                move_uploaded_file($file['tmp_name'], $target_dir . $image_url);
            } else {
                $error = 'Invalid image type or size (Max 2MB, JPG/PNG only).';
            }
        }
        
        if (empty($error)) {
            try {
                $pdo->beginTransaction();
                
                
                $stmt = $pdo->prepare("INSERT INTO dishes (dish_name, description, price, category_id, image_url, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt->execute([$dish_name, $description, $price, $category_id, $image_url]);
                $dish_id = $pdo->lastInsertId();
                
                
                $stmt_inv = $pdo->prepare("INSERT INTO inventory (dish_id, quantity_in_stock) VALUES (?, ?)");
                $stmt_inv->execute([$dish_id, $initial_stock]);
                
                $pdo->commit();
                $success = 'Dish added successfully!';
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="hero-section">
    <h1 class="hero-title">Add New Dish</h1>
    <p class="hero-subtitle">Manage Kitchen Menu & Inventory</p>
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
            <input type="text" name="dish_name" required style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Category:</label>
            <select name="category_id" required style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Description:</label>
            <textarea name="description" rows="4" required style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;"></textarea>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Price (LKR):</label>
            <input type="number" step="0.01" name="price" required style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Initial Stock Quantity:</label>
            <input type="number" name="initial_stock" value="10" required style="width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Dish Image (JPG, PNG):</label>
            <input type="file" name="dish_image" accept="image/jpeg, image/png" style="color: #fff;">
        </div>

        <button type="submit" class="btn-gold" style="padding: 10px 20px; background: #d4af37; border: none; color: #000; font-weight: bold; cursor: pointer; border-radius: 4px;">Add Dish</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>