<?php
/**
 * Featured project card (full width, two columns).
 * @var array $project
 */
$tags = split_tags((string) $project['tags']);
$href = $project['live_url'] !== '' ? $project['live_url'] : '#work'; // TODO: real live URL per project
?>
<a class="project-card project-card--featured reveal"
   href="<?= e($href) ?>"
   target="_blank" rel="noopener noreferrer"
   style="--brand:<?= e($project['brand_primary']) ?>; --brand-2:<?= e($project['brand_secondary']) ?>; --brand-surface:<?= e($project['brand_surface']) ?>;"
   aria-label="<?= e($project['title']) ?> — <?= e($project['category']) ?>, opens the live site in a new tab">

    <div class="project-media">
        <?php if (is_mockup_supported((string) $project['mockup_type'])): ?>
            <?php require __DIR__ . '/mockups/' . $project['mockup_type'] . '.php'; ?>
        <?php endif; ?>
    </div>

    <div class="project-body">
        <span class="project-category"><?= e($project['category']) ?></span>
        <h3 class="project-title"><?= e($project['title']) ?></h3>
        <p class="project-tagline"><?= e($project['tagline']) ?></p>
        <p class="project-desc"><?= e($project['description']) ?></p>

        <?php if ($tags): ?>
            <ul class="project-tags" role="list">
                <?php foreach ($tags as $tag): ?>
                    <li class="project-tag"><?= e($tag) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <span class="project-link">View live site <span class="arrow" aria-hidden="true">&rarr;</span></span>
    </div>
</a>
