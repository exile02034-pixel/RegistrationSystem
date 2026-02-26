<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>File Preview</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; margin: 0; color: #0f172a; }
        .container { max-width: 900px; margin: 24px auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; }
        h1 { margin: 0 0 8px; font-size: 22px; }
        .meta { color: #475569; margin-bottom: 16px; }
        .actions { display: flex; gap: 10px; margin-bottom: 18px; }
        .btn { display: inline-block; padding: 9px 14px; text-decoration: none; border-radius: 7px; font-size: 14px; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-outline { border: 1px solid #cbd5e1; color: #0f172a; }
        .content { white-space: pre-wrap; line-height: 1.6; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Preview: {{ $fileName }}</h1>
        <p class="meta">PDF preview is unavailable in this runtime, showing readable document text instead.</p>

        <div class="actions">
            <a class="btn btn-primary" href="{{ $downloadOriginalUrl }}">Download Original</a>
            <a class="btn btn-outline" href="{{ $downloadPdfUrl }}">Download PDF</a>
        </div>

        <div class="content">{{ $previewText ?: 'No preview text available for this file.' }}</div>
    </div>
</body>
</html>
