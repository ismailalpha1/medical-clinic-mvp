@php
    $locale = ($locale ?? 'en') === 'es' ? 'es' : 'en';
    $copy = $locale === 'es' ? [
        'preheader' => 'Nueva inscripción de clínica de',
        'businessLead' => 'Nueva oportunidad comercial',
        'heading' => 'Inscripción de clínica recibida',
        'intro' => 'Una clínica ha compartido su información a través del sitio web de CarePoint.',
        'clinicOverview' => 'Resumen de la clínica',
        'clinicType' => 'Tipo de clínica',
        'location' => 'Ubicación',
        'address' => 'Dirección',
        'website' => 'Sitio web',
        'notProvided' => 'No indicado',
        'doctors' => 'Médicos',
        'locations' => 'Centros',
        'established' => 'Año de fundación',
        'specialties' => 'Especialidades',
        'primaryContact' => 'Contacto principal',
        'contactMethod' => 'Método de contacto preferido',
        'servicesGoals' => 'Servicios y objetivos',
        'noServiceDetails' => 'No se proporcionaron detalles de los servicios.',
        'aboutClinic' => 'Sobre la clínica',
        'noDescription' => 'No se proporcionó una descripción.',
        'businessGoals' => 'Objetivos del negocio',
        'noGoals' => 'No se indicaron objetivos.',
        'footer' => 'Notificación de registro de clínica de CarePoint',
        'submissionId' => 'ID de envío',
        'automated' => 'Esta es una notificación automática del sitio web de CarePoint.',
    ] : [
        'preheader' => 'New clinic registration from',
        'businessLead' => 'New business lead',
        'heading' => 'Clinic registration received',
        'intro' => 'A clinic has shared its information through the CarePoint website.',
        'clinicOverview' => 'Clinic overview',
        'clinicType' => 'Clinic type',
        'location' => 'Location',
        'address' => 'Address',
        'website' => 'Website',
        'notProvided' => 'Not provided',
        'doctors' => 'Doctors',
        'locations' => 'Locations',
        'established' => 'Established',
        'specialties' => 'Specialties',
        'primaryContact' => 'Primary contact',
        'contactMethod' => 'Preferred contact',
        'servicesGoals' => 'Services and goals',
        'noServiceDetails' => 'No service details provided.',
        'aboutClinic' => 'About the clinic',
        'noDescription' => 'No description provided.',
        'businessGoals' => 'Business goals',
        'noGoals' => 'No goals provided.',
        'footer' => 'CarePoint clinic registration notification',
        'submissionId' => 'Submission ID',
        'automated' => 'This is an automated notification sent from the CarePoint website.',
    ];
    $specialtyLabels = $locale === 'es' ? [
        'family_medicine' => 'Medicina familiar',
        'dentistry' => 'Odontología',
        'cardiology' => 'Cardiología',
        'dermatology' => 'Dermatología',
        'pediatrics' => 'Pediatría',
        'orthopedics' => 'Ortopedia',
        'gynecology' => 'Ginecología',
        'diagnostics' => 'Diagnóstico',
        'other' => 'Otro',
    ] : [
        'family_medicine' => 'Family medicine',
        'dentistry' => 'Dentistry',
        'cardiology' => 'Cardiology',
        'dermatology' => 'Dermatology',
        'pediatrics' => 'Pediatrics',
        'orthopedics' => 'Orthopedics',
        'gynecology' => 'Gynecology',
        'diagnostics' => 'Diagnostics',
        'other' => 'Other',
    ];
    $goalLabels = $locale === 'es' ? [
        'improve_online_visibility' => 'Mejorar la visibilidad en línea',
        'receive_more_patient_inquiries' => 'Recibir más consultas de pacientes',
        'promote_clinic_services' => 'Promocionar los servicios de la clínica',
        'build_new_website' => 'Crear un sitio web nuevo',
        'other' => 'Otro',
    ] : [
        'improve_online_visibility' => 'Improve online visibility',
        'receive_more_patient_inquiries' => 'Receive more patient inquiries',
        'promote_clinic_services' => 'Promote clinic services',
        'build_new_website' => 'Build a new website',
        'other' => 'Other',
    ];
    $specialties = array_map(
        fn ($value) => $specialtyLabels[$value] ?? $value,
        $submission->specialties ?? [],
    );
    $goals = array_map(
        fn ($value) => $goalLabels[$value] ?? $value,
        $submission->goals ?? [],
    );
    $preferredContact = $submission->preferred_contact_method;
    if ($locale === 'es' && is_string($preferredContact)) {
        $preferredContactLabels = [
            'Email' => 'Correo electrónico',
            'Phone' => 'Teléfono',
            'WhatsApp' => 'WhatsApp',
        ];
        $preferredContact = $preferredContactLabels[$preferredContact] ?? $preferredContact;
    }
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
            .detail-column { display: block !important; width: 100% !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f2f6f6;font-family:Arial,Helvetica,sans-serif;color:#18313a;">
    <div style="display:none;font-size:1px;color:#f2f6f6;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
        {{ $copy['preheader'] }} {{ $submission->clinic_name }}.
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
                                {{ $copy['businessLead'] }}
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
                                {{ $copy['clinicOverview'] }}
                            </p>
                            <h2 style="margin:0 0 20px;color:#18313a;font-size:22px;line-height:1.35;">
                                {{ $submission->clinic_name }}
                            </h2>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="detail-column" width="50%" valign="top" style="padding:0 12px 16px 0;">
                                        <p style="margin:0 0 5px;color:#829196;font-size:11px;font-weight:bold;text-transform:uppercase;">{{ $copy['clinicType'] }}</p>
                                        <p style="margin:0;color:#344e55;font-size:14px;line-height:1.5;">{{ $submission->clinic_type ?: $copy['notProvided'] }}</p>
                                    </td>
                                    <td class="detail-column" width="50%" valign="top" style="padding:0 0 16px 12px;">
                                        <p style="margin:0 0 5px;color:#829196;font-size:11px;font-weight:bold;text-transform:uppercase;">{{ $copy['location'] }}</p>
                                        <p style="margin:0;color:#344e55;font-size:14px;line-height:1.5;">{{ $submission->city }}, {{ $submission->country }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="detail-column" width="50%" valign="top" style="padding:0 12px 16px 0;">
                                        <p style="margin:0 0 5px;color:#829196;font-size:11px;font-weight:bold;text-transform:uppercase;">{{ $copy['address'] }}</p>
                                        <p style="margin:0;color:#344e55;font-size:14px;line-height:1.5;">{{ $submission->address ?: $copy['notProvided'] }}</p>
                                    </td>
                                    <td class="detail-column" width="50%" valign="top" style="padding:0 0 16px 12px;">
                                        <p style="margin:0 0 5px;color:#829196;font-size:11px;font-weight:bold;text-transform:uppercase;">{{ $copy['website'] }}</p>
                                        @if ($submission->website)
                                            <a href="{{ $submission->website }}" style="color:#168c78;font-size:14px;line-height:1.5;word-break:break-word;">{{ $submission->website }}</a>
                                        @else
                                            <p style="margin:0;color:#344e55;font-size:14px;line-height:1.5;">{{ $copy['notProvided'] }}</p>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="detail-column" width="50%" valign="top" style="padding:0 12px 16px 0;">
                                        <p style="margin:0 0 5px;color:#829196;font-size:11px;font-weight:bold;text-transform:uppercase;">{{ $copy['doctors'] }}</p>
                                        <p style="margin:0;color:#344e55;font-size:14px;line-height:1.5;">{{ $submission->number_of_doctors ?: $copy['notProvided'] }}</p>
                                    </td>
                                    <td class="detail-column" width="50%" valign="top" style="padding:0 0 16px 12px;">
                                        <p style="margin:0 0 5px;color:#829196;font-size:11px;font-weight:bold;text-transform:uppercase;">{{ $copy['locations'] }}</p>
                                        <p style="margin:0;color:#344e55;font-size:14px;line-height:1.5;">{{ $submission->number_of_locations ?: $copy['notProvided'] }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="detail-column" width="50%" valign="top" style="padding:0 12px 4px 0;">
                                        <p style="margin:0 0 5px;color:#829196;font-size:11px;font-weight:bold;text-transform:uppercase;">{{ $copy['established'] }}</p>
                                        <p style="margin:0;color:#344e55;font-size:14px;line-height:1.5;">{{ $submission->year_established ?: $copy['notProvided'] }}</p>
                                    </td>
                                    <td class="detail-column" width="50%" valign="top" style="padding:0 0 4px 12px;">
                                        <p style="margin:0 0 5px;color:#829196;font-size:11px;font-weight:bold;text-transform:uppercase;">{{ $copy['specialties'] }}</p>
                                        <p style="margin:0;color:#344e55;font-size:14px;line-height:1.5;">{{ implode(', ', $specialties) ?: $copy['notProvided'] }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-gutter" style="padding:24px 34px;background-color:#f8fbfa;border-top:1px solid #e8efee;">
                            <p style="margin:0 0 16px;color:#168c78;font-size:11px;font-weight:bold;letter-spacing:1.2px;text-transform:uppercase;">
                                {{ $copy['primaryContact'] }}
                            </p>
                            <h2 style="margin:0 0 12px;color:#18313a;font-size:18px;line-height:1.4;">
                                {{ $submission->contact_person }}
                            </h2>
                            <p style="margin:0 0 8px;font-size:14px;line-height:1.6;">
                                <a href="mailto:{{ $submission->email }}" style="color:#168c78;text-decoration:none;">{{ $submission->email }}</a>
                            </p>
                            <p style="margin:0;color:#52666c;font-size:14px;line-height:1.6;">
                                {{ $submission->phone }} <span style="color:#a1b0b3;">&nbsp;|&nbsp;</span> {{ $copy['contactMethod'] }}: {{ $preferredContact }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-gutter" style="padding:24px 34px;background-color:#ffffff;border-top:1px solid #e8efee;">
                            <h2 style="margin:0 0 10px;color:#18313a;font-size:16px;">{{ $copy['servicesGoals'] }}</h2>
                            <p style="margin:0 0 18px;color:#52666c;font-size:14px;line-height:1.7;white-space:pre-line;">{{ $submission->services ?: $copy['noServiceDetails'] }}</p>
                            <h2 style="margin:0 0 10px;color:#18313a;font-size:16px;">{{ $copy['aboutClinic'] }}</h2>
                            <p style="margin:0 0 18px;color:#52666c;font-size:14px;line-height:1.7;white-space:pre-line;">{{ $submission->description ?: $copy['noDescription'] }}</p>
                            <h2 style="margin:0 0 10px;color:#18313a;font-size:16px;">{{ $copy['businessGoals'] }}</h2>
                            <p style="margin:0;color:#52666c;font-size:14px;line-height:1.7;">{{ implode(', ', $goals) ?: $copy['noGoals'] }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 34px;background-color:#f2f6f6;border-radius:0 0 14px 14px;color:#829196;font-size:12px;line-height:1.6;">
                            {{ $copy['footer'] }}<br>
                            {{ $copy['submissionId'] }}: {{ $submission->id }}
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
