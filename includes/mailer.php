<?php
/**
 * Mailer: sends via SMTP when configured, otherwise PHP mail().
 */

declare(strict_types=1);

/**
 * Send an email. Returns a [success, error] tuple.
 *
 * @return array{0: bool, 1: string}
 */
function send_mail(string $to, string $subject, string $body, ?string $replyTo = null): array
{
    // Basic header-injection protection.
    if (has_header_injection($to) || has_header_injection($subject)
        || ($replyTo !== null && has_header_injection($replyTo))) {
        return [false, 'Invalid email header value.'];
    }

    $from     = (string) config('mail.from');
    $fromName = (string) config('mail.from_name');

    if ((config('mail.method') ?: 'mail') === 'smtp') {
        return smtp_send($to, $subject, $body, $replyTo, $from, $fromName);
    }

    return mail_send($to, $subject, $body, $replyTo, $from, $fromName);
}

function mail_send(string $to, string $subject, string $body, ?string $replyTo, string $from, string $fromName): array
{
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . encode_name($fromName) . ' <' . $from . '>',
    ];
    if ($replyTo) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    $ok = @mail($to, encode_subject($subject), $body, implode("\r\n", $headers));
    return $ok ? [true, ''] : [false, 'PHP mail() failed.'];
}

// ---------------------------------------------------------------------
// Minimal SMTP client (no dependencies)
// ---------------------------------------------------------------------
function smtp_send(string $to, string $subject, string $body, ?string $replyTo, string $from, string $fromName): array
{
    $host   = (string) config('mail.host');
    $port   = (int) config('mail.port');
    $user   = (string) config('mail.user');
    $pass   = (string) config('mail.pass');
    $secure = (string) config('mail.secure');

    if ($host === '') {
        return [false, 'SMTP host not configured.'];
    }

    $transport = $secure === 'ssl' ? 'ssl://' : '';
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
    $sock = @stream_socket_client(
        $transport . $host . ':' . $port,
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT,
        $ctx
    );
    if (!$sock) {
        return [false, "SMTP connect failed: $errstr ($errno)"];
    }
    stream_set_timeout($sock, 15);

    try {
        smtp_expect($sock, [220]);
        $ehloHost = $_SERVER['SERVER_NAME'] ?? 'localhost';
        smtp_cmd($sock, 'EHLO ' . $ehloHost, [250]);

        if ($secure === 'tls') {
            smtp_cmd($sock, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Failed to enable TLS.');
            }
            smtp_cmd($sock, 'EHLO ' . $ehloHost, [250]);
        }

        if ($user !== '') {
            smtp_cmd($sock, 'AUTH LOGIN', [334]);
            smtp_cmd($sock, base64_encode($user), [334]);
            smtp_cmd($sock, base64_encode($pass), [235]);
        }

        smtp_cmd($sock, 'MAIL FROM:<' . $from . '>', [250]);
        smtp_cmd($sock, 'RCPT TO:<' . $to . '>', [250, 251]);
        smtp_cmd($sock, 'DATA', [354]);

        $data  = 'Date: ' . date('r') . "\r\n";
        $data .= 'From: ' . encode_name($fromName) . ' <' . $from . ">\r\n";
        $data .= 'To: <' . $to . ">\r\n";
        if ($replyTo) {
            $data .= 'Reply-To: ' . $replyTo . "\r\n";
        }
        $data .= 'Subject: ' . encode_subject($subject) . "\r\n";
        $data .= "MIME-Version: 1.0\r\n";
        $data .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $data .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $data .= str_replace("\n", "\r\n", str_replace("\r\n", "\n", $body));
        $data .= "\r\n.\r\n";

        fwrite($sock, $data);
        smtp_expect($sock, [250]);
        smtp_cmd($sock, 'QUIT', [221]);
        fclose($sock);
    } catch (Throwable $ex) {
        fclose($sock);
        return [false, 'SMTP error: ' . $ex->getMessage()];
    }

    return [true, ''];
}

function smtp_cmd($sock, string $command, array $expect): string
{
    fwrite($sock, $command . "\r\n");
    return smtp_expect($sock, $expect);
}

function smtp_expect($sock, array $codes): string
{
    $response = '';
    while (($line = fgets($sock, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    $code = (int) substr($response, 0, 3);
    if (!in_array($code, $codes, true)) {
        throw new RuntimeException(trim($response) ?: 'No response from SMTP server.');
    }
    return $response;
}

function encode_subject(string $subject): string
{
    return mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n");
}

function encode_name(string $name): string
{
    $name = str_replace(['"', "\r", "\n"], '', $name);
    return '"' . addcslashes($name, '\\') . '"';
}
