<?php
/**
 * edit.php - Update an existing room (the "U" in CRUD).
 *
 * GET  edit.php?id=5 -> load room 5 and pre-fill the form
 * POST               -> verify CSRF -> validate -> UPDATE -> redirect
 */
declare(strict_types=1);
require_once __DIR__ . '/db.php';

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

// The id comes from the URL (GET) or from a hidden field (POST). Never trust it.
$roomId = parse_room_id($isPost ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null));
if ($roomId === null) {
    flash('error', 'That room ID is not valid.');
    redirect('index.php');
}

// Make sure the room really exists before showing or changing anything.
try {
    $stmt = db()->prepare('SELECT id, room_number, room_type, price, status FROM rooms WHERE id = :id');
    $stmt->execute([':id' => $roomId]);
    $room = $stmt->fetch();                            // false when no row found
} catch (PDOException $ex) {
    error_log('Load room failed: ' . $ex->getMessage());
    flash('error', 'Could not load that room. Please try again.');
    redirect('index.php');
}
if (!$room) {
    flash('error', 'Room not found. It may have been deleted.');
    redirect('index.php');
}

$errors = [];
$values = [                                            // pre-populate with current DB values
    'room_number' => $room['room_number'],
    'room_type'   => $room['room_type'],
    'price'       => number_format((float) $room['price'], 2, '.', ''),
    'status'      => $room['status'],
];

if ($isPost) {
    [$errors, $clean] = validate_room($_POST, false);  // room number is not editable
    $values = array_merge($clean, ['room_number' => $room['room_number']]);

    if (!verify_csrf()) {
        http_response_code(403);
        $errors['form'] = 'Your session expired or the form was invalid. Please try again.';
    }

    if (!$errors) {
        try {
            $update = db()->prepare(
                'UPDATE rooms
                 SET room_type = :room_type, price = :price, status = :status
                 WHERE id = :id'
            );
            $update->execute([
                ':room_type' => $values['room_type'],
                ':price'     => $values['price'],
                ':status'    => $values['status'],
                ':id'        => $roomId,
            ]);

            flash('success', 'Room ' . $room['room_number'] . ' was updated successfully.');
            redirect('index.php');
        } catch (PDOException $ex) {
            error_log('Update room failed: ' . $ex->getMessage());
            $errors['form'] = APP_DEBUG ? 'Database error: ' . $ex->getMessage()
                                        : 'Something went wrong. Please try again.';
        }
    }
}

$pageTitle  = 'Edit Room ' . $room['room_number'];
$activeNav  = 'rooms';
$mode       = 'edit';
$formAction = 'edit.php';
require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div>
        <h1>Edit Room <?= e($room['room_number']) ?></h1>
        <p class="subtitle">Update the room type, nightly price or availability status.</p>
    </div>
</section>

<?php require __DIR__ . '/includes/room_form.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
