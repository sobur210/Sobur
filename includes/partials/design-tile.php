<?php
/**
 * Design tile. Swap the CSS sample for a real image by setting 'image'
 * to a filename inside assets/img/ — everything else stays the same.
 *
 * @var array $tile { label, caption, visual, image }
 */
$tile = $tile ?? ['label' => '', 'caption' => '', 'visual' => 'logos', 'image' => null];
?>
<div class="design-tile">
    <div class="tile-visual">
        <?php if (!empty($tile['image'])): ?>
            <img src="<?= e(asset('img/' . $tile['image'])) ?>" alt="<?= e($tile['alt'] ?? ($tile['label'] . ' sample')) ?>" loading="lazy">
        <?php elseif ($tile['visual'] === 'branding'): ?>
            <div class="tv-branding" aria-hidden="true">
                <div class="tv-swatches"><i></i><i></i><i></i><i></i></div>
                <span class="tv-type">Aa</span>
            </div>
        <?php elseif ($tile['visual'] === 'social'): ?>
            <div class="tv-social" aria-hidden="true"><i></i><i></i></div>
        <?php elseif ($tile['visual'] === 'posters'): ?>
            <div class="tv-posters" aria-hidden="true"><span class="tv-poster">S</span><span class="tv-poster">B</span><span class="tv-poster">R</span></div>
        <?php else: ?>
            <div class="tv-logos" aria-hidden="true">
                <span class="tv-mono">S</span>
                <span class="tv-word"></span>
            </div>
        <?php endif; ?>
    </div>
    <h3 class="tile-label"><?= e($tile['label']) ?></h3>
    <p class="tile-caption"><?= e($tile['caption']) ?></p>
</div>
