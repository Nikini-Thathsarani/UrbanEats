<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect away
if (is_logged_in()) {
    redirect('public/index.php');
}

$errors = [];
$full_name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $full_name        = trim((string)($_POST['full_name'] ?? ''));
    $email            = strtolower(trim((string)($_POST['email'] ?? '')));
    $phone            = trim((string)($_POST['phone'] ?? ''));
    $password         = (string)($_POST['password'] ?? '');
    $confirm_password = (string)($_POST['confirm_password'] ?? '');

    if ($full_name === '' || strlen($full_name) > 100) {
        $errors[] = 'Please enter your full name (up to 100 characters).';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if ($phone !== '' && (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone) || strlen($phone) > 20)) {
        $errors[] = 'Please enter a valid phone number (up to 20 digits).';
    }

    if (!$errors) {
        // Check for duplicate email
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'An account with this email address already exists. Please log in.';
        } else {
            // Find customer role id
            $stmt = $pdo->prepare("SELECT role_id FROM roles WHERE role_name = 'customer' LIMIT 1");
            $stmt->execute();
            $customer_role = $stmt->fetch();

            if (!$customer_role) {
                $errors[] = 'System configuration error: Customer role not found.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    'INSERT INTO users (role_id, full_name, email, password_hash, phone, is_active)
                     VALUES (?, ?, ?, ?, ?, 1)'
                );
                $stmt->execute([
                    (int) $customer_role['role_id'],
                    $full_name,
                    $email,
                    $hash,
                    $phone !== '' ? $phone : null
                ]);

                $newUserId = (int) $pdo->lastInsertId();

                log_action('REGISTER', 'New customer registration: ' . $email, $newUserId);

                redirect('auth/login.php?registered=1');
            }
        }
    }
}

$pageTitle = 'Create Your Account';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* ==========================================================================
   UrbanEats Luxury Dark & Gold Register Layout
   Integrated with shared header & footer
   ========================================================================== */

.register-page-wrapper {
    background: radial-gradient(circle at 15% 30%, rgba(229, 168, 85, 0.08) 0%, transparent 45%),
                radial-gradient(circle at 85% 70%, rgba(229, 168, 85, 0.05) 0%, transparent 40%),
                #07080a;
    min-height: calc(100vh - 140px);
    padding: 30px 20px 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #f5f5f7;
    position: relative;
    overflow: hidden;
}

/* AMBIENT BACKGROUND GLOWS */
.ambient-glow-top-left {
    position: absolute;
    top: -100px;
    left: -100px;
    width: 650px;
    height: 650px;
    background: radial-gradient(circle, rgba(229, 168, 85, 0.12) 0%, rgba(200, 130, 40, 0.04) 40%, transparent 70%);
    pointer-events: none;
    z-index: 0;
}

.ambient-glow-center-right {
    position: absolute;
    top: 20%;
    right: -150px;
    width: 700px;
    height: 700px;
    background: radial-gradient(circle, rgba(229, 168, 85, 0.08) 0%, transparent 60%);
    pointer-events: none;
    z-index: 0;
}

.register-main-container {
    width: 100%;
    max-width: 1280px;
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 52px;
    align-items: start; /* Align image and form on the exact same top horizontal line */
    position: relative;
    z-index: 2;
}

/* LEFT COLUMN: HERO BURGER & DETAILS */
.left-promo-column {
    display: flex;
    flex-direction: column;
    margin-top: 0;
}

.promo-burger-photo-wrap {
    width: 100%;
    max-width: 620px;
    margin-bottom: 22px;
    margin-top: 0;
    position: relative;
}

.promo-burger-photo-wrap img {
    width: 100%;
    height: 380px;
    object-fit: cover;
    display: block;
    border-radius: 22px;
    box-shadow: 0 18px 50px rgba(0, 0, 0, 0.85), 0 0 32px rgba(229, 168, 85, 0.15);
    border: 1px solid rgba(229, 168, 85, 0.25);
    transition: transform 0.4s ease, box-shadow 0.4s ease;
}

.promo-burger-photo-wrap:hover img {
    transform: scale(1.02);
    box-shadow: 0 22px 55px rgba(0, 0, 0, 0.9), 0 0 40px rgba(229, 168, 85, 0.25);
}

.tag-join-urbaneats {
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 2px;
    color: #e5a855;
    text-transform: uppercase;
    margin-bottom: 8px;
    display: block;
}

.promo-heading {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 2.5rem;
    line-height: 1.2;
    color: #ffffff;
    font-weight: 700;
    margin-bottom: 14px;
}

.promo-heading .gold-text {
    color: #e5a855;
}

.promo-subtext {
    color: #a4aab7;
    font-size: 0.98rem;
    line-height: 1.55;
    margin-bottom: 26px;
    max-width: 530px;
}

