<?php
/**
 * Session Configuration – LAB VERSION (adjusted for cookie hijacking demo)
 * Allows JavaScript to read cookie (HttpOnly=0) and reuses session ID (no regeneration)
 */

// Allow JavaScript to read the cookie (required for XSS theft)
ini_set('session.cookie_httponly', 0);
ini_set('session.use_strict_mode', 1);

// ✅ LAB: Change SameSite to Lax (Strict may block cross-site usage; Lax works for most localhost cases)
ini_set('session.cookie_samesite', 'Lax');

// Use secure cookie only if HTTPS is enabled
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// ✅ LAB: Increase timeout to 1 hour (from 30 minutes) for flexibility
define('SESSION_TIMEOUT', 3600);

session_name('SIS_SESSION');
session_start();

function checkSessionTimeout(): bool
{
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        if ($elapsed > SESSION_TIMEOUT) {
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
 * ✅ LAB: Disable session ID regeneration so the stolen ID never becomes invalid
 */
function regenerateSession(): void
{
    // Do nothing – keep the same session ID forever (for lab only)
    return;
}

// Run checks
checkSessionTimeout();
regenerateSession();
?>