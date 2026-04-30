<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AdminEmailNotifier
{
    public static function send(string $subject, string $title, string $body): void
    {
        // Load SMTP from DB settings
        $s = \DB::table('app_settings')->pluck('value', 'key');
        $smtpHost     = $s['smtp_host']     ?? config('mail.mailers.smtp.host');
        $smtpPort     = $s['smtp_port']     ?? config('mail.mailers.smtp.port', '587');
        $smtpUser     = $s['smtp_username'] ?? config('mail.from.address');
        $smtpPass     = $s['smtp_password'] ?? config('mail.mailers.smtp.password');
        $smtpFromName = $s['smtp_from_name'] ?? config('app.name');

        if (empty($smtpHost) || empty($smtpUser) || empty($smtpPass)) {
            return; // SMTP not configured, skip silently
        }

        config([
            'mail.mailers.smtp.host'       => $smtpHost,
            'mail.mailers.smtp.port'       => $smtpPort,
            'mail.mailers.smtp.username'   => $smtpUser,
            'mail.mailers.smtp.password'   => $smtpPass,
            'mail.mailers.smtp.encryption' => 'tls',
            'mail.from.address'            => $smtpUser,
            'mail.from.name'               => $smtpFromName,
            'mail.default'                 => 'smtp',
        ]);

        $admins = User::where('role', 'admin')
            ->whereNotNull('email')
            ->where('email', 'not like', 'pending_%')
            ->pluck('email')
            ->toArray();

        if (empty($admins)) return;

        $html = self::buildPublicHtml($title, $body);

        foreach ($admins as $email) {
            try {
                Mail::html($html, function($msg) use ($email, $subject, $smtpUser, $smtpFromName) {
                    $msg->to($email)->subject($subject)->from($smtpUser, $smtpFromName);
                });
            } catch (\Exception $e) {
                // Fail silently — don't break the app if email fails
            }
        }
    }

    private static function buildHtml(string $title, string $body): string
    {
        return self::buildPublicHtml($title, $body);
    }

    public static function buildPublicHtml(string $title, string $body, string $recipientName = ''): string
    {
        $firstName = $recipientName ? explode(' ', trim($recipientName))[0] : '';
        $salutation = $firstName ? "Happy ArkCrest Morning, {$firstName}! 👋" : "Happy ArkCrest Morning! 👋";
        $time = now()->format('F j, Y g:i A');

        return "<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
<body style='margin:0;padding:0;background:#eef2f7;font-family:\"Segoe UI\",Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#eef2f7;padding:32px 16px;'>
  <tr><td align='center'>
  <table width='600' cellpadding='0' cellspacing='0' style='max-width:600px;width:100%;'>

    <tr>
      <td style='background:linear-gradient(135deg,#0a1e3d 0%,#1e4575 55%,#2563eb 100%);padding:40px 40px 32px;text-align:center;border-radius:16px 16px 0 0;'>
        <table width='100%' cellpadding='0' cellspacing='0'>
          <tr><td align='center' style='padding-bottom:16px;'>
            <div style='display:inline-block;background:rgba(255,255,255,.15);border-radius:50%;width:60px;height:60px;line-height:60px;font-size:28px;text-align:center;'>🏢</div>
          </td></tr>
          <tr><td align='center'>
            <h1 style='color:white;margin:0;font-size:22px;font-weight:700;letter-spacing:.5px;'>ArkCrest Realty Corporation</h1>
            <p style='color:rgba(255,255,255,.65);margin:6px 0 0;font-size:12px;text-transform:uppercase;letter-spacing:1.5px;'>Internal System Notification</p>
          </td></tr>
        </table>
      </td>
    </tr>

    <tr>
      <td style='background:white;padding:36px 40px 8px;'>
        <h2 style='margin:0 0 10px;font-size:24px;font-weight:700;color:#0a1e3d;'>{$salutation}</h2>
        <p style='margin:0;font-size:14px;color:#64748b;line-height:1.7;'>Here's a quick heads-up from your ArkCrest system. Please take a moment to review the important updates below.</p>
      </td>
    </tr>

    <tr>
      <td style='background:white;padding:20px 40px 8px;'>
        <div style='height:1px;background:linear-gradient(90deg,#e2e8f0,#94a3b8,#e2e8f0);'></div>
      </td>
    </tr>

    <tr>
      <td style='background:white;padding:20px 40px 8px;'>
        <p style='margin:0 0 6px;font-size:10px;font-weight:700;color:#2563eb;text-transform:uppercase;letter-spacing:1.5px;'>Reminder Details</p>
        <h3 style='margin:0;font-size:16px;font-weight:700;color:#0a1e3d;'>{$title}</h3>
      </td>
    </tr>

    <tr>
      <td style='background:white;padding:16px 40px 32px;'>
        <table width='100%' cellpadding='0' cellspacing='0'>
          <tr>
            <td style='background:#f0f6ff;border-left:4px solid #2563eb;border-radius:0 10px 10px 0;padding:20px 22px;font-size:13px;color:#374151;line-height:1.9;'>
              {$body}
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <tr>
      <td style='background:white;padding:0 40px 36px;text-align:center;'>
        <a href='https://arkcrestrealtycorporation.com' style='display:inline-block;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;text-decoration:none;padding:13px 36px;border-radius:8px;font-size:13px;font-weight:700;letter-spacing:.5px;'>Open ArkCrest System &rarr;</a>
      </td>
    </tr>

    <tr>
      <td style='background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 40px;text-align:center;border-radius:0 0 16px 16px;'>
        <p style='margin:0;font-size:11px;color:#94a3b8;line-height:1.7;'>
          This is an automated reminder from the ArkCrest Realty System.<br>
          Sent on {$time} (Philippine Time) &bull; Please do not reply to this email.
        </p>
      </td>
    </tr>

  </table>
  </td></tr>
</table>
</body>
</html>";
    }
}
