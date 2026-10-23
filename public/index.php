<?php
declare(strict_types=1);

$pageTitle = 'Home';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="hero-section">
    <div class="hero-content">
        <span class="hero-tag">Colombo's Premier Cloud Kitchen</span>
        <h1 class="hero-title">Delicious Meals Delivered Fast &amp; Fresh</h1>
        <p class="hero-subtitle">
            Crafted with passion, premium Sri Lankan spices, and uncompromising hygiene.
            Explore our curated menu and enjoy seamless doorstep delivery.
        </p>
        <div class="hero-buttons">
            <a href="menu/index.php" class="btn btn-primary btn-lg">Explore Menu</a>
            <?php if (!is_logged_in()): ?>
                <a href="<?= url('auth/register.php') ?>" class="btn btn-outline-light btn-lg">Join UrbanEats</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="container">
    <section class="features-grid" aria-label="Why choose UrbanEats">
        <article class="feature-card">
            <div class="feature-icon" aria-hidden="true">⚡</div>
            <h2>Fast Delivery</h2>
            <p>Hot, fresh meals straight from our kitchen to your table within 30-45 minutes.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon" aria-hidden="true">🌿</div>
            <h2>Fresh Ingredients</h2>
            <p>We source high-grade produce, authentic local spices, and fresh meats daily.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon" aria-hidden="true">🛡️</div>
            <h2>Hygienic Standards</h2>
            <p>Strict safety and sanitary protocols at every step of meal preparation.</p>
        </article>
    </section>

    <section id="menu" class="menu-showcase mt-40">
        <div class="section-title text-center">
            <h2>Our Featured Specialties</h2>
            <p class="text-muted">Discover mouthwatering Sri Lankan favorites and fusion bowls.</p>
            <a href="menu/index.php" class="btn btn-primary">Explore the full menu</a>
        </div>

        <div class="menu-grid">
            <article class="dish-card">
                <div class="dish-badge">Popular</div>
                <div class="dish-info">
                    <h3>Signature Chicken Kottu</h3>
                    <p class="dish-desc">Shredded godamba roti stir-fried with seasoned chicken, fresh vegetables, egg, and curry sauce.</p>
                    <p class="dish-price">LKR 1,450.00</p>
                </div>
            </article>

            <article class="dish-card">
                <div class="dish-badge">Chef's Choice</div>
                <div class="dish-info">
                    <h3>Ceylon Spiced Biryani</h3>
                    <p class="dish-desc">Fragrant basmati rice cooked with aromatic spices, tender chicken, mint sambol, and boiled egg.</p>
                    <p class="dish-price">LKR 1,650.00</p>
                </div>
            </article>

            <article class="dish-card">
                <div class="dish-info">
                    <h3>Devilled Prawns Bowl</h3>
                    <p class="dish-desc">Crispy prawns tossed in a tangy, sweet-and-sour chili sauce with capsicum, onions, and steamed rice.</p>
                    <p class="dish-price">LKR 1,850.00</p>
                </div>
            </article>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
