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
    <tr><th>Business / Trade Name</th><td>{{ $fields['business_trade_name'] ?? '' }}</td><th>Fiscal Year End</th><td>December 31</td></tr>
    <tr><th>SEC Registration Number</th><td>{{ $fields['sec_registration_number'] ?? '' }}</td><th>Email Address</th><td>vafeir27@gmail.com</td></tr>
    <tr><th>Primary Purpose / Activity / Industry Presently Engaged In</th><td>{{ $fields['primary_purpose_activity'] ?? '' }}</td><th>Corporate TIN</th><td>010-829-526-000</td></tr>
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

    <tr>
        <td>Alexander Rubio</td>
        <td>Filipino</td>
        <td>Male</td>
        <td>President</td>
        <td>720-431-690-000</td>
    </tr>

    <tr>
        <td>Mary Rose Mendoza Esteban</td>
        <td>Filipino</td>
        <td>Female</td>
        <td>Treasurer</td>
        <td>455-574-943-000</td>
    </tr>

    <tr>
        <td>Vince Anthony Feir</td>
        <td>Filipino</td>
        <td>Male</td>
        <td>Corporate Secretary</td>
        <td>765-241-127-000</td>
    </tr>
</table>

<p class="note"><strong>NOTE: USE ADDITIONAL SHEET IF NECESSARY</strong></p>

<div class="notary">
    <p>Certified Correct:</p>
    <p class="tiny center">Alexander Rubio Esteban</p>
    <p class="tiny">(Signature over printed name)</p>
    <p>(TIN)720-431-690-000</p>

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
</div>
</body>
</html>
