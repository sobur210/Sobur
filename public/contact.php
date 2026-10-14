<?php
/**
 * Contact form handler.
 * Saves to the messages table and emails Sobur.
 * Responds with JSON for fetch() and redirects with a flash for the no-JS fallback.
 */
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/mailer.php';
app_boot();
secure_session_start();

$home = base_url('');

function fail(string $message): void
{
    global $home;
    if (wants_json()) {
        json_response(['ok' => false, 'message' => $message], 422);
    }
    flash_set('contact_error', $message);
    redirect($home . '#contact');
}

if (!is_post()) {
    redirect($home . '#contact');
}

// ---- Honeypot: silently accept for bots, never store ----
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    if (wants_json()) {
        json_response(['ok' => true, 'message' => 'Thanks — your message has been sent.']);
    }
    flash_set('contact_success', 'Thanks — your message has been sent.');
    redirect($home . '#contact');
}

// ---- CSRF ----
if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    fail('Your session expired. Please reload the page and try again.');
}

$name    = trim((string) ($_POST['name'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

// Keep the values for the no-JS fallback so nothing is retyped.
$_SESSION['contact_old'] = ['name' => $name, 'email' => $email, 'message' => $message];

// ---- Validation ----
$errors = [];
if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
    $errors[] = 'Please enter your name.';
}
if (!valid_email($email) || mb_strlen($email) > 190) {
    $errors[] = 'Please enter a valid email address.';
}
if (mb_strlen($message) < 10 || mb_strlen($message) > 5000) {
    $errors[] = 'Please write a slightly longer message.';
}
if (has_header_injection($name) || has_header_injection($email)) {
    $errors[] = 'Invalid characters in your details.';
}
if ($errors) {
    fail(implode(' ', $errors));
}

// ---- Rate limiting (per IP) ----
$ip = client_ip();
if (!contact_rate_ok($ip)) {
    fail('Too many messages from this connection. Please try again a little later.');
}

// ---- Store ----
try {
    $stmt = db()->prepare(
        'INSERT INTO messages (name, email, message, ip_address, user_agent)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, $message, $ip, user_agent()]);
} catch (Throwable $ex) {
    error_log('Contact insert failed: ' . $ex->getMessage());
    fail('Sorry, something went wrong saving your message. Please email me directly.');
}

// ---- Email (best effort; the message is already saved) ----
$to      = (string) config('site.email');
$subject = 'New portfolio enquiry from ' . $name;
$body    = "You received a new message from your portfolio site.\n\n"
         . "Name:    $name\n"
         . "Email:   $email\n"
         . "IP:      $ip\n"
         . "Time:    " . gmdate('Y-m-d H:i:s') . " UTC\n\n"
         . "Message:\n$message\n";

[$sent, $smtpError] = send_mail($to, $subject, $body, $email);
if (!$sent) {
    error_log('Contact email failed: ' . $smtpError);
}

unset($_SESSION['contact_old']);

$okMessage = 'Thanks — your message has been sent. I will get back to you soon.';
if (wants_json()) {
    json_response(['ok' => true, 'message' => $okMessage]);
}
flash_set('contact_success', $okMessage);
redirect($home . '#contact');
