<?php
declare(strict_types=1);

/*
 * CSRF protection for every POST form.
 * In the form:      <?= csrf_field() ?>
 * In the handler:   csrf_require();   (first thing inside the POST branch)
 */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = $_POST['csrf_token'] ?? '';

    return is_string($sent)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $sent);
}

/** Stop the request with a friendly 403 page if the token is missing or wrong. */
function csrf_require(): void
{
    if (!csrf_valid()) {
        app_log('CSRF check failed for ' . ($_SERVER['REQUEST_URI'] ?? ''));
        render_error_page(
            403,
            'Invalid request',
            'Your form expired or the request was not valid. Please go back, refresh the page and try again.'
        );
        exit;
    }
}