<?php
require_once __DIR__ . '/../includes/helpers.php';
http_response_code(404);
$pageTitle = 'Page not found — ' . config('site.name');
$pageDescription = 'The page you were looking for does not exist.';
require __DIR__ . '/../includes/header.php';
?>
<main id="main" class="section">
    <div class="container" style="text-align:center; max-width:640px;">
        <span class="eyebrow">404</span>
        <h1 style="font-size:clamp(2.4rem,7vw,4rem);">This page is not here.</h1>
        <p class="lede">The link may be broken or the page may have moved.</p>
        <p style="margin-top:24px;"><a class="btn btn--primary" href="<?= e(base_url('')) ?>">Back to the homepage</a></p>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
