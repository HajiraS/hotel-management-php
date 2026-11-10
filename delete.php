<?php
/**
 * delete.php - Delete a room (the "D" in CRUD).
 *
 * Accepts POST only. Deleting through a GET link would be dangerous: browsers,
 * crawlers and link-prefetchers follow links automatically.
 * This page has no HTML - it does its work and redirects back to the dashboard.
 */
declare(strict_types=1);
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('405 Method Not Allowed - use the Delete button on the dashboard.');
}

if (!verify_csrf()) {
    http_response_code(403);
    flash('error', 'Security check failed. Please try again.');
    redirect('index.php');
}

$roomId = parse_room_id($_POST['id'] ?? null);
if ($roomId === null) {
    flash('error', 'That room ID is not valid.');
    redirect('index.php');
}

try {
    // Confirm the room exists so we can show its number in the message.
    $find = db()->prepare('SELECT room_number FROM rooms WHERE id = :id');
    $find->execute([':id' => $roomId]);
    $room = $find->fetch();

    if (!$room) {
        flash('error', 'Room not found. It may already have been deleted.');
        redirect('index.php');
    }

    $delete = db()->prepare('DELETE FROM rooms WHERE id = :id');
    $delete->execute([':id' => $roomId]);

    flash('success', 'Room ' . $room['room_number'] . ' was deleted.');
} catch (PDOException $ex) {
    error_log('Delete room failed: ' . $ex->getMessage());
    flash('error', APP_DEBUG ? 'Database error: ' . $ex->getMessage()
                             : 'Could not delete the room. Please try again.');
}

redirect('index.php');
