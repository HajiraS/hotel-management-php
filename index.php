<?php
/**
 * index.php - Dashboard + room list + search (the "R" in CRUD).
 *
 *   index.php               -> dashboard (summary cards + table)
 *   index.php?view=rooms    -> "All Rooms" (table only)
 *   index.php?q=203         -> search by room number
 */
declare(strict_types=1);
require_once __DIR__ . '/db.php';

$view   = (($_GET['view'] ?? '') === 'rooms') ? 'rooms' : 'dashboard';
$search = (isset($_GET['q']) && is_string($_GET['q'])) ? trim($_GET['q']) : '';
$search = mb_substr($search, 0, 10);              // room numbers are max 10 chars

$stats   = ['total' => 0, 'available' => 0, 'occupied' => 0, 'maintenance' => 0];
$rooms   = [];
$dbError = null;

try {
    // 1) Summary counts - one query, no user input, so no parameters needed.
    //    SUM(status = 'X') adds up 1 for every matching row (0 otherwise);
    //    COALESCE turns NULL (empty table) into 0.
    $row = db()->query(
        "SELECT COUNT(*)                                  AS total,
                COALESCE(SUM(status = 'Available'),   0)  AS available,
                COALESCE(SUM(status = 'Occupied'),    0)  AS occupied,
                COALESCE(SUM(status = 'Maintenance'), 0)  AS maintenance
         FROM rooms"
    )->fetch();
    $stats = array_map('intval', $row);

    // 2) The room list - filtered when the user searched.
    if ($search !== '') {
        // addcslashes() escapes \ % _ so the user's text is matched literally
        // instead of acting as LIKE wildcards. The value still travels as a
        // bound parameter, never inside the SQL string.
        $like = '%' . addcslashes($search, '\\%_') . '%';
        $stmt = db()->prepare(
            'SELECT id, room_number, room_type, price, status
             FROM rooms
             WHERE room_number LIKE :search
             ORDER BY LENGTH(room_number), room_number'
        );
        $stmt->execute([':search' => $like]);
    } else {
        $stmt = db()->query(
            'SELECT id, room_number, room_type, price, status
             FROM rooms
             ORDER BY LENGTH(room_number), room_number'
        );
    }
    $rooms = $stmt->fetchAll();
} catch (PDOException $ex) {
    error_log('Load rooms failed: ' . $ex->getMessage());
    $dbError = APP_DEBUG
        ? 'Database error: ' . $ex->getMessage() . ' (Did you import database.sql?)'
        : 'We could not load the rooms right now. Please try again later.';
}

$cards = [
    ['key' => 'total',       'label' => 'Total Rooms',       'icon' => 'bed-double'],
    ['key' => 'available',   'label' => 'Available Rooms',   'icon' => 'circle-check'],
    ['key' => 'occupied',    'label' => 'Occupied Rooms',    'icon' => 'key-round'],
    ['key' => 'maintenance', 'label' => 'Maintenance Rooms', 'icon' => 'wrench'],
];

$pageTitle = $view === 'rooms' ? 'All Rooms' : 'Dashboard';
$activeNav = $view === 'rooms' ? 'rooms' : 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div>
        <h1><?= $view === 'rooms' ? 'All Rooms' : 'Hotel Room Management' ?></h1>
        <p class="subtitle">Welcome back, Admin. Here is a live overview of every room in the hotel.</p>
    </div>
    <a href="create.php" class="btn btn-primary"><i data-lucide="plus"></i> Add Room</a>
</section>

<?php if ($dbError): ?>
    <div class="alert alert-error" role="alert"><i data-lucide="circle-alert"></i><span><?= e($dbError) ?></span></div>
<?php endif; ?>

<?php if ($view === 'dashboard'): ?>
    <section class="stats" aria-label="Room summary">
        <?php foreach ($cards as $card): ?>
            <article class="stat stat-<?= e($card['key']) ?>">
                <span class="stat-icon"><i data-lucide="<?= e($card['icon']) ?>"></i></span>
                <div>
                    <span class="stat-value"><?= (int) $stats[$card['key']] ?></span>
                    <span class="stat-label"><?= e($card['label']) ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<section class="panel" id="rooms">
    <div class="panel-head">
        <div>
            <h2>Room Management</h2>
            <p class="muted">
                <?= count($rooms) ?> room<?= count($rooms) === 1 ? '' : 's' ?>
                <?= $search !== '' ? 'matching “' . e($search) . '”' : 'in total' ?>
            </p>
        </div>

        <!-- GET form: the search term becomes ?q=... in the URL (bookmarkable) -->
        <form method="get" action="index.php" class="search" role="search">
            <?php if ($view === 'rooms'): ?><input type="hidden" name="view" value="rooms"><?php endif; ?>
            <div class="search-box">
                <i data-lucide="search"></i>
                <input type="search" name="q" value="<?= e($search) ?>" maxlength="10"
                       placeholder="Search room number…" aria-label="Search by room number">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Search</button>
            <?php if ($search !== ''): ?>
                <a class="btn btn-ghost btn-sm" href="index.php<?= $view === 'rooms' ? '?view=rooms' : '' ?>">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!$rooms): ?>
        <div class="empty">
            <span class="empty-icon"><i data-lucide="<?= $search !== '' ? 'search-x' : 'door-closed' ?>"></i></span>
            <?php if ($search !== ''): ?>
                <h3>No rooms match “<?= e($search) ?>”</h3>
                <p>Check the room number and try again, or clear the search to see every room.</p>
            <?php else: ?>
                <h3>No rooms yet</h3>
                <p>Your hotel has no rooms on record. Add the first one to get started.</p>
                <a href="create.php" class="btn btn-primary"><i data-lucide="plus"></i> Add Room</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Type</th>
                        <th>Price / Night</th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td class="room-no"><?= e($room['room_number']) ?></td>
                        <td><?= e($room['room_type']) ?></td>
                        <td>$<?= e(number_format((float) $room['price'], 2)) ?></td>
                        <td><span class="badge badge-<?= e(strtolower($room['status'])) ?>"><?= e($room['status']) ?></span></td>
                        <td class="col-actions">
                            <div class="actions">
                                <a class="icon-btn edit" href="edit.php?id=<?= (int) $room['id'] ?>"
                                   title="Edit room <?= e($room['room_number']) ?>" aria-label="Edit room <?= e($room['room_number']) ?>">
                                    <i data-lucide="pencil"></i>
                                </a>
                                <!-- Delete is a POST form (never a link) + CSRF token + confirm dialog -->
                                <form method="post" action="delete.php"
                                      data-confirm="Room <?= e($room['room_number']) ?> will be permanently removed.">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $room['id'] ?>">
                                    <button type="submit" class="icon-btn delete"
                                            title="Delete room <?= e($room['room_number']) ?>" aria-label="Delete room <?= e($room['room_number']) ?>">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
