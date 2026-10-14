<?php
/** Admin — add / edit a project. */
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();

$id = (int) ($_GET['id'] ?? 0);
$project = [
    'id' => 0, 'slug' => '', 'title' => '', 'category' => '', 'tagline' => '', 'description' => '',
    'tags' => '', 'brand_primary' => '#0A7F52', 'brand_secondary' => '#FFFFFF', 'brand_surface' => '#F3FAF6',
    'live_url' => '', 'mockup_type' => 'isobash', 'is_featured' => 0, 'sort_order' => 0,
];
$errors = [];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash_set('admin_err', 'Project not found.');
        redirect(base_url('admin/projects.php'));
    }
    $project = $found;
}

if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $fields = ['title', 'category', 'tagline', 'description', 'tags', 'live_url', 'mockup_type'];
        foreach ($fields as $f) {
            $project[$f] = trim((string) ($_POST[$f] ?? ''));
        }
        foreach (['brand_primary', 'brand_secondary', 'brand_surface'] as $f) {
            $val = trim((string) ($_POST[$f] ?? ''));
            $project[$f] = preg_match('/^#[0-9A-Fa-f]{6}$/', $val) ? strtoupper($val) : '#000000';
        }
        $project['slug'] = trim((string) ($_POST['slug'] ?? ''));
        $project['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
        $project['sort_order'] = (int) ($_POST['sort_order'] ?? 0);

        if ($project['title'] === '') {
            $errors[] = 'Title is required.';
        }
        if ($project['slug'] === '') {
            $project['slug'] = slugify($project['title']);
        } else {
            $project['slug'] = slugify($project['slug']);
        }
        if (!is_mockup_supported($project['mockup_type'])) {
            $project['mockup_type'] = 'isobash';
        }
        if ($project['live_url'] !== '' && !filter_var($project['live_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Live URL must be a full URL (https://…).';
        }

        if (!$errors) {
            try {
                if ($id > 0) {
                    $sql = 'UPDATE projects SET slug=?, title=?, category=?, tagline=?, description=?, tags=?,
                            brand_primary=?, brand_secondary=?, brand_surface=?, live_url=?, mockup_type=?,
                            is_featured=?, sort_order=? WHERE id=?';
                    db()->prepare($sql)->execute([
                        $project['slug'], $project['title'], $project['category'], $project['tagline'],
                        $project['description'], $project['tags'], $project['brand_primary'],
                        $project['brand_secondary'], $project['brand_surface'], $project['live_url'],
                        $project['mockup_type'], $project['is_featured'], $project['sort_order'], $id,
                    ]);
                } else {
                    $sql = 'INSERT INTO projects (slug, title, category, tagline, description, tags,
                            brand_primary, brand_secondary, brand_surface, live_url, mockup_type, is_featured, sort_order)
                            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)';
                    db()->prepare($sql)->execute([
                        $project['slug'], $project['title'], $project['category'], $project['tagline'],
                        $project['description'], $project['tags'], $project['brand_primary'],
                        $project['brand_secondary'], $project['brand_surface'], $project['live_url'],
                        $project['mockup_type'], $project['is_featured'], $project['sort_order'],
                    ]);
                }
                if ($project['is_featured'] === 1 && $id === 0) {
                    // fine: multiple featured allowed but homepage uses the first
                }
                flash_set('admin_ok', $id > 0 ? 'Project updated.' : 'Project added.');
                redirect(base_url('admin/projects.php'));
            } catch (PDOException $ex) {
                $errors[] = str_contains($ex->getMessage(), 'Duplicate')
                    ? 'That slug is already in use.'
                    : 'Could not save the project.';
                error_log('Project save failed: ' . $ex->getMessage());
            }
        }
    }
}

$adminTitle = $id > 0 ? 'Edit project' : 'Add project';
$activeNav = 'projects';
require __DIR__ . '/includes/admin-header.php';
?>
<div class="page-head">
    <h1 class="page-title"><?= $id > 0 ? 'Edit project' : 'Add project' ?></h1>
    <a class="btn btn--ghost btn--sm" href="<?= e(base_url('admin/projects.php')) ?>">&larr; Back</a>
</div>

<?php if ($errors): ?>
    <div class="notice notice--err" role="alert">
        <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="admin-form admin-form--wide" action="<?= e(base_url('admin/project-edit.php' . ($id > 0 ? '?id=' . $id : ''))) ?>">
    <?= csrf_field() ?>

    <div class="form-grid">
        <div class="field">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" required maxlength="160" value="<?= e($project['title']) ?>">
        </div>
        <div class="field">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" maxlength="160" value="<?= e($project['slug']) ?>" placeholder="auto from title">
        </div>
        <div class="field">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" maxlength="80" value="<?= e($project['category']) ?>">
        </div>
        <div class="field">
            <label for="mockup_type">Mockup</label>
            <select id="mockup_type" name="mockup_type">
                <?php foreach (['isobash' => 'ISOBASH chat', 'abagaf' => 'Abagaf storefront', 'prime-estates' => 'Prime Estates listings'] as $val => $label): ?>
                    <option value="<?= e($val) ?>" <?= $project['mockup_type'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="field">
        <label for="tagline">Tagline</label>
        <input type="text" id="tagline" name="tagline" maxlength="255" value="<?= e($project['tagline']) ?>">
    </div>

    <div class="field">
        <label for="description">Description</label>
        <textarea id="description" name="description" maxlength="2000"><?= e($project['description']) ?></textarea>
    </div>

    <div class="field">
        <label for="tags">Tags (comma separated)</label>
        <input type="text" id="tags" name="tags" maxlength="255" value="<?= e($project['tags']) ?>" placeholder="Design, Development, Laravel">
    </div>

    <div class="field">
        <label for="live_url">Live URL</label>
        <input type="url" id="live_url" name="live_url" maxlength="255" value="<?= e($project['live_url']) ?>" placeholder="https://">
    </div>

    <div class="form-grid form-grid--3">
        <div class="field">
            <label for="brand_primary">Brand primary</label>
            <input type="color" id="brand_primary" name="brand_primary" value="<?= e($project['brand_primary']) ?>">
        </div>
        <div class="field">
            <label for="brand_secondary">Brand secondary</label>
            <input type="color" id="brand_secondary" name="brand_secondary" value="<?= e($project['brand_secondary']) ?>">
        </div>
        <div class="field">
            <label for="brand_surface">Brand surface</label>
            <input type="color" id="brand_surface" name="brand_surface" value="<?= e($project['brand_surface']) ?>">
        </div>
    </div>

    <div class="form-grid">
        <div class="field">
            <label for="sort_order">Sort order</label>
            <input type="number" id="sort_order" name="sort_order" value="<?= (int) $project['sort_order'] ?>">
        </div>
        <div class="field field--check">
            <label class="check-label">
                <input type="checkbox" name="is_featured" value="1" <?= (int) $project['is_featured'] === 1 ? 'checked' : '' ?>>
                Featured on homepage
            </label>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn--primary" type="submit"><?= $id > 0 ? 'Save changes' : 'Add project' ?></button>
        <a class="btn btn--ghost" href="<?= e(base_url('admin/projects.php')) ?>">Cancel</a>
    </div>
</form>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
