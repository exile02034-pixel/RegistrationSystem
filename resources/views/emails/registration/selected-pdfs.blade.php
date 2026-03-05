<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Selected registration PDFs</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <p>Hello,</p>

    <p>The selected registration PDF files are attached to this email.</p>

    @if (! empty($documentNames))
        <p><strong>Attached documents:</strong></p>
        <ul>
            @foreach ($documentNames as $name)
                <li>{{ $name }}</li>
            @endforeach
        </ul>
    @endif

    <p>
        Registration email: {{ $registrationLink->email }}<br>
        Sent at: {{ now()->timezone('Asia/Manila')->format('M d, Y h:i A') }}
    </p>
</body>
</html>
