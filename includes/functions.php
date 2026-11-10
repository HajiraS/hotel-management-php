<?php
/**
 * includes/functions.php - small reusable helpers:
 * output escaping, CSRF protection, flash messages, validation.
 */
declare(strict_types=1);

const ROOM_TYPES    = ['Standard', 'Deluxe', 'Suite'];
const ROOM_STATUSES = ['Available', 'Occupied', 'Maintenance'];

/** Escape text before printing it in HTML (prevents XSS). */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* ----------------------------- CSRF ----------------------------------- */

/** Create (once per session) and return a random CSRF token. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden <input> to place inside every POST form. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** True only if the submitted token matches the one stored in the session. */
function verify_csrf(): bool
{
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $sent); // timing-safe comparison
}

/* ------------------------- Flash messages ----------------------------- */

/** Store a one-time message that survives exactly one redirect. */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Read and clear the stored message. */
function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/* ---------------------------- Validation ------------------------------ */

/** Turn raw input into a positive int ID, or null if it is not valid. */
function parse_room_id(mixed $raw): ?int
{
    $id = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

/**
 * Validate room form input on the server.
 *
 * @return array{0: array<string,string>, 1: array<string,string>}
 *         [errors, cleaned values]. No errors => cleaned values are safe to save.
 */
function validate_room(array $input, bool $requireNumber = true): array
{
    $get = static fn(string $key): string =>
        isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : '';

    $clean = [
        'room_number' => $get('room_number'),
        'room_type'   => $get('room_type'),
        'price'       => $get('price'),
        'status'      => $get('status'),
    ];
    $errors = [];

    if ($requireNumber) {
        if ($clean['room_number'] === '') {
            $errors['room_number'] = 'Room number is required.';
        } elseif (!preg_match('/^[A-Za-z0-9-]{1,10}$/', $clean['room_number'])) {
            $errors['room_number'] = 'Use 1-10 letters, numbers or dashes.';
        }
    }

    if (!in_array($clean['room_type'], ROOM_TYPES, true)) {
        $errors['room_type'] = 'Please choose a valid room type.';
    }

    $price = filter_var($clean['price'], FILTER_VALIDATE_FLOAT);
    if ($clean['price'] === '') {
        $errors['price'] = 'Price is required.';
    } elseif ($price === false || $price <= 0 || $price > 99999999.99) {
        $errors['price'] = 'Enter a price greater than 0 (e.g. 129.99).';
    } else {
        $clean['price'] = number_format($price, 2, '.', ''); // normalise to 2 decimals
    }

    if (!in_array($clean['status'], ROOM_STATUSES, true)) {
        $errors['status'] = 'Please choose a valid status.';
    }

    return [$errors, $clean];
}
