<?php
declare(strict_types=1);

/*
 * Shared helper functions used by every component.
 * Loaded automatically by includes/auth.php.
 */

// ---- BASE_URL: web path of the project folder, e.g. "/UrbanEats" on XAMPP ----
if (!defined('BASE_URL')) {
    $projectRoot = str_replace('\\', '/', dirname(__DIR__));
    $docRoot     = isset($_SERVER['DOCUMENT_ROOT'])
        ? str_replace('\\', '/', rtrim((string) realpath($_SERVER['DOCUMENT_ROOT']), '/\\'))
        : '';

    define(
        'BASE_URL',
        ($docRoot !== '' && stripos($projectRoot, $docRoot) === 0)
            ? substr($projectRoot, strlen($docRoot))
            : ''
    );
}

/** Escape a value for safe output in HTML. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Build a URL inside the project, e.g. url('auth/login.php'). */
function url(string $path): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Redirect to a project path and stop the script. */
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Write an entry to the audit log.
 * Components B and C call this for important events, e.g.
 *   log_action('DISH_CREATED', 'Dish #12 Chicken Kottu');
 *   log_action('ORDER_PLACED', 'Order #55');
 * The user id is taken from the session unless passed explicitly.
 * It never throws: a logging failure must not break the page.
 */
function log_action(string $action, ?string $details = null, ?int $userId = null): void
{
    global $pdo;

    try {
        $userId ??= isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

        $stmt = $pdo->prepare(
            'INSERT INTO audit_log (user_id, action, details, ip_address)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            mb_substr($action, 0, 50),
            $details !== null ? mb_substr($details, 0, 255) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $ex) {
        app_log('log_action failed: ' . $ex->getMessage());
    }
}

/**
 * Reduce stock safely during checkout (used by Component C).
 */
function reduce_stock($pdo, int $dish_id, int $qty): bool
{
    $stmt = $pdo->prepare("SELECT quantity_in_stock FROM inventory WHERE dish_id = ? FOR UPDATE");
    $stmt->execute([$dish_id]);
    $stock = $stmt->fetchColumn();

    if ($stock === false || (int)$stock < $qty) {
        return false; // Insufficient stock available
    }

    $updateStmt = $pdo->prepare("UPDATE inventory SET quantity_in_stock = quantity_in_stock - ? WHERE dish_id = ? AND quantity_in_stock >= ?");
    return $updateStmt->execute([$qty, $dish_id, $qty]);
}

/**
 * Securely validate and upload dish images (used by Component B).
 */
function upload_dish_image(array $file): string
{
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
    
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_mime = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_ext, $allowed_extensions, true) || !in_array($file_mime, $allowed_mimes, true)) {
        throw new Exception("Invalid file type uploaded.");
    }
    
    if ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
        throw new Exception("File size exceeds the 2MB limit.");
    }
    
    $new_filename = uniqid('dish_', true) . '.' . $file_ext;
    $destination = __DIR__ . '/../public/uploads/' . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $new_filename;
    }
    
    throw new Exception("Failed to save the uploaded image.");
}

/**
 * Record stock movements (restock, wastage, adjustments) for Component B[cite: 3].
 */
function log_stock_movement($pdo, int $dish_id, int $change_qty, string $movement_type, ?string $reason = null): bool
{
    try {
        $user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        
        $stmt = $pdo->prepare("
            INSERT INTO stock_movements (dish_id, user_id, change_qty, movement_type, reason, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$dish_id, $user_id, $change_qty, $movement_type, $reason]);
    } catch (Throwable $ex) {
        app_log('log_stock_movement failed: ' . $ex->getMessage());
        return false;
    }
}