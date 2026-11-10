<?php
/**
 * includes/room_form.php - the Add/Edit form, shared by create.php and edit.php
 * so the markup lives in exactly one place.
 *
 * Expected variables:
 *   $mode       'create' | 'edit'
 *   $formAction URL the form posts to
 *   $values     array with room_number, room_type, price, status
 *   $errors     array of field => message (may contain 'form' for a general error)
 *   $roomId     (edit only) the room's id
 */
?>
<?php if (!empty($errors['form'])): ?>
    <div class="alert alert-error" role="alert">
        <i data-lucide="circle-alert"></i><span><?= e($errors['form']) ?></span>
    </div>
<?php endif; ?>

<form method="post" action="<?= e($formAction) ?>" class="panel form-card">
    <?= csrf_field() /* hidden anti-CSRF token */ ?>
    <?php if ($mode === 'edit'): ?>
        <input type="hidden" name="id" value="<?= (int) $roomId ?>">
    <?php endif; ?>

    <div class="form-grid">
        <div class="field<?= isset($errors['room_number']) ? ' has-error' : '' ?>">
            <label for="room_number">Room Number</label>
            <input type="text" id="room_number" name="room_number" maxlength="10"
                   value="<?= e($values['room_number']) ?>" placeholder="e.g. 204"
                   <?= $mode === 'edit' ? 'readonly' : 'required autofocus' ?>>
            <?php if (isset($errors['room_number'])): ?>
                <p class="field-error"><?= e($errors['room_number']) ?></p>
            <?php elseif ($mode === 'edit'): ?>
                <p class="hint">Room numbers are fixed once a room is created.</p>
            <?php endif; ?>
        </div>

        <div class="field<?= isset($errors['room_type']) ? ' has-error' : '' ?>">
            <label for="room_type">Room Type</label>
            <select id="room_type" name="room_type" required>
                <?php foreach (ROOM_TYPES as $type): ?>
                    <option value="<?= e($type) ?>"<?= $values['room_type'] === $type ? ' selected' : '' ?>><?= e($type) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['room_type'])): ?><p class="field-error"><?= e($errors['room_type']) ?></p><?php endif; ?>
        </div>

        <div class="field<?= isset($errors['price']) ? ' has-error' : '' ?>">
            <label for="price">Price per Night ($)</label>
            <input type="number" id="price" name="price" step="0.01" min="0.01" inputmode="decimal"
                   value="<?= e($values['price']) ?>" placeholder="e.g. 129.99" required>
            <?php if (isset($errors['price'])): ?><p class="field-error"><?= e($errors['price']) ?></p><?php endif; ?>
        </div>

        <div class="field<?= isset($errors['status']) ? ' has-error' : '' ?>">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <?php foreach (ROOM_STATUSES as $status): ?>
                    <option value="<?= e($status) ?>"<?= $values['status'] === $status ? ' selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['status'])): ?><p class="field-error"><?= e($errors['status']) ?></p><?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <a href="index.php" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i data-lucide="<?= $mode === 'edit' ? 'save' : 'plus' ?>"></i>
            <?= $mode === 'edit' ? 'Save Changes' : 'Add Room' ?>
        </button>
    </div>
</form>
