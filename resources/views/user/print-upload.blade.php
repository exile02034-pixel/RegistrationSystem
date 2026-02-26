<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print {{ $fileName }}</title>
    <style>
        html, body { margin: 0; padding: 0; height: 100%; background: #fff; }
        iframe { width: 100%; height: 100%; border: 0; }
    </style>
</head>
<body>
    @if ($printableUrl)
        <iframe id="print-frame" src="{{ $printableUrl }}" title="Printable Document"></iframe>
    @else
        <div style="display:flex;height:100%;align-items:center;justify-content:center;font-family:Arial,sans-serif;color:#0f172a;">
            {{ $errorMessage ?? 'Unable to print this file.' }}
        </div>
    @endif

    <script>
        @if ($printableUrl)
        const frame = document.getElementById('print-frame');

        frame.addEventListener('load', function () {
            setTimeout(function () {
                try {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                } catch (_) {
                    window.print();
                }
            }, 500);
        });

        window.addEventListener('afterprint', function () {
            window.close();
        });
        @endif
    </script>
</body>
</html>
