<?php
header('Content-Type: application/json; charset=UTF-8');

use PHPMailer\PHPMailer\PHPMailer;

function cleanText(?string $value): string
{
    return trim(strip_tags((string) $value));
}

function outputJson(bool $success, int $statusCode, string $message, array $errors = []): void
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'errors' => $errors,
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function getAppSetting(string $name, string $default = ''): string
{
    $value = getenv($name);
    if ($value !== false && $value !== '') {
        return (string) $value;
    }

    if (isset($_ENV[$name]) && $_ENV[$name] !== '') {
        return (string) $_ENV[$name];
    }

    if (isset($_SERVER[$name]) && $_SERVER[$name] !== '') {
        return (string) $_SERVER[$name];
    }

    $dotenvFile = __DIR__ . '/.env';
    if (is_file($dotenvFile)) {
        $lines = @file($dotenvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (is_array($lines)) {
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '#') === 0) {
                    continue;
                }

                $parts = explode('=', $line, 2);
                if (count($parts) === 2 && trim($parts[0]) === $name) {
                    return trim($parts[1], " \t\n\r\0\x0B\"'");
                }
            }
        }
    }

    return $default;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    outputJson(false, 405, 'Only POST requests are allowed.');
}

$honeypot = cleanText($_POST['website'] ?? '');
if ($honeypot !== '') {
    outputJson(false, 400, 'Invalid request.');
}

$name = cleanText($_POST['name'] ?? $_POST['Full Name'] ?? '');
$email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_SANITIZE_EMAIL);
$phone = cleanText($_POST['phone'] ?? $_POST['Phone Number'] ?? '');
$service = cleanText($_POST['service_type'] ?? $_POST['Service Required'] ?? '');
$subject = cleanText($_POST['subject'] ?? $_POST['Subject'] ?? 'Airsell Cargo Contact Request');
$subject = preg_replace('/[\r\n]+/', ' ', $subject);
$details = cleanText($_POST['details'] ?? $_POST['Message'] ?? $_POST['message'] ?? '');

$errors = [];
if (mb_strlen($name) < 2) {
    $errors[] = 'Name is required.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email is required.';
}
if (mb_strlen($phone) < 5) {
    $errors[] = 'A valid phone number is required.';
}
if ($service !== '' && mb_strlen($service) < 2) {
    $errors[] = 'Service type is invalid.';
}
if (mb_strlen($details) < 10) {
    $errors[] = 'Cargo details are required.';
}

if ($errors !== []) {
    outputJson(false, 422, 'Please fix the form errors.', $errors);
}

$autoloadFile = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoloadFile)) {
    error_log('Contact form mail failed: Composer dependencies are not installed.');
    outputJson(false, 500, 'Unable to send your inquiry right now. Please email us directly at sales@airsellcargo.com');
}
require_once $autoloadFile;

if (!class_exists(PHPMailer::class)) {
    error_log('Contact form mail failed: PHPMailer is not available.');
    outputJson(false, 500, 'Unable to send your inquiry right now. Please email us directly at sales@airsellcargo.com');
}

$emailSubject = 'Airsell Portal Inquiry: ' . ($subject !== '' ? $subject : 'General Inquiry');
$body = "New message from Airsell Cargo Website Form:\n\n";
$body .= "Customer Name: {$name}\n";
$body .= "Customer Email: {$email}\n";
$body .= "Phone Number: {$phone}\n";
$body .= "Service Required: {$service}\n";
$body .= "Subject: {$subject}\n\n";
$body .= "Cargo Details:\n{$details}\n\n";
$body .= "--- End of Message ---";

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = getAppSetting('AIRSELL_SMTP_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = getAppSetting('AIRSELL_SMTP_USER');
    $mail->Password = getAppSetting('AIRSELL_SMTP_PASS');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int) getAppSetting('AIRSELL_SMTP_PORT', '587');
    $mail->CharSet = PHPMailer::CHARSET_UTF8;
    $mail->setFrom('info@airsellcargo.com', 'Airsell Cargo');
    $mail->addAddress('info@airsellcargo.com');
    $mail->addReplyTo($email, $name);
    $mail->Subject = $emailSubject;
    $mail->Body = $body;
    $mail->send();
} catch (Throwable $exception) {
    error_log('Contact form mail failed: ' . $exception->getMessage());
    outputJson(false, 500, 'Unable to send your inquiry right now. Please email us directly at sales@airsellcargo.com');
}

$logFile = __DIR__ . '/contact_submissions.log';
$logLine = date('c') . ' | ' . $name . ' | ' . $email . ' | ' . $service . PHP_EOL;
@file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);

outputJson(true, 200, 'Thank you! Your inquiry has been sent to the Airsell Cargo team.');
