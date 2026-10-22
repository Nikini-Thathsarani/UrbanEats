<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    log_action('LOGOUT', 'User signed out');
}

destroy_session();

redirect('auth/login.php?logged_out=1');