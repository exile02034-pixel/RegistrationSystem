<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
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
        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #64748b;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">{{ $title }}</h1>
        <p class="meta">Submission ID: {{ $submission->id }} | Email: {{ $submission->email }}</p>
    </div>

    @if(isset($sectionView))
        @include($sectionView, ['rows' => $rows])
    @else
        <table>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <th>{{ $row['label'] }}</th>
                        <td>{{ $row['value'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="footer">Generated on {{ now()->format('M d, Y h:i A') }}</p>
</body>
</html>
