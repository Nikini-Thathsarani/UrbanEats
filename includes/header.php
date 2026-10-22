<?php
declare(strict_types=1);

/**
 * Shared Header for UrbanEats Cloud Kitchen.
 * Included across all components (A, B, C).
 * Navigation dynamically updates based on session authentication & user role.
 */

require_once __DIR__ . '/auth.php';

$pageTitle = isset($pageTitle) ? $pageTitle . ' | UrbanEats' : 'UrbanEats Cloud Kitchen';
$isLoggedIn = is_logged_in();
$userRole = current_role();
$userName = $_SESSION['full_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= url('public/css/style.css') ?>">
</head>
<body>
<header class="main-header">
    <div class="nav-container">
        <a href="<?= url('public/index.php') ?>" class="brand-logo">
            <span class="logo-icon">🍲</span>
            <span class="logo-text">Urban<strong>Eats</strong></span>
        </a>

        <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-label="Toggle navigation">
        <label for="nav-toggle" class="nav-toggle-label">
            <span></span>
            <span></span>
            <span></span>
        </label>

        <nav class="nav-links">
            <a href="<?= url('public/index.php') ?>">Home</a>
            <a href="<?= url('public/index.php#menu') ?>">Menu</a>

            <?php if (!$isLoggedIn): ?>
                <a href="<?= url('auth/login.php') ?>" class="nav-btn-link">Login</a>
                <a href="<?= url('auth/register.php') ?>" class="btn btn-sm btn-primary">Register</a>
            <?php else: ?>

                <?php if ($userRole === 'customer'): ?>
                    <a href="<?= url('orders/cart.php') ?>">My Cart</a>
                    <a href="<?= url('orders/history.php') ?>">My Orders</a>
                <?php elseif ($userRole === 'staff'): ?>
                    <a href="<?= url('orders/kitchen.php') ?>">Kitchen Queue</a>
                    <a href="<?= url('menu/manage.php') ?>">Manage Menu</a>
                    <a href="<?= url('menu/stock.php') ?>">Stock</a>
                <?php elseif ($userRole === 'administrator'): ?>
                    <a href="<?= url('admin/dashboard.php') ?>">Dashboard</a>
                    <a href="<?= url('admin/users.php') ?>">Users</a>
                    <a href="<?= url('admin/audit-log.php') ?>">Audit Log</a>
                    <a href="<?= url('admin/reports.php') ?>">Reports</a>
                <?php endif; ?>

                <div class="nav-user-dropdown">
                    <span class="user-badge role-<?= e($userRole) ?>">
                        <?= e($userName) ?> (<?= e(ucfirst((string)$userRole)) ?>)
                    </span>
                    <a href="<?= url('auth/profile.php') ?>" title="My Profile">Profile</a>
                    <a href="<?= url('auth/logout.php') ?>" class="btn-logout">Logout</a>
                </div>

            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="page-content">
