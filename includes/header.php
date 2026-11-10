<?php
/**
 * includes/header.php - opens the HTML page, loads the sidebar and top bar.
 *
 * Before including this file a page may set:
 *   $pageTitle  - text for the <title> tag
 *   $activeNav  - 'dashboard' | 'rooms' | 'create' (highlights the sidebar link)
 */
$pageTitle = $pageTitle ?? 'Hotel Room Management';
$activeNav = $activeNav ?? '';
$flash     = pull_flash();   // one-time success/error message from the previous request
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> · Grand Meridian</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <div class="backdrop" id="backdrop"></div>

    <div class="main">
        <header class="topbar">
            <button type="button" class="icon-btn menu-btn" id="menuBtn" aria-label="Open menu">
                <i data-lucide="menu"></i>
            </button>
            <div class="topbar-date">
                <i data-lucide="calendar-days"></i>
                <span><?= e(date('l, j F Y')) ?></span>
            </div>
            <div class="admin">
                <span class="avatar">A</span>
                <div class="admin-meta">
                    <strong>Admin</strong>
                    <small>Front Office Manager</small>
                </div>
            </div>
        </header>

        <main class="content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>" role="status" data-autodismiss>
                    <i data-lucide="<?= $flash['type'] === 'success' ? 'circle-check' : 'circle-alert' ?>"></i>
                    <span><?= e($flash['message']) ?></span>
                </div>
            <?php endif; ?>