.promo-features-trio {
    display: flex;
    gap: 32px;
}

.trio-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
}

.trio-icon-circle {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    border: 1.5px solid #e5a855;
    background: rgba(229, 168, 85, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #e5a855;
    box-shadow: 0 4px 15px rgba(229, 168, 85, 0.12);
    transition: all 0.3s ease;
}

.trio-item:hover .trio-icon-circle {
    background: rgba(229, 168, 85, 0.2);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(229, 168, 85, 0.25);
}

.trio-label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #e5e7eb;
    max-width: 95px;
    line-height: 1.3;
}

/* RIGHT COLUMN: REGISTRATION CARD */
.right-card-column {
    position: relative;
    margin-top: 0;
}

.luxury-register-card {
    background: rgba(14, 16, 21, 0.92);
    border: 1.5px solid rgba(229, 168, 85, 0.45);
    border-radius: 28px;
    padding: 38px 36px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.75), 0 0 35px rgba(229, 168, 85, 0.09);
    backdrop-filter: blur(16px);
    position: relative;
    overflow: hidden;
    margin-top: 0;
}

/* BOTANICAL GOLD LEAF IN TOP-RIGHT OF CARD */
.card-top-right-leaf {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 95px;
    height: 95px;
    pointer-events: none;
    opacity: 0.65;
}

/* CARD HEADER */
.card-center-header {
    text-align: center;
    margin-bottom: 24px;
}

.card-logo-ring {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    border: 2px solid #e5a855;
    background: rgba(229, 168, 85, 0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    box-shadow: 0 0 15px rgba(229, 168, 85, 0.2);
}

.card-brand-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.55rem;
    color: #e5a855;
    font-weight: 700;
    margin-bottom: 2px;
}

.card-brand-slogan {
    font-size: 0.75rem;
    color: #9ca3af;
    letter-spacing: 0.8px;
    margin-bottom: 16px;
}

.card-main-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.95rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 6px;
}

.card-main-sub {
    font-size: 0.92rem;
    color: #a4aab7;
}

/* FORM FIELDS */
.form-box {
    margin-top: 22px;
}

.input-field-block {
    margin-bottom: 18px;
}

.input-field-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    font-weight: 500;
    color: #e5e7eb;
    margin-bottom: 8px;
}

.label-gold-icon {
    color: #e5a855;
    display: inline-flex;
}

.input-wrapper-inner {
    position: relative;
    display: flex;
    align-items: center;
}

.styled-text-input {
    width: 100%;
    padding: 13px 18px;
    background: rgba(8, 10, 14, 0.8);
    border: 1px solid rgba(229, 168, 85, 0.28);
    border-radius: 10px;
    color: #ffffff;
    font-size: 0.95rem;
    transition: all 0.25s ease;
}

.styled-text-input::placeholder {
    color: #555d6b;
}

.styled-text-input:focus {
    outline: none;
    border-color: #e5a855;
    box-shadow: 0 0 15px rgba(229, 168, 85, 0.25);
    background: rgba(12, 15, 20, 0.95);
}

.input-with-eye {
    padding-right: 48px;
}

.eye-icon-button {
    position: absolute;
    right: 14px;
    background: transparent;
    border: none;
    cursor: pointer;
    color: #8b92a0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4px;
    transition: color 0.2s ease;
}

.eye-icon-button:hover {
    color: #e5a855;
}

