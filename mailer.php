<?php
require_once __DIR__ . '/mail_config.php';
require_once __DIR__ . '/vendor/phpmailer/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendMail(string $toEmail, string $toName, string $subject, string $htmlBody): array {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);
        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

function sendWelcomeEmail(string $toEmail, string $toName, string $studentId, string $tempPassword): array {
    $loginUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/qr-event-attendance/student_login.php';
    $subject  = 'Your ZDSPGC QR Attendance Account';
    $html = "
    <div style='font-family:sans-serif;max-width:520px;margin:0 auto;background:#f9fafb;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb'>
      <div style='background:#1b4332;padding:28px 32px;text-align:center'>
        <h1 style='color:#fff;margin:0;font-size:1.3rem'>ZDSPGC QR Attendance</h1>
        <p style='color:#a7f3d0;margin:6px 0 0;font-size:.88rem'>Student Account Created</p>
      </div>
      <div style='padding:32px'>
        <p style='margin:0 0 16px;color:#111827'>Hi <strong>" . htmlspecialchars($toName) . "</strong>,</p>
        <p style='color:#374151;font-size:.9rem;line-height:1.6;margin:0 0 20px'>
          Your student account has been created. <strong>Please change your password after first login.</strong>
        </p>
        <div style='background:#fff;border:1px solid #d1fae5;border-radius:8px;padding:20px;margin-bottom:24px'>
          <table style='width:100%;border-collapse:collapse;font-size:.9rem'>
            <tr><td style='color:#6b7280;padding:6px 0;width:40%'>Student ID</td>
                <td style='font-weight:700;color:#1b4332;font-family:monospace'>" . htmlspecialchars($studentId) . "</td></tr>
            <tr><td style='color:#6b7280;padding:6px 0'>Temporary Password</td>
                <td style='font-weight:700;color:#dc2626;font-family:monospace'>" . htmlspecialchars($tempPassword) . "</td></tr>
          </table>
        </div>
        <a href='" . htmlspecialchars($loginUrl) . "'
           style='display:inline-block;background:#2d6a4f;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600'>
          Log In Now →
        </a>
      </div>
      <div style='background:#f3f4f6;padding:14px 32px;text-align:center;font-size:.75rem;color:#9ca3af'>
        © " . date('Y') . " Zamboanga Del Sur Provincial Government College
      </div>
    </div>";
    return sendMail($toEmail, $toName, $subject, $html);
}
