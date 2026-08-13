<?php
$defaultTitle = isset($title) ? $title : 'Page';
$defaultSubtitle = isset($subtitle) ? $subtitle : '';
$defaultIcon = isset($icon) ? $icon : 'bi-grid-fill';
$showBack = isset($show_back) ? (bool) $show_back : true;
$backUrl = isset($back_url) ? $back_url : site_url('dashboard');
$backLabel = isset($back_label) ? $back_label : 'Dashboard';
$primaryUrl = isset($primary_url) ? $primary_url : null;
$primaryLabel = isset($primary_label) ? $primary_label : '';
$primaryIcon = isset($primary_icon) ? $primary_icon : '';
$primaryClass = isset($primary_class) ? $primary_class : 'btn btn-primary';
?>

<div class="page-header">
    <div class="page-header__content">
        <div class="page-header__icon">
            <i class="<?= htmlspecialchars($defaultIcon, ENT_QUOTES, 'UTF-8'); ?>"></i>
        </div>

        <div class="page-header__text">
            <h2><?= htmlspecialchars($defaultTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
            <?php if (!empty($defaultSubtitle)) : ?>
                <p><?= htmlspecialchars($defaultSubtitle, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="page-header__actions">
        <?php if ($showBack) : ?>
            <a href="<?= $backUrl; ?>" class="btn btn-back">
                <i class="bi bi-arrow-left"></i>
                <span><?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8'); ?></span>
            </a>
        <?php endif; ?>

        <?php if (!empty($primaryUrl) && !empty($primaryLabel)) : ?>
            <a href="<?= $primaryUrl; ?>" class="<?= htmlspecialchars($primaryClass, ENT_QUOTES, 'UTF-8'); ?>">
                <?php if (!empty($primaryIcon)) : ?>
                    <i class="<?= htmlspecialchars($primaryIcon, ENT_QUOTES, 'UTF-8'); ?>"></i>
                <?php endif; ?>
                <span><?= htmlspecialchars($primaryLabel, ENT_QUOTES, 'UTF-8'); ?></span>
            </a>
        <?php endif; ?>
    </div>
</div>
