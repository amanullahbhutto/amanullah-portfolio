<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>New portfolio contact message</title>
    <style>
        @media only screen and (max-width: 640px) {
            .email-shell { width: 100% !important; }
            .email-pad { padding: 22px !important; }
            .email-title { font-size: 28px !important; line-height: 1.12 !important; }
            .detail-column { display: block !important; width: 100% !important; padding-right: 0 !important; padding-bottom: 10px !important; }
            .brand-text { display: block !important; margin-top: 8px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background:#0b0c0f; color:#f6f7fb; font-family: Arial, Helvetica, sans-serif; line-height:1.6;">
    <span style="display:none!important; max-height:0; max-width:0; opacity:0; overflow:hidden; color:transparent;">
        New message from {{ $contactMessage->name }}: {{ $contactMessage->subject }}
    </span>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#0b0c0f; margin:0; padding:28px 12px;">
        <tr>
            <td align="center">
                <table class="email-shell" role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="width:640px; max-width:640px; background:#17191f; border:1px solid rgba(255,255,255,.09); border-radius:22px; overflow:hidden;">
                    <tr>
                        <td class="email-pad" style="padding:30px 34px 26px; background:#111318; border-bottom:1px solid rgba(255,255,255,.09);">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="vertical-align:middle;">
                                        <span style="display:inline-block; width:42px; height:42px; border-radius:12px; background:#ff6b2c; color:#ffffff; font-size:20px; font-weight:800; line-height:42px; text-align:center;">A</span>
                                        <span class="brand-text" style="display:inline-block; margin-left:12px; color:#f6f7fb; font-size:15px; font-weight:800; letter-spacing:.08em; vertical-align:middle;">AMANULLAH<span style="color:#ff6b2c;">.</span></span>
                                    </td>
                                    <td align="right" style="vertical-align:middle;">
                                        <span style="display:inline-block; padding:7px 10px; border-radius:9px; background:rgba(255,107,44,.13); color:#ff6b2c; font-size:11px; font-weight:800; letter-spacing:.12em; text-transform:uppercase;">New Lead</span>
                                    </td>
                                </tr>
                            </table>

                            <h1 class="email-title" style="margin:28px 0 10px; color:#f6f7fb; font-size:34px; line-height:1.08; font-weight:800;">New contact message</h1>
                            <p style="margin:0; color:#a7abb6; font-size:15px;">A visitor submitted the portfolio contact form. The message is saved in your admin inbox too.</p>
                        </td>
                    </tr>

                    <tr>
                        <td class="email-pad" style="padding:30px 34px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:20px;">
                                <tr>
                                    <td class="detail-column" width="50%" style="width:50%; padding-right:8px; vertical-align:top;">
                                        <div style="background:#20232a; border:1px solid rgba(255,255,255,.09); border-radius:14px; padding:15px;">
                                            <div style="color:#ff6b2c; font-size:11px; font-weight:800; letter-spacing:.1em; text-transform:uppercase;">Name</div>
                                            <div style="color:#f6f7fb; font-size:16px; font-weight:700; margin-top:5px;">{{ $contactMessage->name }}</div>
                                        </div>
                                    </td>
                                    <td class="detail-column" width="50%" style="width:50%; padding-left:8px; vertical-align:top;">
                                        <div style="background:#20232a; border:1px solid rgba(255,255,255,.09); border-radius:14px; padding:15px;">
                                            <div style="color:#ff6b2c; font-size:11px; font-weight:800; letter-spacing:.1em; text-transform:uppercase;">Email</div>
                                            <div style="color:#f6f7fb; font-size:15px; font-weight:700; margin-top:5px; word-break:break-word;">
                                                <a href="mailto:{{ $contactMessage->email }}" style="color:#f6f7fb; text-decoration:none;">{{ $contactMessage->email }}</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:20px;">
                                <tr>
                                    @if($contactMessage->phone)
                                        <td class="detail-column" width="50%" style="width:50%; padding-right:8px; vertical-align:top;">
                                            <div style="background:#20232a; border:1px solid rgba(255,255,255,.09); border-radius:14px; padding:15px;">
                                                <div style="color:#ff6b2c; font-size:11px; font-weight:800; letter-spacing:.1em; text-transform:uppercase;">Phone</div>
                                                <div style="color:#f6f7fb; font-size:15px; font-weight:700; margin-top:5px;">{{ $contactMessage->phone }}</div>
                                            </div>
                                        </td>
                                    @endif
                                    <td class="detail-column" width="{{ $contactMessage->phone ? '50%' : '100%' }}" style="width:{{ $contactMessage->phone ? '50%' : '100%' }}; {{ $contactMessage->phone ? 'padding-left:8px;' : '' }} vertical-align:top;">
                                        <div style="background:#20232a; border:1px solid rgba(255,255,255,.09); border-radius:14px; padding:15px;">
                                            <div style="color:#ff6b2c; font-size:11px; font-weight:800; letter-spacing:.1em; text-transform:uppercase;">Subject</div>
                                            <div style="color:#f6f7fb; font-size:15px; font-weight:700; margin-top:5px;">{{ $contactMessage->subject }}</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div style="background:#0f1116; border:1px solid rgba(255,107,44,.25); border-radius:16px; padding:20px 22px; margin-bottom:24px;">
                                <div style="color:#ff6b2c; font-size:11px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; margin-bottom:10px;">Message</div>
                                <div style="color:#e7e9ef; font-size:15px; white-space:pre-line;">{{ $contactMessage->message }}</div>
                            </div>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="border-radius:12px; background:#ff6b2c;">
                                        <a href="{{ route('admin.messages.show', $contactMessage) }}" style="display:inline-block; padding:13px 18px; color:#ffffff; font-size:14px; font-weight:800; text-decoration:none; border-radius:12px;">Open in Admin Inbox</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 34px 24px; border-top:1px solid rgba(255,255,255,.09); background:#111318;">
                            <p style="margin:0; color:#a7abb6; font-size:12px;">Received {{ $contactMessage->created_at?->format('M d, Y h:i A') }}. Reply directly to this email to contact {{ $contactMessage->name }}.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
