<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Times New Roman", serif; font-size: 15px; margin: 56px; line-height: 1.58; }
        .center { text-align: center; }
        .indent { text-indent: 48px; text-align: justify; }
        .line {
            display: inline-block;
            border-bottom: 1px solid #111;
            min-width: 160px;
            padding: 0 6px 2px;
            line-height: 1.4;
            text-align: center;
            vertical-align: bottom;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .big-gap { margin-top: 34px; }
        .meta { margin-top: 36px; }
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
@endphp

<h3 class="center">SECRETARY’S CERTIFICATE</h3>

<p class="indent">
    I, <span class="line" style="min-width: 240px;">{{ $secretaryName }}</span>, of legal age, Filipino and a resident of
    <span class="line" style="min-width: 320px;">{{ $secretaryAddress }}</span>, after having been sworn to in accordance with law,
    hereby depose and state that:
</p>

<p class="indent">
    1. I am the duly elected and qualified Corporate Secretary of
    <span class="line" style="min-width: 320px;">{{ $corpName }}</span>,
    a corporation duly organized and existing under the laws of the Republic of the Philippines, with principal address at
    <span class="line" style="min-width: 300px;">{{ $corpAddress }}</span>.
</p>

<p class="indent">
    2. Resolved that <span class="line" style="min-width: 220px;">{{ $fields['authorized_person_name'] ?? '' }}</span>
    is / are hereby authorized to transact and process documents with the Bureau of Internal Revenue (BIR), including but not limited to:
    business registration, updating of registration, case monitoring system updates, submission of requirements, closure of business,
    application for Permit to Use (PTU), and application for Authority to Print (ATP).
</p>

<p class="indent">
    3. That the aforementioned person/s is / are further authorized to process Securities and Exchange Commission (SEC) transactions,
    including but not limited to: business registration, updating of registration, filing of reports, and pick-up of approved documents
    or certificates.
</p>

<p class="indent">
    4. That the aforementioned person/s is / are likewise authorized to process documents with Local Government Units (LGUs),
    including but not limited to: updating of business registration, business closure, and submission of compliance documents.
</p>

<p class="center big-gap">
    IN WITNESS WHEREOF, I have hereunto signed this Certificate this
    <span class="line" style="min-width: 72px;">{{ $day }}</span> day of
    <span class="line" style="min-width: 140px;">{{ $month }}</span> {{ $year }} in Pampanga, Philippines.
</p>

<p class="center big-gap">
    <strong style="text-transform: uppercase;">{{ $secretaryName }}</strong><br>
    Corporate Secretary
</p>

<p class="indent big-gap">
    SUBSCRIBED AND SWORN TO before me this ______ day of ____________ 2026, affiant exhibiting to me
    his/her Tax Identification Number (TIN) <span class="line">{{ $fields['tin'] ?? '' }}</span>.
</p>

<div class="meta">
    <div>DOC. NO. <span class="line" style="min-width: 120px;">{{ $fields['doc_no'] ?? '' }}</span>;</div>
    <div>PAGE NO. <span class="line" style="min-width: 120px;">{{ $fields['page_no'] ?? '' }}</span>;</div>
    <div>BOOK NO. <span class="line" style="min-width: 120px;">{{ $fields['book_no'] ?? '' }}</span>.</div>
    <div>SERIES OF <span class="line" style="min-width: 120px;">{{ $fields['series'] ?? '' }}</span>.</div>
</div>

</body>
</html>
