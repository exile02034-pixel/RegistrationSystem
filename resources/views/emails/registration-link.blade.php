<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Complete Your Registration</title>
</head>
<body>
    <p>Greetings,</p>

    <p>
        Please complete your online registration form for {{ $companyTypeLabel }} using the secure link below.
    </p>

    <p>
        <a href="{{ $registrationUrl }}" style="display:inline-block;padding:10px 16px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">
            Open Registration Form
        </a>
    </p>

    <p>If the button above does not work, copy and paste this URL:</p>
    <p><a href="{{ $registrationUrl }}">{{ $registrationUrl }}</a></p>

    <p>Or scan this QR code to open the same registration form:</p>
    <p>
        <img src="{{ $qrCodeDataUri }}" alt="Registration QR Code" width="220" height="220" style="border:1px solid #e5e7eb;border-radius:8px;" />
    </p>
</body>
</html>
