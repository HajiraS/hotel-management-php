<?php
/**
 * includes/sidebar.php - dark navy navigation menu.
 * Uses $activeNav (set by each page) to highlight the current link.
 */
$navItems = [
    'dashboard' => ['index.php',            'layout-dashboard', 'Dashboard'],
    'rooms'     => ['index.php?view=rooms', 'bed-double',       'All Rooms'],
    'create'    => ['create.php',           'circle-plus',      'Add New Room'],
];
?>
<aside class="sidebar" id="sidebar">
    <div class="brand">
        <span class="brand-mark"><i data-lucide="building-2"></i></span>
        <div>
            <strong>Grand Meridian</strong>
            <small>Admin Console</small>
        </div>
    </div>

    <nav class="nav" aria-label="Main navigation">
        <span class="nav-label">Menu</span>
        <?php foreach ($navItems as $key => [$href, $icon, $label]): ?>
            <a class="nav-link<?= $activeNav === $key ? ' active' : '' ?>" href="<?= e($href) ?>"
               <?= $activeNav === $key ? 'aria-current="page"' : '' ?>>
                <i data-lucide="<?= e($icon) ?>"></i>
                <span><?= e($label) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-foot">
        <i data-lucide="shield-check"></i>
        <span>Secure session active</span>
    </div>
</aside>
