<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>New Website Inquiry</title>
<!--[if mso]>
<noscript>
<xml>
<o:OfficeDocumentSettings>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml>
</noscript>
<![endif]-->
<style>
  :root { color-scheme: light; supported-color-schemes: light; }
  body { margin:0; padding:0; }
  table { border-collapse:collapse; }
  a { text-decoration:none; }
  /* Belt-and-suspenders: force light regardless of client dark-mode heuristics */
  [data-ogsc] .force-bg-white { background-color:#ffffff !important; }
  [data-ogsc] .force-bg-navy { background-color:#0d1a2b !important; }
  [data-ogsc] .force-bg-soft { background-color:#f8fafc !important; }
  [data-ogsc] .force-text-dark { color:#1b2733 !important; }
  [data-ogsc] .force-text-white { color:#ffffff !important; }
  [data-ogsc] .force-text-muted { color:#94a3b8 !important; }
</style>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">
  New inquiry received via the ArkCrest Realty website.
</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;">
  <tr>
    <td align="center" style="padding:40px 16px;">
      <table role="presentation" width="520" cellpadding="0" cellspacing="0" class="force-bg-white" bgcolor="#ffffff" style="width:520px;max-width:520px;background-color:#ffffff;border-radius:12px;overflow:hidden;">

        <!-- Header -->
        <tr>
          <td class="force-bg-navy" bgcolor="#0d1a2b" style="background-color:#0d1a2b;padding:30px 32px;text-align:center;">
            <span class="force-text-white" style="color:#d3652f;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:3px;text-transform:uppercase;font-weight:700;">ArkCrest Realty</span>
            <h1 class="force-text-white" style="margin:6px 0 0 0;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:18px;letter-spacing:1px;font-weight:700;">New Website Inquiry</h1>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td class="force-bg-white" bgcolor="#ffffff" style="background-color:#ffffff;padding:32px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">

              <tr>
                <td style="padding-bottom:18px;">
                  <div class="force-text-muted" style="color:#94a3b8;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-bottom:4px;">Full Name</div>
                  <div class="force-text-dark" style="color:#1b2733;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:600;">{{ $inquiry->full_name }}</div>
                </td>
              </tr>

              <tr>
                <td style="padding-bottom:18px;">
                  <div class="force-text-muted" style="color:#94a3b8;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-bottom:4px;">Email</div>
                  <div class="force-text-dark" style="color:#1b2733;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:600;">
                    <a href="mailto:{{ $inquiry->email }}" class="force-text-dark" style="color:#1b2733;text-decoration:none;">{{ $inquiry->email }}</a>
                  </div>
                </td>
              </tr>

              @if($inquiry->phone)
              <tr>
                <td style="padding-bottom:18px;">
                  <div class="force-text-muted" style="color:#94a3b8;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-bottom:4px;">Phone</div>
                  <div class="force-text-dark" style="color:#1b2733;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:600;">
                    <a href="tel:{{ $inquiry->phone }}" class="force-text-dark" style="color:#1b2733;text-decoration:none;">{{ $inquiry->phone }}</a>
                  </div>
                </td>
              </tr>
              @endif

              @if($inquiry->property_interest)
              <tr>
                <td style="padding-bottom:18px;">
                  <div class="force-text-muted" style="color:#94a3b8;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-bottom:4px;">Property Interest</div>
                  <div class="force-text-dark" style="color:#1b2733;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:600;">{{ $inquiry->property_interest }}</div>
                </td>
              </tr>
              @endif

              @if($inquiry->message)
              <tr>
                <td style="padding-bottom:18px;">
                  <div class="force-text-muted" style="color:#94a3b8;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-bottom:4px;">Message</div>
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="force-bg-soft" bgcolor="#f8fafc" style="background-color:#f8fafc;border-radius:6px;">
                    <tr>
                      <td style="border-left:3px solid #d3652f;padding:16px 18px;">
                        <div class="force-text-dark" style="color:#334155;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;white-space:pre-line;">{{ $inquiry->message }}</div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              @endif

              <tr>
                <td style="padding-top:6px;">
                  <table role="presentation" cellpadding="0" cellspacing="0">
                    <tr>
                      <td bgcolor="#d3652f" style="background-color:#d3652f;border-radius:4px;">
                        <a href="mailto:{{ $inquiry->email }}" class="force-text-white" style="display:inline-block;padding:12px 20px;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:12px;letter-spacing:1px;text-transform:uppercase;font-weight:700;text-decoration:none;">Reply to {{ $inquiry->full_name }}</a>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

            </table>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td class="force-bg-soft" bgcolor="#f8fafc" style="background-color:#f8fafc;border-top:1px solid #e2e8f0;padding:16px 32px;text-align:center;">
            <span class="force-text-muted" style="color:#94a3b8;font-family:Arial,Helvetica,sans-serif;font-size:11px;">Submitted from the ArkCrest Realty website &mdash; {{ $inquiry->created_at->format('F j, Y g:i A') }}</span>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>