/* GOLD GRADIENT SUBMIT BUTTON */
.btn-create-account-gold {
    width: 100%;
    padding: 15px;
    background: linear-gradient(90deg, #f3b344 0%, #e0992a 100%);
    color: #0d0f13;
    border: none;
    border-radius: 10px;
    font-size: 1.05rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 6px 20px rgba(224, 153, 42, 0.35);
    transition: all 0.3s ease;
    margin-top: 10px;
}

.btn-create-account-gold:hover {
    background: linear-gradient(90deg, #f7bf57 0%, #ebaa3b 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(224, 153, 42, 0.48);
}

.btn-create-account-gold:active {
    transform: translateY(0);
}

/* OR DIVIDER */
.or-divider-row {
    display: flex;
    align-items: center;
    text-align: center;
    color: #555d6b;
    font-size: 0.8rem;
    font-weight: 600;
    margin: 22px 0 18px;
}

.or-divider-row::before,
.or-divider-row::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.or-divider-row span {
    padding: 0 14px;
    letter-spacing: 1px;
}

.card-bottom-signin {
    text-align: center;
    font-size: 0.92rem;
    color: #a4aab7;
}

.gold-signin-link {
    color: #e5a855;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s ease;
}

.gold-signin-link:hover {
    color: #f7bf57;
    text-decoration: underline;
}

/* ERROR ALERTS */
.error-alert-card {
    background: rgba(185, 28, 28, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.45);
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 18px;
    color: #fca5a5;
    font-size: 0.88rem;
}

.error-alert-card ul {
    margin: 0;
    padding-left: 20px;
}

/* CORNER BOTANICAL LEAF LINE-ART (BOTTOM LEFT OF SCREEN) */
.screen-bottom-left-leaves {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 170px;
    height: 170px;
    pointer-events: none;
    z-index: 1;
    opacity: 0.75;
}

/* CORNER METALLIC GOLD WAVE ACCENT (BOTTOM LEFT) */
.screen-bottom-gold-wave {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 380px;
    height: 80px;
    pointer-events: none;
    z-index: 1;
    opacity: 0.9;
}

/* RESPONSIVE DESIGN */
@media (max-width: 1024px) {
    .register-main-container {
        grid-template-columns: 1fr;
        gap: 40px;
    }

    .left-promo-column {
        align-items: center;
        text-align: center;
    }

    .promo-subtext {
        max-width: 100%;
    }

    .promo-features-trio {
        justify-content: center;
    }

    .promo-burger-photo-wrap {
        max-width: 500px;
    }
}

@media (max-width: 520px) {
    .register-page-wrapper {
        padding: 20px 14px 60px;
    }

    .luxury-register-card {
        padding: 28px 20px;
        border-radius: 20px;
    }

    .promo-heading {
        font-size: 2.2rem;
    }

    .promo-features-trio {
        gap: 20px;
    }
}
</style>

<div class="register-page-wrapper">
    <!-- AMBIENT BACKGROUND GLOWS -->
    <div class="ambient-glow-top-left"></div>
    <div class="ambient-glow-center-right"></div>

    <div class="register-main-container">
        
        <!-- LEFT PROMO COLUMN -->
        <div class="left-promo-column">
            <div class="promo-burger-photo-wrap">
                <img src="<?= url('public/uploads/regiburger.jpeg') ?>" alt="UrbanEats Delicious Burger with French Fries" onerror="this.src='<?= url('public/uploads/burger.jpg') ?>';">
            </div>

            <span class="tag-join-urbaneats">JOIN URBANEATS</span>
            <h1 class="promo-heading">Good Food<br>Brings People <span class="gold-text">Together</span></h1>
            <p class="promo-subtext">
                Create your account and get access to delicious meals, exclusive offers and a smoother ordering experience.
            </p>

            <div class="promo-features-trio">
                <div class="trio-item">
                    <div class="trio-icon-circle" title="Fast Delivery">
                        <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="15" cy="5" r="1"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/>
                        </svg>
                    </div>
                    <span class="trio-label">Fast Delivery</span>
                </div>

                <div class="trio-item">
                    <div class="trio-icon-circle" title="Safe & Secure">
                        <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>
                        </svg>
                    </div>
                    <span class="trio-label">Safe & Secure</span>
                </div>

                <div class="trio-item">
                    <div class="trio-icon-circle" title="Fresh & Tasty Meals">
                        <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/><line x1="6" y1="17" x2="18" y2="17"/>
                        </svg>
                    </div>
                    <span class="trio-label">Fresh & Tasty Meals</span>
                </div>
            </div>
        </div>

        <!-- RIGHT REGISTRATION CARD -->
        <div class="right-card-column">
            <div class="luxury-register-card">
                
                <!-- BOTANICAL GOLD LEAF SVG (TOP RIGHT OF CARD) -->
                <svg class="card-top-right-leaf" viewBox="0 0 100 100" fill="none" stroke="#d4af37" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M85 15 Q60 30 40 70"/>
                    <path d="M75 22 Q90 18 92 30 Q78 30 75 22 Z"/>
                    <path d="M62 33 Q76 26 80 40 Q66 40 62 33 Z"/>
                    <path d="M50 48 Q64 42 66 54 Q52 56 50 48 Z"/>
                    <path d="M42 65 Q54 58 56 70 Q44 72 42 65 Z"/>
                </svg>

                <div class="card-center-header">
                    <div class="card-logo-ring">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#e5a855" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 2v6a3 3 0 0 1-3 3 3 3 0 0 1-3-3V2"/><path d="M15 2v18"/><path d="M6 2v7a3 3 0 0 0 6 0V2"/><path d="M9 12v8"/>
                        </svg>
                    </div>
                    <div class="card-brand-title">UrbanEats</div>
                    <div class="card-brand-slogan">Good Food &bull; Better Mood</div>

                    <h2 class="card-main-title">Create Your Account</h2>
                    <p class="card-main-sub">Join UrbanEats and start your food journey today!</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="error-alert-card">
                        <ul>
                            <?php foreach ($errors as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('auth/register.php') ?>" class="form-box" novalidate>
                    <?= csrf_field() ?>

                    <!-- Full Name -->
                    <div class="input-field-block">
                        <label for="full_name" class="input-field-label">
                            <span class="label-gold-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </span>
                            Full Name
                        </label>
                        <div class="input-wrapper-inner">
                            <input id="full_name" name="full_name" type="text" class="styled-text-input"
                                   placeholder="Enter your full name" maxlength="100" required
                                   value="<?= e($full_name) ?>" autocomplete="name">
                        </div>
                    </div>

                    <!-- Email Address -->
                    <div class="input-field-block">
                        <label for="email" class="input-field-label">
                            <span class="label-gold-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </span>
                            Email Address
                        </label>
                        <div class="input-wrapper-inner">
                            <input id="email" name="email" type="email" class="styled-text-input"
                                   placeholder="Enter your email address" maxlength="150" required
                                   value="<?= e($email) ?>" autocomplete="email">
                        </div>
                    </div>

                    <!-- Phone Number -->
                    <div class="input-field-block">
                        <label for="phone" class="input-field-label">
                            <span class="label-gold-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </span>
                            Phone Number
                        </label>
                        <div class="input-wrapper-inner">
                            <input id="phone" name="phone" type="tel" class="styled-text-input"
                                   placeholder="Enter your phone number" maxlength="20"
                                   value="<?= e($phone) ?>" autocomplete="tel">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="input-field-block">
                        <label for="password" class="input-field-label">
                            <span class="label-gold-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            Password
                        </label>
                        <div class="input-wrapper-inner">
                            <input id="password" name="password" type="password" class="styled-text-input input-with-eye"
                                   placeholder="Create a password" minlength="8" required autocomplete="new-password">
                            <button type="button" class="eye-icon-button" aria-label="Toggle password visibility" onclick="togglePasswordVisibility('password', this)">
                                <svg class="eye-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="input-field-block">
                        <label for="confirm_password" class="input-field-label">
                            <span class="label-gold-icon">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            Confirm Password
                        </label>
                        <div class="input-wrapper-inner">
                            <input id="confirm_password" name="confirm_password" type="password" class="styled-text-input input-with-eye"
                                   placeholder="Confirm your password" minlength="8" required autocomplete="new-password">
                            <button type="button" class="eye-icon-button" aria-label="Toggle password visibility" onclick="togglePasswordVisibility('confirm_password', this)">
                                <svg class="eye-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-create-account-gold">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>
                        </svg>
                        Create Account &rarr;
                    </button>
                </form>

                <div class="or-divider-row">
                    <span>OR</span>
                </div>

                <div class="card-bottom-signin">
                    Already have an account? <a href="<?= url('auth/login.php') ?>" class="gold-signin-link">Sign in</a>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- CORNER BOTANICAL LEAF LINE-ART (BOTTOM LEFT OF SCREEN) -->
<svg class="screen-bottom-left-leaves" viewBox="0 0 160 160" fill="none" stroke="#d4af37" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round">
    <path d="M15 155 Q40 100 125 45"/>
    <path d="M35 125 Q45 95 65 105 Q50 130 35 125 Z"/>
    <path d="M60 100 Q80 75 98 88 Q78 110 60 100 Z"/>
    <path d="M85 75 Q115 50 130 65 Q105 85 85 75 Z"/>
    <path d="M110 55 Q135 30 148 42 Q130 60 110 55 Z"/>
</svg>

<!-- CORNER METALLIC GOLD WAVE ACCENT (BOTTOM LEFT) -->
<svg class="screen-bottom-gold-wave" viewBox="0 0 380 80" fill="none">
    <path d="M-20,80 Q90,20 220,55 Q310,80 380,45 L380,80 L-20,80 Z" fill="url(#metallicGoldGrad1)"/>
    <path d="M-20,80 Q120,35 260,65 Q330,85 380,60 L380,80 L-20,80 Z" fill="url(#metallicGoldGrad2)" opacity="0.6"/>
    <defs>
        <linearGradient id="metallicGoldGrad1" x1="0%" y1="100%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#73510c"/>
            <stop offset="35%" stop-color="#e5b045"/>
            <stop offset="70%" stop-color="#fae498"/>
            <stop offset="100%" stop-color="#8a610e"/>
        </linearGradient>
        <linearGradient id="metallicGoldGrad2" x1="0%" y1="100%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#b88319"/>
            <stop offset="50%" stop-color="#f5d376"/>
            <stop offset="100%" stop-color="#6e4d0a"/>
        </linearGradient>
    </defs>
</svg>

<!-- PASSWORD VISIBILITY TOGGLE SCRIPT -->
<script>
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const isPassword = input.getAttribute('type') === 'password';
    input.setAttribute('type', isPassword ? 'text' : 'password');

    if (isPassword) {
        button.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e5a855" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m2 2 20 20"/><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
            </svg>
        `;
    } else {
        button.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
            </svg>
        `;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>