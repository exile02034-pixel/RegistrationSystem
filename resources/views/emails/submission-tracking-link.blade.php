<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Submission Tracking Link</title>
</head>
<body>
    <p>Hello,</p>

    <p>Use this secure link to view your submission status and continue editing if revisions are still allowed.</p>

    <p>
        <a href="{{ $trackingUrl }}" style="display:inline-block;padding:10px 16px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">
            Open Submission Tracking
        </a>
    </p>

    <p>This link expires soon for your security. If it expires, request a new one from the tracking page.</p>

    <p>If the button above does not work, copy and paste this URL:</p>
    <p><a href="{{ $trackingUrl }}">{{ $trackingUrl }}</a></p>
</body>
</html>
