<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>We Received Your Files</title>
</head>
<body>
    <p>Hello,</p>

    <p>
        Thank you for submitting your registration documents for {{ $companyTypeLabel }}.
        We received {{ $filesCount }} file(s) successfully.
    </p>

    <p>
        Our team will review your submission and contact you if we need any additional information.
    </p>

    <p>
        <a href="{{ config('app.url') }}" style="display:inline-block;padding:10px 16px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">
            Visit Our Website
        </a>
    </p>

    <p>Thank you.</p>
</body>
</html>
