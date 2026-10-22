<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/header.php';

$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$category = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?? 0;
$sort = in_array($_GET['sort'] ?? '', ['price_asc', 'price_desc', 'name']) ? $_GET['sort'] : 'name';

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
$limit = 6;
$offset = ($page - 1) * $limit;

$query = "SELECT d.*, c.category_name, i.quantity_in_stock 
          FROM dishes d 
          JOIN categories c ON d.category_id = c.category_id 
          JOIN inventory i ON d.dish_id = i.dish_id 
          WHERE d.is_active = 1";

$params = [];
if ($search) {
    $query .= " AND (d.dish_name LIKE ? OR d.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category) {
    $query .= " AND d.category_id = ?";
    $params[] = $category;
}

$sort_order = match($sort) {
    'price_asc' => 'd.price ASC',
    'price_desc' => 'd.price DESC',
    default => 'd.dish_name ASC'
};
$query .= " ORDER BY $sort_order LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<div class="hero-section">
    <h1 class="hero-title">Where Taste Meets Elegance</h1>
    <p class="hero-subtitle">Fine Dining & Fresh Cloud Kitchen Deliveries</p>
</div>

<div class="filter-bar">
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search dishes..." value="<?= htmlspecialchars($search) ?>">
        <select name="category">
            <option value="0">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>" <?= $category == $cat['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['category_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="sort">
            <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Sort by Name</option>
            <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
        </select>
        <button type="submit" class="btn-gold">Filter</button>
    </form>
</div>

<div class="dish-grid">
    <?php foreach ($dishes as $dish): ?>
        <div class="dish-card">
            <img src="../uploads/<?= htmlspecialchars($dish['image_url']) ?>" alt="<?= htmlspecialchars($dish['dish_name']) ?>">
            <div class="dish-info">
                <h3 class="dish-title"><?= htmlspecialchars($dish['dish_name']) ?></h3>
                <p class="dish-price">LKR <?= number_format($dish['price'], 2) ?></p>
                <a href="detail.php?id=<?= $dish['dish_id'] ?>" class="btn-gold">View Details</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>