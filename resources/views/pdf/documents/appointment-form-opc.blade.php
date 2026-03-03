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
    </style>
</head>
<body>
<h1>Form for Appointment of Officers</h1>
<h2>One Person Corporation (OPC) - For the Year {{ $fields['for_the_year'] ?? '' }}</h2>

<table>
    <tr><th colspan="4" class="section-title">Corporate Information</th></tr>
    <tr><th style="width: 18%">Corporate Name</th><td style="width: 32%">{{ $fields['corporate_name'] ?? '' }}</td><th style="width: 18%">Date of Registration</th><td style="width: 32%">{{ $fields['date_of_registration'] ?? '' }}</td></tr>
    <tr><th>Business / Trade Name</th><td>{{ $fields['business_trade_name'] ?? '' }}</td><th>Fiscal Year End</th><td>{{ $fields['fiscal_year_end'] ?? '' }}</td></tr>
    <tr><th>SEC Registration Number</th><td>{{ $fields['sec_registration_number'] ?? '' }}</td><th>Email Address</th><td>{{ $fields['email_address'] ?? '' }}</td></tr>
    <tr><th>Primary Purpose / Activity / Industry Presently Engaged In</th><td>{{ $fields['primary_purpose_activity'] ?? '' }}</td><th>Corporate TIN</th><td>{{ $fields['corporate_tin'] ?? '' }}</td></tr>
    <tr><th>Telephone Number</th><td colspan="3">{{ $fields['telephone_number'] ?? '' }}</td></tr>
    <tr><th>Complete Business Address</th><td colspan="3">{{ $fields['complete_business_address'] ?? '' }}</td></tr>
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
    @foreach(($fields['officers'] ?? []) as $officer)
        <tr>
            <td>{{ $officer['name_and_residential_address'] ?? '' }}</td>
            <td>{{ $officer['nationality'] ?? '' }}</td>
            <td>{{ $officer['gender'] ?? '' }}</td>
            <td>{{ $officer['position'] ?? '' }}</td>
            <td>{{ $officer['tin'] ?? '' }}</td>
        </tr>
    @endforeach
</table>

<p class="note"><strong>NOTE: USE ADDITIONAL SHEET IF NECESSARY</strong></p>

<div class="notary">
    <p>Certified Correct:</p>
    <div class="sign"></div>
    <p class="tiny center">{{ $fields['certifier_name'] ?? '' }}</p>
    <p class="tiny">(Signature over printed name)</p>
    <p>(TIN) {{ $fields['certifier_tin'] ?? '' }}</p>

    <p>
        SUBSCRIBED AND SWORN TO before me in
        <span class="line">{{ $fields['sworn_place'] ?? '' }}</span>
        on
        <span class="line">{{ $fields['sworn_date'] ?? '' }}</span>
        by affiant who personally appeared before me and exhibited to me his/her competent evidence of identity consisting of
        <span class="line" style="min-width: 180px;">{{ $fields['competent_evidence'] ?? '' }}</span>
        issued at
        <span class="line" style="min-width: 90px;">{{ $fields['issued_at'] ?? '' }}</span>
        on
        <span class="line" style="min-width: 90px;">{{ $fields['issued_on'] ?? '' }}</span>.
    </p>

    <div class="center" style="margin-top: 18px;">NOTARY PUBLIC</div>
</div>
</body>
</html>
