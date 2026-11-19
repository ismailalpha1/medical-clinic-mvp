@php
    $locale = ($locale ?? 'en') === 'es' ? 'es' : 'en';
    $copy = $locale === 'es' ? [
        'preheader' => 'Nueva consulta del sitio web de',
        'eyebrow' => 'Nueva consulta',
        'heading' => 'Alguien se ha puesto en contacto',
        'intro' => 'Se ha recibido una nueva consulta a través del sitio web de CarePoint.',
        'details' => 'Datos de contacto',
        'name' => 'Nombre',
        'email' => 'Correo electrónico',
        'phone' => 'Teléfono',
        'subject' => 'Asunto',
        'message' => 'Mensaje',
        'notProvided' => 'No indicado',
        'reply' => 'Responder a esta consulta',
        'call' => 'Llamar a este contacto',
        'inquiryId' => 'ID de consulta',
        'footer' => 'Notificación de contacto de CarePoint',
        'automated' => 'Este mensaje es una notificación automática del sitio web de CarePoint.',
    ] : [
        'preheader' => 'New website inquiry from',
        'eyebrow' => 'New inquiry',
        'heading' => 'Someone got in touch',
        'intro' => 'A new inquiry was received through the CarePoint website.',
        'details' => 'Contact details',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'subject' => 'Subject',
        'message' => 'Message',
        'notProvided' => 'Not provided',
        'reply' => 'Reply to this inquiry',
        'call' => 'Call this contact',
        'inquiryId' => 'Inquiry ID',
        'footer' => 'CarePoint contact notification',
        'automated' => 'This is an automated notification sent from the CarePoint website.',
    ];
@endphp
<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $copy['heading'] }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-shell { width: 100% !important; }
            .email-gutter { padding-left: 20px !important; padding-right: 20px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f2f6f6;font-family:Arial,Helvetica,sans-serif;color:#18313a;">
    <div style="display:none;font-size:1px;color:#f2f6f6;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
        {{ $copy['preheader'] }} {{ $inquiry->name }}.
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f2f6f6;">
        <tr>
            <td align="center" style="padding:32px 12px;">
                <table role="presentation" class="email-shell" width="640" cellpadding="0" cellspacing="0" border="0" style="width:640px;max-width:640px;">
                    <tr>
                        <td style="padding:0 0 16px 4px;color:#173b45;font-size:20px;font-weight:bold;letter-spacing:-.4px;">
                            CarePoint<span style="color:#20a78b;">+</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 34px;background-color:#173b45;border-radius:14px 14px 0 0;">
                            <p style="margin:0 0 9px;color:#8ce0cb;font-size:11px;font-weight:bold;letter-spacing:1.6px;text-transform:uppercase;">
                                {{ $copy['eyebrow'] }}
                            </p>
                            <h1 style="margin:0;color:#ffffff;font-size:26px;line-height:1.3;font-weight:700;">
                                {{ $copy['heading'] }}
                            </h1>
                            <p style="margin:10px 0 0;color:#d4e5e3;font-size:14px;line-height:1.6;">
                                {{ $copy['intro'] }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-gutter" style="padding:28px 34px;background-color:#ffffff;">
                            <p style="margin:0 0 6px;color:#168c78;font-size:11px;font-weight:bold;letter-spacing:1.2px;text-transform:uppercase;">
                                {{ $copy['details'] }}
                            </p>
                            <h2 style="margin:0 0 20px;color:#18313a;font-size:22px;line-height:1.35;">
                                {{ $inquiry->name }}
                            </h2>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td valign="top" style="padding:0 0 16px;">
                                        <p style="margin:0 0 5px;color:#829196;font-size:11px;font-weight:bold;text-transform:uppercase;">{{ $copy['email'] }}</p>
                                        @if ($inquiry->email)
                                            <a href="mailto:{{ $inquiry->email }}" style="color:#168c78;font-size:14px;line-height:1.5;text-decoration:none;word-break:break-word;">{{ $inquiry->email }}</a>
                                        @else
                                            <p style="margin:0;color:#344e55;font-size:14px;line-height:1.5;">{{ $copy['notProvided'] }}</p>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" style="padding:0 0 16px;">
                                        <p style="margin:0 0 5px;color:#829196;font-size:11px;font-weight:bold;text-transform:uppercase;">{{ $copy['phone'] }}</p>
                                        <p style="margin:0;color:#344e55;font-size:14px;line-height:1.5;">{{ $inquiry->phone ?: $copy['notProvided'] }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" style="padding:0;">
                                        <p style="margin:0 0 5px;color:#829196;font-size:11px;font-weight:bold;text-transform:uppercase;">{{ $copy['subject'] }}</p>
                                        <p style="margin:0;color:#344e55;font-size:14px;line-height:1.5;">{{ $inquiry->subject }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-gutter" style="padding:24px 34px;background-color:#f8fbfa;border-top:1px solid #e8efee;">
                            <h2 style="margin:0 0 12px;color:#18313a;font-size:16px;">{{ $copy['message'] }}</h2>
                            <div style="padding:16px 18px;border-left:3px solid #20a78b;border-radius:0 8px 8px 0;background-color:#ffffff;color:#52666c;font-size:14px;line-height:1.7;white-space:pre-line;">
                                {{ $inquiry->message }}
                            </div>
                            @if ($inquiry->email)
                                <p style="margin:22px 0 0;">
                                    <a href="mailto:{{ $inquiry->email }}" style="display:inline-block;padding:12px 18px;border-radius:7px;background-color:#168c78;color:#ffffff;font-size:13px;font-weight:bold;text-decoration:none;">
                                        {{ $copy['reply'] }}
                                    </a>
                                </p>
                            @elseif ($inquiry->phone)
                                <p style="margin:22px 0 0;">
                                    <a href="tel:{{ $inquiry->phone }}" style="display:inline-block;padding:12px 18px;border-radius:7px;background-color:#168c78;color:#ffffff;font-size:13px;font-weight:bold;text-decoration:none;">
                                        {{ $copy['call'] }}
                                    </a>
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 34px;background-color:#f2f6f6;border-radius:0 0 14px 14px;color:#829196;font-size:12px;line-height:1.6;">
                            {{ $copy['footer'] }}<br>
                            {{ $copy['inquiryId'] }}: {{ $inquiry->id }}
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:18px 8px 0;color:#9aa8ab;font-size:11px;line-height:1.5;">
                            {{ $copy['automated'] }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
