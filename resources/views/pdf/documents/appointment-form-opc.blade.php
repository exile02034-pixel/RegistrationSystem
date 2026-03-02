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
    </style>
</head>
<body>
<h1>Appointment Form - One Person Corporation (OPC)</h1>
<h2>Registration #{{ $registrationLink->id }}</h2>

<table>
    <tr><th colspan="4" class="section-title">Corporate Information</th></tr>
    <tr><th style="width: 18%">Corporate Name</th><td style="width: 32%">{{ $fields['corporate_name'] ?? '' }}</td><th style="width: 18%">Trade Name</th><td style="width: 32%">{{ $fields['trade_name'] ?? '' }}</td></tr>
    <tr><th>SEC Registration Number</th><td>{{ $fields['sec_registration_number'] ?? '' }}</td><th>Date of Registration</th><td>{{ $fields['date_of_registration'] ?? '' }}</td></tr>
    <tr><th>Fiscal Year End</th><td>{{ $fields['fiscal_year_end'] ?? '' }}</td><th>Corporate TIN</th><td>{{ $fields['corporate_tin'] ?? '' }}</td></tr>
    <tr><th>Email Address</th><td>{{ $fields['email_address'] ?? '' }}</td><th>Telephone Number</th><td>{{ $fields['telephone_number'] ?? '' }}</td></tr>
    <tr><th>Complete Business Address</th><td colspan="3">{{ $fields['complete_business_address'] ?? '' }}</td></tr>
    <tr><th>Primary Purpose / Activity</th><td colspan="3">{{ $fields['primary_purpose_activity'] ?? '' }}</td></tr>
</table>

<table>
    <tr><th colspan="5" class="section-title">Officers</th></tr>
    <tr>
        <th style="width: 15%">Role</th>
        <th style="width: 45%">Name &amp; Residential Address</th>
        <th style="width: 13%">Nationality</th>
        <th style="width: 12%">Gender</th>
        <th style="width: 15%">TIN</th>
    </tr>
    @foreach(($fields['officers'] ?? []) as $officer)
        <tr>
            <td>{{ $officer['role'] ?? '' }}</td>
            <td>{{ $officer['name_and_residential_address'] ?? '' }}</td>
            <td>{{ $officer['nationality'] ?? '' }}</td>
            <td>{{ $officer['gender'] ?? '' }}</td>
            <td>{{ $officer['tin'] ?? '' }}</td>
        </tr>
    @endforeach
</table>

<table>
    <tr><th colspan="4" class="section-title">Certification</th></tr>
    <tr><th style="width: 18%">Certifier Name</th><td style="width: 32%">{{ $fields['certifier_name'] ?? '' }}</td><th style="width: 18%">Certifier TIN</th><td style="width: 32%">{{ $fields['certifier_tin'] ?? '' }}</td></tr>
</table>

<p class="tiny">
    This generated form uses placeholders from the Input Fields form and mirrors the Appointment Form OPC structure for PDF output.
</p>
</body>
</html>
