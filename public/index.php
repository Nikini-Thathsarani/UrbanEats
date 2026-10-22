
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrbanEats | Cloud Kitchen</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <h1>UrbanEats</h1>
        <p>Fresh food. Made with care.</p>
    </header>

<<<<<<< Updated upstream
    <main class="container">
        <h2>Discover Our Menu</h2>
        <p>Welcome to UrbanEats! Explore our menu and find your favourites.</p>
=======
<div class="hero-section">
    <div class="hero-content">
        <span class="hero-tag">Colombo's Premier Cloud Kitchen</span>
        <h1>Delicious Meals Delivered Fast & Fresh</h1>
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
</div>
>>>>>>> Stashed changes

        <section class="menu-placeholder">
            <h3>Our delicious menu is coming soon!</h3>
            <p>We're preparing something tasty for you.</p>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 UrbanEats Cloud Kitchen</p>
    </footer>
</body>
</html>
