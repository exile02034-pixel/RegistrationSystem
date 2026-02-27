<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Selected Forms</title>
    <style>
        @page { margin: 24px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.4;
        }
        .header { margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: 700; margin: 0 0 4px 0; }
        .meta { color: #475569; font-size: 11px; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            vertical-align: top;
        }
        th {
            width: 38%;
            background: #f1f5f9;
            text-align: left;
            font-weight: 600;
        }
    </style>
</head>
<body>
    @foreach($sections as $section)
        <div class="header">
            <h1 class="title">{{ $section['title'] }}</h1>
            <p class="meta">Submission ID: {{ $submission->id }} | Email: {{ $submission->email }}</p>
        </div>

        @include($section['view'], ['rows' => $section['rows']])

        @if(! $loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>
</html>
