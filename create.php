<?php
/**
 * create.php - Add a new room (the "C" in CRUD).
 *
 * GET  -> show the empty form
 * POST -> verify CSRF -> validate -> check duplicate -> INSERT -> redirect
 */
declare(strict_types=1);
require_once __DIR__ . '/db.php';

$errors = [];
$values = ['room_number' => '', 'room_type' => 'Standard', 'price' => '', 'status' => 'Available'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$errors, $values] = validate_room($_POST);       // server-side validation + cleaning

    if (!verify_csrf()) {
        http_response_code(403);
        $errors['form'] = 'Your session expired or the form was invalid. Please try again.';
    }

    if (!$errors) {
        try {
            // Friendly duplicate check (the UNIQUE index in MySQL is the real guarantee).
            $check = db()->prepare('SELECT id FROM rooms WHERE room_number = :room_number LIMIT 1');
            $check->execute([':room_number' => $values['room_number']]);

            if ($check->fetch()) {
                $errors['room_number'] = 'Room ' . $values['room_number'] . ' already exists.';
            } else {
                $insert = db()->prepare(
                    'INSERT INTO rooms (room_number, room_type, price, status)
                     VALUES (:room_number, :room_type, :price, :status)'
                );
                $insert->execute([
                    ':room_number' => $values['room_number'],
                    ':room_type'   => $values['room_type'],
                    ':price'       => $values['price'],
                    ':status'      => $values['status'],
                ]);

                flash('success', 'Room ' . $values['room_number'] . ' was added successfully.');
                redirect('index.php');                 // Post/Redirect/Get: refresh won't re-submit
            }
        } catch (PDOException $ex) {
            if ((string) $ex->getCode() === '23000') { // SQLSTATE 23000 = UNIQUE violation (race condition)
                $errors['room_number'] = 'Room ' . $values['room_number'] . ' already exists.';
            } else {
                error_log('Create room failed: ' . $ex->getMessage());
                $errors['form'] = APP_DEBUG ? 'Database error: ' . $ex->getMessage()
                                            : 'Something went wrong. Please try again.';
            }
        }
    }
}

$pageTitle  = 'Add New Room';
$activeNav  = 'create';
$mode       = 'create';
$formAction = 'create.php';
require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div>
        <h1>Add New Room</h1>
        <p class="subtitle">Enter the details below to add a room to your inventory.</p>
    </div>
</section>

<?php require __DIR__ . '/includes/room_form.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
