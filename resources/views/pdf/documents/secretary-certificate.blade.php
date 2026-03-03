<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Times New Roman", serif; font-size: 12pt; margin: 72px; line-height: 1.28; color: #111; }
        h3, p, li, div, span { font-size: 12pt; }
        .center { text-align: center; }
        .justify { text-align: justify; }
        .lead { text-indent: 36px; text-align: justify; margin-top: 12px; margin-bottom: 0; }
        .title {
            text-align: center;
            font-weight: 700;
            text-decoration: underline;
            letter-spacing: 0.3px;
            margin: 0 0 12px;
        }
        .line {
            display: inline-block;
            border-bottom: 1px solid #111;
            min-width: 160px;
            padding: 0 6px 2px;
            line-height: 1.2;
            text-align: center;
            vertical-align: bottom;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .bullet-list {
            margin: 10px 0 0;
            padding: 0 0 0 20px;
        }
        .bullet-list li {
            margin: 0 0 6px;
            text-align: justify;
        }
        .witness { text-align: justify; margin-top: 12px; margin-bottom: 0; }
        .signature-wrap { margin-top: 10px; text-align: right; padding-right: 24px; }
        .signature-img {
            display: block;
            margin: 0 0 6px auto;
            max-height: 108px;
            width: auto;
            object-fit: contain;
        }
        .signature-name {
            font-weight: 700;
            text-transform: uppercase;
            margin: 0;
        }
        .signature-name-secondary {
            font-weight: 700;
            text-transform: uppercase;
            margin: 0;
            text-decoration: underline;
        }
        .signature-role {
            margin: 6px 0 0;
        }
        .notary-img {
            display: block;
            margin: 0 0 8px;
            max-height: 84px;
            width: auto;
            object-fit: contain;
        }
        .sworn { margin-top: 12px; text-align: justify; margin-bottom: 0; }
        .meta { margin-top: 14px; line-height: 1.2; }
    </style>
</head>
<body>
@php
    $secretaryName = trim((string) ($fields['secretary_name'] ?? ''));
    $secretaryAddress = trim((string) ($fields['secretary_address'] ?? ''));
    $corpName = trim((string) ($fields['corporation_name'] ?? ''));
    $corpAddress = trim((string) ($fields['corporation_address'] ?? ''));

    if ($secretaryName === '' || $secretaryAddress === '') {
        $legacySecretaryRaw = trim((string) ($fields['secretary_name_address'] ?? ''));
        [$legacyName, $legacyAddress] = array_pad(array_map('trim', explode(',', $legacySecretaryRaw, 2)), 2, '');
        $secretaryName = $secretaryName !== '' ? $secretaryName : $legacyName;
        $secretaryAddress = $secretaryAddress !== '' ? $secretaryAddress : $legacyAddress;
    }

    if ($corpName === '' || $corpAddress === '') {
        $legacyCorpRaw = trim((string) ($fields['corporation_name_address'] ?? ''));
        [$legacyCorpName, $legacyCorpAddress] = array_pad(array_map('trim', explode(',', $legacyCorpRaw, 2)), 2, '');
        $corpName = $corpName !== '' ? $corpName : $legacyCorpName;
        $corpAddress = $corpAddress !== '' ? $corpAddress : $legacyCorpAddress;
    }

    $signingDate = trim((string) ($fields['signing_date'] ?? ''));
    $day = '';
    $month = '';
    $year = '2026';
    if ($signingDate !== '') {
        try {
            $parsed = \Carbon\Carbon::parse($signingDate);
            $day = $parsed->format('j');
            $month = $parsed->format('F');
            $year = $parsed->format('Y');
        } catch (\Throwable) {
            $day = '';
            $month = '';
        }
    }

    if ($day === '' || $month === '') {
        $legacySigningRaw = trim((string) ($fields['signing_day_month'] ?? ''));
        $day = $legacySigningRaw;
        $month = '';
        if (preg_match('/^(\d{1,2})\s*(?:,|-)?\s*(.*)$/', $legacySigningRaw, $m) === 1) {
            $day = trim((string) $m[1]);
            $month = trim((string) ($m[2] ?? ''));
        }
    }

    $signaturePath = public_path('images/Signature.png');
    $hasSignature = is_file($signaturePath);
    $signatureDataUri = '';
    if ($hasSignature) {
        try {
            $binary = file_get_contents($signaturePath);
            if ($binary !== false) {
                $mime = mime_content_type($signaturePath) ?: 'image/png';
                $signatureDataUri = 'data:'.$mime.';base64,'.base64_encode($binary);
            }
        } catch (\Throwable) {
            $signatureDataUri = '';
        }
    }
@endphp

<h3 class="title">SECRETARY’S CERTIFICATE</h3>

<p class="lead">
    I, <span class="line" style="min-width: 240px;">Vince Anthony Feir</span>, of legal age, Filipino and a resident of
    <span class="line" style="min-width: 320px;">299 Purok 1 San Agustin Lubao Pampanga</span>, after having been sworn to in accordance with law,
    hereby depose and state that:
</p>

<ul class="bullet-list">
    <li>
        I am the duly elected and qualified Corporate Secretary of
        <span class="line" style="min-width: 300px;">{{ $corpName }}</span>
        corporation duly organized and existing under the laws of the Republic of the Philippines, with principal address at
        <span class="line" style="min-width: 260px;">{{ $corpAddress }}</span>.
    </li>
    <li>
        Resolved that <span class="line" style="min-width: 180px;">{{ $fields['authorized_person_name'] ?? '' }}</span>
        is / are hereby authorized to transact and process documents with the Bureau of Internal Revenue (BIR), including but not limited to:
        business registration, updating of registration, case monitoring system updates, submission of requirements, closure of business,
        application for Permit to Use (PTU), and application for Authority to Print (ATP).
    </li>
    <li>
        That the aforementioned persons are further authorized to process Securities and Exchange Commission (SEC) transactions,
        including but not limited to: business registration, updating of registration, filing of reports, and pick-up of approved documents
        or certificates.
    </li>
    <li>
        That the aforementioned persons are likewise authorized to process documents with Local Government Units (LGUs),
        including but not limited to: updating of business registration, business closure, and submission of compliance documents.
    </li>
</ul>

<p class="witness">
    IN WITNESS WHEREOF, I have hereunto signed this Certificate this
    <span class="line" style="min-width: 72px;">{{ $day }}</span> day of
    <span class="line" style="min-width: 140px;">{{ $month }}</span> {{ $year }} in Pampanga, Philippines.
</p>

<div class="signature-wrap">
    @if ($signatureDataUri !== '')
        <img src="{{ $signatureDataUri }}" alt="" class="signature-img">
    @endif
</div>

<p class="sworn">
    SUBSCRIBED AND SWORN TO before me this ______ day of ____________ 2026, affiant exhibiting to me
    her/his Tax Identification Number (TIN) <span class="line">{{ $fields['tin'] ?? '' }}</span>.
</p>

<div class="meta">
    <div>DOC. NO. <span class="line" style="min-width: 120px;">{{ $fields['doc_no'] ?? '' }}</span>;</div>
    <div>PAGE NO. <span class="line" style="min-width: 120px;">{{ $fields['page_no'] ?? '' }}</span>;</div>
    <div>BOOK NO. <span class="line" style="min-width: 120px;">{{ $fields['book_no'] ?? '' }}</span>.</div>
    <div>SERIES OF <span class="line" style="min-width: 120px;">{{ $fields['series'] ?? '' }}</span>.</div>
</div>

</body>
</html>
