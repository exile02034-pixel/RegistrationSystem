<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Follow-up: Missing Registration Documents</title>
</head>
<body>
    <p>Hello,</p>

    <p>
        We hope you are doing well. This is a polite follow-up regarding your {{ $companyTypeLabel }} registration.
        We are still missing the following document(s):
    </p>

    <ul>
        @foreach($missingDocuments as $document)
            <li>{{ $document }}</li>
        @endforeach
    </ul>

    <p>
        For your convenience, we have attached the missing template file(s) to this email.
        Please complete and upload them using the link below.
    </p>

    <p>
        <a href="{{ $uploadUrl }}" style="display:inline-block;padding:10px 16px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">
            Upload Missing Documents
        </a>
    </p>

    <p>
        Thank you very much for your time and cooperation. Please let us know if you need any assistance.
    </p>
</body>
</html>
