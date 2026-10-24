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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/style.css') ?>">
</head>
<body>
<header class="main-header">
    <div class="nav-container">
        <a href="<?= url('public/index.php') ?>" class="brand-logo">
            <div class="header-logo-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#e5a855" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 2v6a3 3 0 0 1-3 3 3 3 0 0 1-3-3V2"/><path d="M15 2v18"/><path d="M6 2v7a3 3 0 0 0 6 0V2"/><path d="M9 12v8"/>
                </svg>
            </div>
            <div class="header-brand-text">
                <span class="header-brand-title">UrbanEats</span>
                <span class="header-brand-subtitle">Good Food &bull; Better Mood</span>
            </div>
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
                <?php elseif ($userRole === 'staff' || strtolower((string)$userRole) === 'kitchen staff'): ?>
                    <a href="<?= url('orders/kitchen.php') ?>">Kitchen Queue</a>
                    <a href="<?= url('public/menu/manage.php') ?>">Menu & Stock</a>
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
