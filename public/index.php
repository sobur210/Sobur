<?php
/**
 * Sobur — single page portfolio.
 * Projects are read from MySQL so new work never means editing markup.
 */
require_once __DIR__ . '/../includes/helpers.php';
app_boot();
secure_session_start();

$pageTitle = 'Sobur — Graphic designer & software engineer';
$pageDescription = 'Brand identity, visuals and full web products from one person, from the first sketch to the live site.';

try {
    $projects = get_projects();
} catch (Throwable $ex) {
    error_log('Failed to load projects: ' . $ex->getMessage());
    $projects = [];
}

$featured = null;
$others = [];
foreach ($projects as $project) {
    if ($featured === null && (int) $project['is_featured'] === 1) {
        $featured = $project;
    } else {
        $others[] = $project;
    }
}
if ($featured === null && $projects) {
    $featured = array_shift($others);
}

$designTiles = [
    ['label' => 'Logos',         'caption' => 'Marks and monograms',        'visual' => 'logos',   'image' => null],
    ['label' => 'Branding',      'caption' => 'Identity systems',           'visual' => 'branding','image' => null],
    ['label' => 'Social posts',  'caption' => 'Campaign-ready visuals',     'visual' => 'social',  'image' => null],
    ['label' => 'Posters',       'caption' => 'Print and large format',     'visual' => 'posters', 'image' => null],
];

require __DIR__ . '/../includes/header.php';
?>
<main id="main">
    <span id="top"></span>

    <!-- ==================== HERO ==================== -->
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero-decor" aria-hidden="true">
            <span class="decor-1"></span>
            <span class="decor-3"></span>
            <span class="decor-dot"></span>
        </div>
        <div class="container hero-grid">
            <div class="hero-inner">
                <span class="eyebrow">Graphic designer &amp; software engineer</span>
                <h1 id="hero-title">I design it. I build it.</h1>
                <p class="hero-sub">Brand identity, visuals and full web products from one person, from the first sketch to the live site.</p>
                <div class="hero-actions">
                    <a class="btn btn--primary" href="#work">See my web work</a>
                    <a class="btn btn--secondary" href="#design">See my design work</a>
                </div>
            </div>
            <div class="hero-art">
                <img src="<?= e(asset('img/hero-visual.svg')) ?>" width="640" height="640"
                     alt="Abstract geometric composition in emerald, mint and blue"
                     fetchpriority="high" decoding="async">
                <div class="hero-badge" aria-hidden="true">
                    <strong>1</strong>
                    <span>person<br>design + build</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== WORK ==================== -->
    <section class="section" id="work" aria-labelledby="work-title">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Selected work</span>
                <h2 id="work-title">Web products, designed and built end to end.</h2>
                <p class="lede">Each project below was designed and developed by me, from interface to database.</p>
            </div>

            <div class="work-list">
                <?php if ($featured): ?>
                    <?php $project = $featured; require __DIR__ . '/../includes/partials/project-card-featured.php'; ?>
                <?php endif; ?>

                <?php if ($others): ?>
                    <div class="work-grid">
                        <?php foreach ($others as $project): ?>
                            <?php require __DIR__ . '/../includes/partials/project-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!$projects): ?>
                    <p class="lede">No projects yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ==================== DESIGN ==================== -->
    <section class="section section--alt" id="design" aria-labelledby="design-title">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Design work</span>
                <h2 id="design-title">The visual side.</h2>
                <p class="lede">Logos, identity systems, social campaigns and print.</p>
            </div>

            <div class="design-grid">
                <?php foreach ($designTiles as $tile): ?>
                    <?php require __DIR__ . '/../includes/partials/design-tile.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ==================== ABOUT ==================== -->
    <section class="section" id="about" aria-labelledby="about-title">
        <div class="container about-grid">
            <div class="reveal">
                <span class="eyebrow">About</span>
                <h2 id="about-title">One person. Both sides of the work.</h2>
            </div>
            <div class="about-body reveal">
                <p>Designing and building together means what gets designed is what gets built. No hand-off gaps, no lost details, no translating intentions between teams.</p>
                <p>I started with brand identity and moved into software because clients kept asking for the whole thing. Today I take a product from the first sketch to the live, working site.</p>
                <p class="about-sign">— Sobur</p>
            </div>
        </div>
    </section>

    <!-- ==================== CONTACT ==================== -->
    <section class="contact-wrap" id="contact" aria-labelledby="contact-title">
        <div class="container">
            <div class="contact-block reveal">
                <div class="contact-intro">
                    <span class="eyebrow" style="color: var(--mint);">Contact</span>
                    <h2 id="contact-title">Have a project in mind?</h2>
                    <p>Tell me what you are building and I will get back to you. Design, development, or both.</p>

                    <div class="contact-aside">
                        <a href="mailto:<?= e(config('site.email')) ?>">
                            <span aria-hidden="true">&#9993;</span> <?= e(config('site.email')) ?>
                        </a>
                        <?php
                        $wa = (string) config('site.whatsapp');
                        if ($wa !== ''):
                        ?>
                            <a href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer">
                                <span aria-hidden="true">&#9742;</span> Chat on WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <form class="contact-form" method="post" action="<?= e(base_url('contact.php')) ?>" data-contact-form novalidate>
                    <div class="form-status" data-form-status role="status" aria-live="polite">
                        <?php
                        if ($msg = flash_get('contact_error')) {
                            echo '<span class="form-status form-status--err" style="display:block;">' . e($msg) . '</span>';
                        } elseif ($msg = flash_get('contact_success')) {
                            echo '<span class="form-status form-status--ok" style="display:block;">' . e($msg) . '</span>';
                        }
                        ?>
                    </div>

                    <?= csrf_field() ?>

                    <div class="field">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" autocomplete="name" required maxlength="120"
                               value="<?= e((string)($_SESSION['contact_old']['name'] ?? '')) ?>">
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" autocomplete="email" required maxlength="190"
                               value="<?= e((string)($_SESSION['contact_old']['email'] ?? '')) ?>">
                    </div>

                    <div class="field">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required maxlength="5000"><?= e((string)($_SESSION['contact_old']['message'] ?? '')) ?></textarea>
                    </div>

                    <?php unset($_SESSION['contact_old']); ?>

                    <!-- Honeypot: hidden from people, tempting to bots -->
                    <div class="hp-field" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <button class="btn btn--secondary" type="submit">Send message</button>
                </form>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
