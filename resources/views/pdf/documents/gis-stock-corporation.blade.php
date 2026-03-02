<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 14px; }
        body { font-family: Arial, sans-serif; font-size: 10.5px; color: #111827; }
        h1 { font-size: 15px; margin: 0 0 2px; text-transform: uppercase; text-align: center; letter-spacing: .3px; }
        h2 { font-size: 10.5px; margin: 0 0 8px; text-align: center; font-weight: normal; color: #374151; }
        .section-title { font-size: 10px; margin: 8px 0 4px; background: #e5e7eb; padding: 3px 5px; border: 1px solid #111827; font-weight: 700; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        th, td { border: 1px solid #111827; padding: 5px; vertical-align: top; text-align: left; }
        th { background: #f3f4f6; font-weight: 700; }
        .break { page-break-after: always; }
        .tiny { font-size: 9px; color: #4b5563; }
    </style>
</head>
<body>
<h1>General Information Sheet (GIS) - Stock Corporation</h1>
<h2>Registration #{{ $registrationLink->id }}</h2>

<div class="section-title">Step 1: Corporate Information</div>
<table>
    <tr><th style="width: 20%">Corporate Name</th><td style="width: 30%">{{ $fields['step_1']['corporate_name'] ?? '' }}</td><th style="width: 20%">SEC Registration Number</th><td style="width: 30%">{{ $fields['step_1']['sec_registration_number'] ?? '' }}</td></tr>
    <tr><th>Principal Office Address</th><td colspan="3">{{ $fields['step_1']['principal_office_address'] ?? '' }}</td></tr>
    <tr><th>Business Address</th><td colspan="3">{{ $fields['step_1']['business_address'] ?? '' }}</td></tr>
    <tr><th>Email</th><td>{{ $fields['step_1']['email'] ?? '' }}</td><th>Telephone</th><td>{{ $fields['step_1']['telephone'] ?? '' }}</td></tr>
    <tr><th>Annual Meeting Date</th><td>{{ $fields['step_1']['meeting_date_annual'] ?? '' }}</td><th>Special Meeting Date</th><td>{{ $fields['step_1']['meeting_date_special'] ?? '' }}</td></tr>
</table>

<div class="section-title">Step 2: AMLA Information</div>
<table>
    <tr><th style="width: 25%">AMLA Covered</th><td style="width: 25%">{{ ($fields['step_2']['amla_covered'] ?? false) ? 'YES' : 'NO' }}</td><th style="width: 25%">AMLA Reporting Entity</th><td style="width: 25%">{{ ($fields['step_2']['amla_reporting_entity'] ?? false) ? 'YES' : 'NO' }}</td></tr>
    <tr><th>Other AMLA Details</th><td colspan="3">{{ $fields['step_2']['amla_other_details'] ?? '' }}</td></tr>
</table>

<div class="section-title">Step 3: Capital Structure</div>
<table>
    <tr><th style="width: 33%">Authorized Capital Stock</th><th style="width: 33%">Subscribed Capital Stock</th><th style="width: 34%">Paid-Up Capital Stock</th></tr>
    <tr><td>{{ $fields['step_3']['authorized_capital_stock'] ?? '' }}</td><td>{{ $fields['step_3']['subscribed_capital_stock'] ?? '' }}</td><td>{{ $fields['step_3']['paid_up_capital_stock'] ?? '' }}</td></tr>
</table>

<div class="section-title">Step 4: Directors</div>
<table>
    <thead><tr><th>Name</th><th>Nationality</th><th>Shareholdings</th></tr></thead>
    <tbody>
    @foreach(($fields['step_4'] ?? []) as $row)
        <tr>
            <td>{{ $row['name'] ?? '' }}</td>
            <td>{{ $row['nationality'] ?? '' }}</td>
            <td>{{ $row['shareholdings'] ?? '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="section-title">Step 5: Officers</div>
<table>
    <thead><tr><th>Position</th><th>Name</th><th>TIN</th></tr></thead>
    <tbody>
    @foreach(($fields['step_5'] ?? []) as $row)
        <tr>
            <td>{{ $row['position'] ?? '' }}</td>
            <td>{{ $row['name'] ?? '' }}</td>
            <td>{{ $row['tin'] ?? '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="break"></div>

<div class="section-title">Step 6: Stockholders</div>
<table>
    <thead><tr><th>Stockholder Name</th><th>Shares</th></tr></thead>
    <tbody>
    @foreach(($fields['step_6'] ?? []) as $row)
        <tr>
            <td>{{ $row['stockholder_name'] ?? '' }}</td>
            <td>{{ $row['shares'] ?? '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="section-title">Step 7: External Auditor</div>
<table>
    <tr><th style="width: 20%">Name</th><td style="width: 30%">{{ $fields['step_7']['external_auditor_name'] ?? '' }}</td><th style="width: 20%">TIN</th><td style="width: 30%">{{ $fields['step_7']['external_auditor_tin'] ?? '' }}</td></tr>
</table>

<div class="section-title">Step 8: Corporate Secretary</div>
<table>
    <tr><th style="width: 20%">Name</th><td style="width: 30%">{{ $fields['step_8']['corporate_secretary_name'] ?? '' }}</td><th style="width: 20%">TIN</th><td style="width: 30%">{{ $fields['step_8']['corporate_secretary_tin'] ?? '' }}</td></tr>
</table>

<div class="section-title">Step 9: Certification</div>
<table>
    <tr><th style="width: 20%">Certifier Name</th><td style="width: 30%">{{ $fields['step_9']['certifier_name'] ?? '' }}</td><th style="width: 20%">Certifier TIN</th><td style="width: 30%">{{ $fields['step_9']['certifier_tin'] ?? '' }}</td></tr>
    <tr><th>Certification Date</th><td colspan="3">{{ $fields['step_9']['certifier_date'] ?? '' }}</td></tr>
</table>

<p class="tiny">
    This output follows a Blade placeholder layout mapped from Input Fields data. For strict 1:1 government form geometry, native Excel-to-PDF rendering is still required.
</p>
</body>
</html>
