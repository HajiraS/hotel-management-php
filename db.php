<?php
/**
 * db.php - database connection + bootstrap.
 *
 * Every page starts with: require_once __DIR__ . '/db.php';
 * It (1) starts a secure session, (2) loads helper functions,
 * (3) provides db(), which returns ONE shared PDO connection.
 */
declare(strict_types=1);

// ---- Configuration: change these to match your MySQL setup -------------
// XAMPP / MAMP defaults are usually root with an empty password
// (MAMP's default password is "root").
const DB_HOST    = '127.0.0.1';
const DB_NAME    = 'hotel_db';
const DB_USER    = 'root';
const DB_PASS    = '';
const DB_CHARSET = 'utf8mb4';

// Set to false in production so internal error details are never shown.
const APP_DEBUG  = true;

// ---- Session (needed for CSRF tokens and flash messages) ---------------
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,   // JavaScript cannot read the session cookie
        'samesite' => 'Lax',  // cookie is not sent on cross-site POSTs (extra CSRF defence)
    ]);
    session_start();
}

require_once __DIR__ . '/includes/functions.php';

/**
 * Return a shared PDO connection (created on first call).
 */
function db(): PDO
{
    static $pdo = null;          // 'static' keeps the value between calls
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // errors become exceptions we can catch
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows come back as ['column' => value]
            PDO::ATTR_EMULATE_PREPARES   => false,                  // real server-side prepared statements
        ]);
    } catch (PDOException $e) {
        error_log('DB connection failed: ' . $e->getMessage());     // log details privately
        http_response_code(500);
        exit(APP_DEBUG
            ? 'Database connection failed: ' . htmlspecialchars($e->getMessage())
            : 'Service temporarily unavailable.');
    }

    return $pdo;
}
