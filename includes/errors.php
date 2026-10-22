<?php
declare(strict_types=1);

/*
 * Central error handling.
 * - Nothing technical is ever shown to the visitor.
 * - Everything is written to logs/error.log.
 * - Exceptions and fatal errors show a friendly page.
 */

define('ERROR_LOG_FILE', dirname(__DIR__) . '/logs/error.log');

if (!is_dir(dirname(ERROR_LOG_FILE))) {
    @mkdir(dirname(ERROR_LOG_FILE), 0755, true);
}

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', ERROR_LOG_FILE);

// Buffer output so a half-built page can be discarded if an error happens.
if (ob_get_level() === 0) {
    ob_start();
}

/** Append a line to logs/error.log. */
function app_log(string $message): void
{
    @error_log('[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, 3, ERROR_LOG_FILE);
}

/** Show a friendly, self-contained error page (no technical details). */
function render_error_page(int $code, string $title, string $message): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    if (!headers_sent()) {
        http_response_code($code);
    }

    $home = (defined('BASE_URL') ? BASE_URL : '') . '/public/index.php';
    $t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $m = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $h = htmlspecialchars($home, ENT_QUOTES, 'UTF-8');

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$t} | UrbanEats</title>
    <style>
        body{font-family:Arial,sans-serif;background:#fff8f2;color:#302522;margin:0;
             display:flex;min-height:100vh;align-items:center;justify-content:center;padding:16px}
        main{max-width:480px;background:#fff;border:1px solid #eadbd4;border-radius:12px;padding:32px;text-align:center}
        h1{color:#8b2635;margin-top:0}
        a{color:#8b2635;font-weight:bold}
    </style>
</head>
<body>
    <main>
        <h1>{$t}</h1>
        <p>{$m}</p>
        <p><a href="{$h}">Back to the home page</a></p>
    </main>
</body>
</html>
HTML;
}

// Warnings / notices: log only, keep the page running.
set_error_handler(function (int $no, string $str, string $file, int $line): bool {
    if (!(error_reporting() & $no)) {
        return false;
    }
    app_log("PHP error [$no] $str in $file:$line");
    return true;
});

// Uncaught exceptions (including PDOException): log + friendly page.
set_exception_handler(function (Throwable $ex): void {
    app_log(
        get_class($ex) . ': ' . $ex->getMessage()
        . ' in ' . $ex->getFile() . ':' . $ex->getLine()
    );
    render_error_page(
        500,
        'Something went wrong',
        'Sorry, we hit a problem on our side. Please try again in a moment.'
    );
});

// Fatal errors that cannot be caught as exceptions.
register_shutdown_function(function (): void {
    $err = error_get_last();
    $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

    if ($err !== null && in_array($err['type'], $fatal, true)) {
        app_log("Fatal error: {$err['message']} in {$err['file']}:{$err['line']}");
        render_error_page(
            500,
            'Something went wrong',
            'Sorry, we hit a problem on our side. Please try again in a moment.'
        );
    }
});