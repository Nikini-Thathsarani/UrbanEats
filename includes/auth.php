<?php
declare(strict_types=1);

/*
 * Guard file used by EVERY page in the application.
 *
 * Put this at the top of every page:
 *     require_once __DIR__ . '/../includes/auth.php';
 * It loads helpers, error handling, a hardened session, the database
 * connection ($pdo) and CSRF helpers. Then, on protected pages, call:
 *     require_login();                         // any logged-in user
 *     require_role('administrator');           // one role
 *     require_role('staff', 'administrator');  // any of these roles
 *
 * Roles: 'administrator', 'staff', 'customer'
 * Session: $_SESSION['user_id'], $_SESSION['role'] (+ full_name, email)
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/errors.php';

const SESSION_TIMEOUT = 1800; // 30 minutes of inactivity

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_name('URBANEATS_SID');
    session_start();
}

/** Log the user out automatically after SESSION_TIMEOUT seconds of inactivity. */
function enforce_session_timeout(): void
{
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $last = $_SESSION['last_activity'] ?? time();

    if (time() - $last > SESSION_TIMEOUT) {
        $_SESSION = [];
        session_regenerate_id(true);
        $GLOBALS['session_expired'] = true;
        return;
    }

    $_SESSION['last_activity'] = time();
}

start_secure_session();
enforce_session_timeout();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/csrf.php';

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

/** Completely end the session (used by logout and by forced sign-outs). */
function destroy_session(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy();
}

/**
 * Only logged-in users may continue.
 * The account is re-checked in the database on every request, so a
 * deactivated user is signed out and a role change applies immediately.
 */
function require_login(): void
{
    global $pdo;

    if (!is_logged_in()) {
        $query = !empty($GLOBALS['session_expired']) ? '?timeout=1' : '';
        redirect('auth/login.php' . $query);
    }

    $stmt = $pdo->prepare(
        'SELECT u.is_active, r.role_name
         FROM users u
         JOIN roles r ON r.role_id = u.role_id
         WHERE u.user_id = ?'
    );
    $stmt->execute([current_user_id()]);
    $account = $stmt->fetch();

    if (!$account || (int) $account['is_active'] !== 1) {
        destroy_session();
        start_secure_session();
        redirect('auth/login.php?deactivated=1');
    }

    $_SESSION['role'] = $account['role_name'];
}

/** Only users with one of the given roles may continue; others get "access denied". */
function require_role(string ...$roles): void
{
    require_login();

    if (!in_array(current_role(), $roles, true)) {
        log_action('ACCESS_DENIED', 'Tried to open ' . ($_SERVER['REQUEST_URI'] ?? ''));
        redirect('public/access-denied.php');
    }
}