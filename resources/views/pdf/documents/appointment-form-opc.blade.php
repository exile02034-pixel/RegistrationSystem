<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11.5px; color: #111827; margin: 16px; }
        h1 { font-size: 16px; margin: 0 0 2px; text-transform: uppercase; text-align: center; letter-spacing: .4px; }
        h2 { font-size: 11.5px; margin: 0 0 10px; text-align: center; color: #374151; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #111827; padding: 5px 6px; vertical-align: top; text-align: left; }
        th { background: #f4f4f4; font-weight: 700; }
        .tiny { font-size: 10px; color: #374151; }
        .section-title { background: #e5e7eb; font-weight: 700; text-transform: uppercase; }
        .note { margin-top: 10px; font-size: 11px; }
        .notary { margin-top: 8px; font-size: 11px; line-height: 1.6; }
        .line { display: inline-block; border-bottom: 1px solid #111827; min-width: 130px; padding: 0 4px; }
        .sign { margin: 18px 0 2px; border-bottom: 1px solid #111827; width: 260px; }
        .center { text-align: center; }
        .certified { margin: 8px 0 14px auto; width: 320px; text-align: center; }
        .certified-label { margin: 0 0 18px; font-weight: 700; }
        .certified-name { margin: 0 auto 2px; width: 290px; border-bottom: 1px solid #111827; padding: 2px 6px; }
        .certified-sub { margin: 0; font-size: 10px; color: #374151; }
        .certified-tin { margin: 6px 0 0; font-size: 11px; }
    </style>
</head>
<body>
<h1>Form for Appointment of Officers</h1>
<h2>One Person Corporation (OPC) - For the Year {{ $fields['for_the_year'] ?? '' }}</h2>
@php
    $officers = is_array($fields['officers'] ?? null) ? array_values($fields['officers']) : [];
    $president = collect($officers)->first(function ($officer) {
        $position = strtolower(trim((string) ($officer['position'] ?? '')));
        return $position === 'president';
    });
    $certifierName = (string) ($president['name_and_residential_address'] ?? '');
    $certifierTin = (string) ($president['tin'] ?? '');
@endphp

<table>
    <tr><th colspan="4" class="section-title">Corporate Information</th></tr>
    <tr><th style="width: 18%">Corporate Name</th><td style="width: 32%">{{ $fields['corporate_name'] ?? '' }}</td><th style="width: 18%">Date of Registration</th><td style="width: 32%">{{ $fields['date_of_registration'] ?? '' }}</td></tr>
    <tr><th>Business / Trade Name</th><td>{{ $fields['business_trade_name'] ?? '' }}</td><th>Fiscal Year End</th><td>December 31</td></tr>
    <tr><th>SEC Registration Number</th><td>{{ $fields['sec_registration_number'] ?? '' }}</td><th>Email Address</th><td>vafeir27@gmail.com</td></tr>
    <tr><th>Primary Purpose / Activity / Industry Presently Engaged In</th><td>{{ $fields['primary_purpose_activity'] ?? '' }}</td><th>Corporate TIN</th><td>{{ $fields['corporate_tin'] ?? '' }}</td></tr>
    <tr><th>Telephone Number</th><td colspan="3">{{ $fields['telephone_number'] ?? '' }}</td></tr>
    <tr><th>Complete Business Address</th><td colspan="3">482 Purok 4 San Juan Nepomuceno Betis Guagua Pampanga</td></tr>
</table>

<table>
    <tr><th colspan="5" class="section-title">Officers' Information</th></tr>
    <tr>
        <th style="width: 45%">Name / Current Residential Address</th>
        <th style="width: 13%">Nationality</th>
        <th style="width: 12%">Gender</th>
        <th style="width: 15%">Officer / Position</th>
        <th style="width: 15%">TIN</th>
    </tr>

    @foreach($officers as $officer)
        <tr>
            <td>{{ $officer['name_and_residential_address'] ?? '' }}</td>
            <td>{{ $officer['nationality'] ?: 'Filipino' }}</td>
            <td>{{ $officer['gender'] ?? '' }}</td>
            <td>{{ $officer['position'] ?? '' }}</td>
            <td>{{ $officer['tin'] ?? '' }}</td>
        </tr>
    @endforeach
</table>

<p class="note"><strong>NOTE: USE ADDITIONAL SHEET IF NECESSARY</strong></p>

<div class="notary">
    <div class="certified">
        <p class="certified-label">Certified Correct:</p>
        <p class="certified-name">{{ $certifierName !== '' ? $certifierName : '(President Name)' }}</p>
        <p class="certified-sub">(Signature over printed name)</p>
        <p class="certified-tin">(TIN) {{ $certifierTin }}</p>
    </div>

    <p>
        SUBSCRIBED AND SWORN TO before me in
        <span class="line"></span>
        on
        <span class="line"></span>
        by affiant who personally appeared before me and exhibited to me his/her competent evidence of identity consisting of
        <span class="line" style="min-width: 180px;"></span>
        issued at
        <span class="line" style="min-width: 90px;"></span>
        on
        <span class="line" style="min-width: 90px;"></span>.
    </p>

    <div class="center" style="margin-top: 18px;">NOTARY PUBLIC</div>
    <div style="margin-top:8pt; width:235pt; margin-left:0; font-size:10.2pt; line-height:1.2;">
            <div style="margin-bottom:4pt;">DOC. NO. <span style="display:inline-block; min-width:110px; border-bottom:1px solid #000;"></span>;</div>
            <div style="margin-bottom:4pt;">PAGE NO. <span style="display:inline-block; min-width:110px; border-bottom:1px solid #000;"></span>;</div>
            <div style="margin-bottom:4pt;">BOOK NO. <span style="display:inline-block; min-width:110px; border-bottom:1px solid #000;"></span>;</div>
            <div>SERIES OF <span style="display:inline-block; min-width:110px; border-bottom:1px solid #000;"></span>.</div>
        </div>
</div>
</body>
</html>
