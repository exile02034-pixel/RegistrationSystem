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

.bullet-list { margin: 10px 0 0; padding: 0 0 0 20px; }

.bullet-list li { margin: 0 0 6px; text-align: justify; }

.witness { text-align: justify; margin-top: 12px; margin-bottom: 0; }

.signature-wrap {
    margin-top: 10px;
    text-align: right;
    padding-right: 24px;
}

.signature-img {
    display: block;
    margin: 0 auto 6px;
    max-height: 108px;
    width: auto;
    object-fit: contain;
}

.signature-name-underlined {
    display: inline-block;
    border-bottom: 1px solid #111;
    padding-bottom: 2px;
    min-width: 240px;
    font-size: 14pt;
    line-height: 1.1;
    margin: 0;
}

.signature-role {
    margin: 4px 0 0;
    font-size: 11pt;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.sworn { margin-top: 12px; text-align: justify; margin-bottom: 0; }

.meta { margin-top: 14px; line-height: 1.2; }
</style>
</head>

<body>

@php
$defaultSecretaryName = 'Vince Anthony Feir';
$defaultSecretaryAddress = '299 Purok 1 San Agustin Lubao Pampanga';
$defaultTin = '765-241-127-000';

$secretaryName = trim((string) ($fields['secretary_name'] ?? ''));
$secretaryAddress = trim((string) ($fields['secretary_address'] ?? ''));
$corpName = trim((string) ($fields['corporation_name'] ?? ''));
$corpAddress = trim((string) ($fields['corporation_address'] ?? ''));

if ($secretaryName === '') $secretaryName = $defaultSecretaryName;
if ($secretaryAddress === '') $secretaryAddress = $defaultSecretaryAddress;

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
    } catch (\Throwable) {}
}

$signatureDataUri = trim((string) ($fields['secretary_signature_data_uri'] ?? ''));

if ($signatureDataUri === '') {
    $signaturePath = public_path('images/Signature.png');
    if (is_file($signaturePath)) {
        $binary = file_get_contents($signaturePath);
        $mime = mime_content_type($signaturePath) ?: 'image/png';
        $signatureDataUri = 'data:'.$mime.';base64,'.base64_encode($binary);
    }
}

$signatureIsImage = str_starts_with($signatureDataUri, 'data:image/');
$signatureText = $signatureIsImage ? '' : $signatureDataUri;

$tin = trim((string) ($fields['tin'] ?? ''));
if ($tin === '') $tin = $defaultTin;
@endphp


<h3 class="title">SECRETARY’S CERTIFICATE</h3>

<p class="lead">
I, <span class="line" style="min-width:240px;">{{ $secretaryName }}</span>, of legal age, Filipino and a resident of
<span class="line" style="min-width:320px;">{{ $secretaryAddress }}</span>,
after having been sworn to in accordance with law, hereby depose and state that:
</p>

<ol>
<li>
I am the duly elected and qualified Corporate Secretary of
<span class="line" style="min-width:300px;">{{ $corpName }}</span>
corporation duly organized and existing under the laws of the Republic of the Philippines, with principal address at
<span class="line" style="min-width:260px;">{{ $corpAddress }}</span>.
</li>

<li>
Resolved that <span class="line" style="min-width:180px;">Ronnel Landa</span>
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
</ol>

<p class="witness">
IN WITNESS WHEREOF, I have hereunto signed this Certificate this
<span class="line" style="min-width:72px;">{{ $day }}</span> day of
<span class="line" style="min-width:140px;">{{ $month }}</span> {{ $year }}
in Pampanga, Philippines.
</p>


<div class="signature-wrap">
@if ($signatureDataUri !== '')
    <div style="display:inline-block; text-align:center;">

        @if ($signatureIsImage)
            <img src="{{ $signatureDataUri }}" class="signature-img">
        @else
            <p class="signature-name-underlined">{{ $signatureText }}</p>
        @endif

        <p class="signature-role">Corporate Secretary</p>

    </div>
@endif
</div>


<p class="sworn">
SUBSCRIBED AND SWORN TO before me this ______ day of ____________ 2026, affiant exhibiting to me
her/his Tax Identification Number (TIN) <span class="line">{{ $tin }}</span>.
</p>

<div class="meta">
<div>DOC. NO.</div>
<div>PAGE NO.</div>
<div>BOOK NO.</div>
<div>SERIES OF</div>
</div>

</body>
</html>
