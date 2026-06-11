<?php
/**
 * Session Configuration
 * Secure session management with timeout
 */

// Session security settings (must be set before session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Use secure cookies only if HTTPS is enabled
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Session timeout in seconds (30 minutes)
define('SESSION_TIMEOUT', 1800);

session_name('SIS_SESSION');
session_start();

/**
 * Check if session has timed out
 */
function checkSessionTimeout(): bool
{
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        if ($elapsed > SESSION_TIMEOUT) {
            // Session expired - destroy it
            session_unset();
            session_destroy();
            session_start();
            return true;
        }
    }
    $_SESSION['last_activity'] = time();
    return false;
}

/**
 * Regenerate session ID periodically to prevent fixation
 */
function regenerateSession(): void
{
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > 300) {
        // Regenerate every 5 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Run checks on every request
checkSessionTimeout();
regenerateSession();
