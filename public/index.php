<?php
declare(strict_types=1);

$pageTitle = 'Home';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="hero-section">
    <div class="hero-content">
        <span class="hero-tag">Colombo's Premier Cloud Kitchen</span>
        <h1>Delicious Meals Delivered Fast & Fresh</h1>
        <p class="hero-subtitle">
            Crafted with passion, premium Sri Lankan spices, and uncompromising hygiene.
            Explore our curated menu and enjoy seamless doorstep delivery.
        </p>
        <div class="hero-buttons">
            <a href="#menu" class="btn btn-primary btn-lg">Explore Menu</a>
            <?php if (!is_logged_in()): ?>
                <a href="<?= url('auth/register.php') ?>" class="btn btn-outline-light btn-lg">Join UrbanEats</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <!-- Value propositions / features -->
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">⚡</div>
            <h3>Fast Delivery</h3>
            <p>Hot, fresh meals straight from our kitchen to your table within 30-45 minutes.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🌿</div>
            <h3>Fresh Ingredients</h3>
            <p>We source only high-grade produce, authentic local spices, and fresh meats daily.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🛡️</div>
            <h3>Hygienic Standards</h3>
            <p>Strict safety and sanitary protocols at every step of meal preparation.</p>
        </div>
    </div>

    <!-- Menu Section (Component B integration point) -->
    <section id="menu" class="menu-showcase mt-40">
        <div class="section-title text-center">
            <h2>Our Featured Specialties</h2>
            <p class="text-muted">Discover mouthwatering Sri Lankan favorites and fusion bowls</p>
        </div>

        <div class="menu-grid">
            <div class="dish-card">
                <div class="dish-badge">Popular</div>
                <div class="dish-info">
                    <h4>Signature Chicken Kottu</h4>
                    <p class="dish-desc">Shredded godamba roti stir-fried with juicy seasoned chicken, fresh vegetables, egg, and fiery curry sauce.</p>
                    <div class="dish-meta">
                        <span class="dish-price">LKR 1,450.00</span>
                        <?php if (is_logged_in()): ?>
                            <a href="<?= url('orders/cart.php') ?>" class="btn btn-sm btn-primary">Order Now</a>
                        <?php else: ?>
                            <a href="<?= url('auth/login.php') ?>" class="btn btn-sm btn-outline">Sign In to Order</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="dish-card">
                <div class="dish-badge">Chef's Choice</div>
                <div class="dish-info">
                    <h4>Ceylon Spiced Biryani</h4>
                    <p class="dish-desc">Fragrant basmati rice cooked in slow-simmered ghee, aromatic spices, tender chicken, mint sambol, and boiled egg.</p>
                    <div class="dish-meta">
                        <span class="dish-price">LKR 1,650.00</span>
                        <?php if (is_logged_in()): ?>
                            <a href="<?= url('orders/cart.php') ?>" class="btn btn-sm btn-primary">Order Now</a>
                        <?php else: ?>
                            <a href="<?= url('auth/login.php') ?>" class="btn btn-sm btn-outline">Sign In to Order</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="dish-card">
                <div class="dish-info">
                    <h4>Devilled Prawns Bowl</h4>
                    <p class="dish-desc">Crispy jumbo prawns tossed in a tangy, spicy sweet-and-sour chili sauce with capsicum, onions, and steamed rice.</p>
                    <div class="dish-meta">
                        <span class="dish-price">LKR 1,850.00</span>
                        <?php if (is_logged_in()): ?>
                            <a href="<?= url('orders/cart.php') ?>" class="btn btn-sm btn-primary">Order Now</a>
                        <?php else: ?>
                            <a href="<?= url('auth/login.php') ?>" class="btn btn-sm btn-outline">Sign In to Order</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
