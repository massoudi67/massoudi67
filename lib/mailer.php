<?php
/**
 * Lightweight trial email sender for free hosting.
 * Tries PHP mail() first, then falls back to direct SMTP via fsockopen.
 *
 * Setup: Replace SMTP_USER and SMTP_PASS below with your Gmail + App Password.
 *        Get App Password from: Google Account > Security > 2-Step Verification > App Passwords
 */

function sendTrialEmail(string $to, string $subject, string $htmlBody, string $plainBody): bool {
    // ── CONFIG ──
    $smtpUser = 'massoudisameh07@gmail.com';      // ← CHANGE THIS
    $smtpPass = 'uxwm rqtr zymr rvac';         // ← CHANGE THIS (16-char app password)
    $fromEmail = $smtpUser;
    $fromName  = 'SAM Traffic Pro';

    // ── OPTION A: Google Apps Script Web App Gateway (Recommended for Free Hosting) ──
    // Bypasses Ezyro SMTP blocks entirely by sending HTTP POST request to your Gmail Apps Script.
    // Set this URL after deploying your script.
    $googleScriptUrl = 'https://script.google.com/macros/s/AKfycbxRRubunSutoa7QSp_KASLklITS3zvs2BaRjVef8dYzyMro3-M7LAnPPXpYcMfD6LYR/exec'; 

    // ── OPTION B: Brevo (Sendinblue) API Gateway ──
    // Bypasses SMTP blocks by sending HTTP POST to Brevo API.
    // Put your Brevo API key here to enable it.
    $brevoApiKey = ''; 

    // --- Executing Option A (Google Apps Script) ---
    if ($googleScriptUrl !== '') {
        $payload = json_encode([
            'to' => $to,
            'subject' => $subject,
            'htmlBody' => $htmlBody,
            'plainBody' => $plainBody,
            'key' => 'SAM_TP_SECRET_KEY'
        ]);
        
        $res = _httpPostSecure($googleScriptUrl, $payload, [
            'Content-Type: application/json'
        ]);

        if ($res !== null) {
            $data = json_decode($res, true);
            if (isset($data['success']) && $data['success'] === true) {
                return true;
            } else {
                error_log("[TrialMail] Google Script returned error: " . ($data['error'] ?? 'unknown'));
            }
        } else {
            error_log("[TrialMail] Google Script HTTP request failed (null response).");
        }
    }

    // --- Executing Option B (Brevo API) ---
    if ($brevoApiKey !== '') {
        $payload = json_encode([
            'sender' => ['name' => $fromName, 'email' => $fromEmail],
            'to' => [['email' => $to]],
            'subject' => $subject,
            'htmlContent' => $htmlBody,
            'textContent' => $plainBody
        ]);

        $res = _httpPostSecure('https://api.brevo.com/v3/smtp/email', $payload, [
            'api-key: ' . $brevoApiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        if ($res !== null) {
            return true;
        } else {
            error_log("[TrialMail] Brevo HTTP request failed (null response).");
        }
    }

    // ── Build SMTP multipart message ──
    $boundary = 'SAMTP_' . md5(uniqid('', true));
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "Reply-To: massoudisameh@gmail.com\r\n";
    $headers .= "X-Mailer: SAMTP/1.0\r\n";

    $body = "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=utf-8\r\n\r\n{$plainBody}\r\n";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=utf-8\r\n\r\n{$htmlBody}\r\n";
    $body .= "--{$boundary}--\r\n";

    // ── Layer 1: PHP mail() ──
    if (function_exists('mail')) {
        $mailHeaders = "MIME-Version: 1.0\r\n";
        $mailHeaders .= "Content-type: text/html; charset=utf-8\r\n";
        $mailHeaders .= "From: {$fromName} <{$fromEmail}>\r\n";
        $mailHeaders .= "Reply-To: massoudisameh@gmail.com\r\n";
        if (@mail($to, $subject, $htmlBody, $mailHeaders)) {
            return true;
        }
    }

    // ── Layer 2: Direct SMTP via fsockopen ──
    $sent = _smtpSend(
        $to, $subject, $body, $headers,
        $fromEmail, $smtpUser, $smtpPass
    );
    if ($sent) return true;

    // ── Layer 3: Log failure ──
    error_log("[TrialMail] FAILED to send OTP to {$to}. Check SMTP config in lib/mailer.php");
    return false;
}

/**
 * Robust HTTP POST helper that bypasses SSL certificate issues and falls back
 * to file_get_contents if cURL is disabled/restricted.
 */
function _httpPostSecure(string $url, string $payload, array $headers): ?string {
    // 1. Try cURL if available
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL verification issues on free host
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($res !== false && $httpCode >= 200 && $httpCode < 400) {
            return $res;
        }
    }

    // 2. Try file_get_contents as a fallback
    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers) . "\r\n",
            'content' => $payload,
            'timeout' => 12,
            'follow_location' => 1,
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ];
    $context = stream_context_create($opts);
    $res = @file_get_contents($url, false, $context);
    return $res !== false ? $res : null;
}

/**
 * Internal: direct SMTP submission to Gmail (or any host).
 * Works on most free hosting that allows fsockopen on port 587.
 */
function _smtpSend(
    string $to,
    string $subject,
    string $body,
    string $headers,
    string $from,
    string $user,
    string $pass
): bool {
    $host = 'smtp.gmail.com';
    $port = 587;

    // If user hasn't configured credentials, skip SMTP
    if ($user === 'YOUR_GMAIL@gmail.com' || $pass === 'YOUR_APP_PASSWORD') {
        error_log("[TrialMail] SMTP credentials not configured. Skipping SMTP layer.");
        return false;
    }

    $sock = @fsockopen($host, $port, $errno, $errstr, 10);
    if (!$sock) {
        error_log("[TrialMail] fsockopen to {$host}:{$port} failed: {$errstr} ({$errno})");
        return false;
    }

    $talk = function ($cmd, $expectCode = null) use ($sock) {
        if ($cmd !== null) {
            fwrite($sock, $cmd . "\r\n");
        }
        $resp = '';
        while ($line = fgets($sock, 512)) {
            $resp .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        if ($expectCode !== null && strpos($resp, (string)$expectCode) !== 0) {
            error_log("[TrialMail] SMTP unexpected response: " . trim($resp));
        }
        return $resp;
    };

    $talk(null, 220);                       // greeting
    $talk('EHLO ' . gethostname(), 250);    // hello
    $talk('STARTTLS', 220);                  // TLS

    stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

    $talk('EHLO ' . gethostname(), 250);
    $talk('AUTH LOGIN', 334);
    $talk(base64_encode($user), 334);
    $talk(base64_encode($pass), 235);
    $talk("MAIL FROM:<{$from}>", 250);
    $talk("RCPT TO:<{$to}>", 250);
    $talk('DATA', 354);

    $message  = "To: {$to}\r\n";
    $message .= "Subject: {$subject}\r\n";
    $message .= $headers;
    $message .= "\r\n" . $body;
    $message .= "\r\n.\r\n";

    fwrite($sock, $message);
    $resp = fgets($sock, 512);
    $talk('QUIT', 221);
    fclose($sock);

    $ok = strpos($resp, '250') !== false || strpos($resp, '2.0.0') !== false;
    if (!$ok) {
        error_log("[TrialMail] SMTP DATA failed: " . trim($resp));
    }
    return $ok;
}
