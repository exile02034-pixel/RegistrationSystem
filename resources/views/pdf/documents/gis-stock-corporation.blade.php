<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 14px; }
        body { font-family: Cambria, serif; font-size: 10px; color: #111; }
        .page { page-break-after: always; }
        .page:last-of-type { page-break-after: auto; }
        .title { margin: 0; text-align: center; font-weight: 700; font-size: 16px; }
        .subtitle { margin: 0 0 6px; text-align: center; font-weight: 700; font-size: 12px; }
        .rule { text-align: center; font-size: 9px; margin: 6px 0; }
        .section-title { margin: 8px 0 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        th, td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        th { background: #f3f3f3; text-align: left; }
        .small { font-size: 9px; }
        .page-no { font-size: 9px; color: #444; text-align: right; margin-top: 4px; }
        .check-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 12px; margin-bottom: 8px; }
        .check-item { display: flex; align-items: center; gap: 6px; }
        .box { display: inline-block; width: 11px; height: 11px; border: 1px solid #000; text-align: center; line-height: 11px; font-size: 9px; }
        .box.tight { width: 10px; height: 10px; line-height: 10px; font-size: 8px; }
        .line { border-bottom: 1px solid #000; min-height: 16px; display: inline-block; min-width: 160px; padding: 0 4px; }
        .indent { margin-left: 14px; }
        .gis-amla { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .gis-amla td, .gis-amla th { border: 1px solid #000; padding: 3px; vertical-align: top; }
        .gis-amla .num { width: 5%; text-align: center; font-weight: 700; }
        .gis-amla .detail { width: 95%; }
        .gis-amla .option-line { margin: 0 0 2px; line-height: 1.25; }
        .gis-amla .option-line:last-child { margin-bottom: 0; }
        .gis-amla .option-line .box { margin-right: 4px; vertical-align: middle; }
        .gis-amla .option-key { display: inline-block; width: 14px; font-weight: 700; }
        .cap-table th, .cap-table td { padding: 3px; font-size: 8.5px; }
        .cap-label { font-weight: 700; text-transform: uppercase; background: #f3f3f3; }
        .cap-sub { font-weight: 700; text-align: left; }
        .num { text-align: right; }
        .page9-wrap { font-size: 12px; line-height: 1.42; margin-top: 12px; }
        .page9-wrap p { margin: 0 0 13px; text-align: justify; }
        .page9-done { margin: 20px 0 24px; text-align: left; }
        .page9-done .line { min-width: 92px; min-height: 14px; vertical-align: baseline; }
        .page9-signature { width: 340px; margin: 22px 8px 24px auto; text-align: center; }
        .page9-signature-image { width: 100%; height: auto; max-height: 126px; display: block; }
        .page9-notary { margin-top: 8px; text-align: left; }
        .page9-notary .line { min-width: 86px; min-height: 14px; vertical-align: baseline; }
        .page9-notary-title { margin-top: 54px; text-align: center; font-weight: 700; letter-spacing: 0.4px; }
        .page9-meta { width: 280px; margin-top: 22px; font-size: 11px; line-height: 1.5; }
        .page9-meta-row { margin: 0 0 4px; white-space: nowrap; }
        .page9-meta .line { min-width: 120px; min-height: 12px; }
        .page9-footer { margin-top: 200px; font-size: 9px; color: #222; }
        .page9-footer-left { float: left; }
        .page9-footer-right { float: right; }
        .page9-footer::after { content: ""; display: block; clear: both; }
    </style>
</head>
<body>
@php
    $step1 = $fields['step_1'] ?? [];
    $step2 = $fields['step_2'] ?? [];
    $step3 = $fields['step_3'] ?? [];
    $step4 = $fields['step_4'] ?? [];
    $step5 = $fields['step_5'] ?? [];
    $step6 = $fields['step_6'] ?? [];
    $step7 = $fields['step_7'] ?? [];
    $step8 = $fields['step_8'] ?? [];
    $step9 = $fields['step_9'] ?? [];

    $amlaTypes = is_array($step2['amla_types'] ?? null) ? $step2['amla_types'] : [];
    $amlaDetailed = is_array($step2['amla_detailed'] ?? null) ? $step2['amla_detailed'] : [];
    $stockRows5 = is_array($step5['rows'] ?? null) ? $step5['rows'] : [];
    $stockRows6 = is_array($step6['rows'] ?? null) ? $step6['rows'] : [];
    $stockRows7 = is_array($step7['rows'] ?? null) ? $step7['rows'] : [];
    $additionalShares = is_array($step8['additional_shares'] ?? null) ? $step8['additional_shares'] : [];
    $authorizedRows = array_values(is_array($step3['authorized_rows'] ?? null) ? $step3['authorized_rows'] : []);
    $subscribedFilipinoRows = array_values(is_array($step3['subscribed_filipino_rows'] ?? null) ? $step3['subscribed_filipino_rows'] : []);
    $subscribedForeignRows = array_values(is_array($step3['subscribed_foreign_rows'] ?? null) ? $step3['subscribed_foreign_rows'] : []);
    $paidupFilipinoRows = array_values(is_array($step3['paidup_filipino_rows'] ?? null) ? $step3['paidup_filipino_rows'] : []);
    $paidupForeignRows = array_values(is_array($step3['paidup_foreign_rows'] ?? null) ? $step3['paidup_foreign_rows'] : []);
    $monthFromDate = static function ($value): string {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        $fromIso = \DateTimeImmutable::createFromFormat('Y-m-d', $raw);
        if ($fromIso instanceof \DateTimeImmutable) {
            return $fromIso->format('F');
        }

        $timestamp = strtotime($raw);
        if ($timestamp !== false) {
            return date('F', $timestamp);
        }

        return $raw;
    };
    $annualMeetingMonth = $monthFromDate($step1['meeting_date_annual'] ?? '');
    $actualMeetingMonth = $monthFromDate($step1['meeting_date_actual'] ?? '');

    $isChecked = static function ($value): bool {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int) $value === 1;
        }
        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on', 'x'], true);
        }

        return false;
    };

    $amla = static function ($group, $key = null) use ($amlaDetailed, $isChecked): bool {
        $value = $key === null ? ($amlaDetailed[$group] ?? false) : ($amlaDetailed[$group][$key] ?? false);

        return $isChecked($value);
    };

    $legacyTypeChecked = static function (string $label) use ($amlaTypes): bool {
        return in_array($label, $amlaTypes, true);
    };
@endphp

<div class="page">
    <p class="title">GENERAL INFORMATION SHEET (GIS)</p>
    <p class="subtitle">STOCK CORPORATION</p>
    <p>For the Year of 2026</p>
    <div class="section-title">General Instructions</div>
    <table>
        <tr><td>1. FOR USER CORPORATION: THIS GIS SHOULD BE SUBMITTED WITHIN THIRTY (30) CALENDAR DAYS FROM THE DATE OF THE ANNUAL STOCKHOLDERS' MEETING. DO NOT LEAVE ANY ITEM BLANK.</td></tr>
        <tr><td>2. IF NO MEETING IS HELD, THE CORPORATION SHALL SUBMIT THE GIS NOT LATER THAN JANUARY 30 OF THE FOLLOWING YEAR.</td></tr>
        <tr><td>3. THIS GIS SHALL BE ACCOMPLISHED IN ENGLISH AND CERTIFIED AND SWORN TO BY THE CORPORATE SECRETARY OF THE CORPORATION.</td></tr>
        <tr><td>4. SUBMIT FOUR (4) COPIES OF THE GIS TO THE SEC RECEIVING SECTION OR SATELLITE/OFF-SITE OFFICES.</td></tr>
    </table>

    <p class="rule">================================ PLEASE PRINT LEGIBLY ================================</p>

    <table>
        <tr>
            <th style="width:24%">CORPORATE NAME</th><td style="width:26%">{{ $step1['corporate_name'] ?? '' }}</td>
            <th style="width:24%">DATE REGISTERED</th><td style="width:26%">{{ $step1['date_registered'] ?? '' }}</td>
        </tr>
        <tr>
            <th>BUSINESS/TRADE NAME</th><td>{{ $step1['business_trade_name'] ?? '' }}</td>
            <th>FISCAL YEAR END</th><td>December 31</td>
        </tr>
        <tr>
            <th>SEC REGISTRATION NUMBER</th><td>{{ $step1['sec_registration_number'] ?? '' }}</td>
            <th>CORPORATE TAX IDENTIFICATION NUMBER (TIN)</th><td>{{ $step1['corporate_tin'] ?? '' }}</td>
        </tr>
        <tr>
            <th>DATE OF ANNUAL MEETING PER BY-LAWS</th><td>3rd Saturday of {{ $annualMeetingMonth }}</td>
            <th>ACTUAL DATE OF ANNUAL MEETING</th><td>Last Sunday of {{ $actualMeetingMonth }}</td>
        </tr>
        <tr><th>COMPLETE PRINCIPAL OFFICE ADDRESS</th><td colspan="3">482 Purok 4 San Juan Nepomuceno, Betis, Guagua, Pampanga
</td></tr>
        <tr><th>COMPLETE BUSINESS ADDRESS</th><td colspan="3">482 Purok 4 San Juan Nepomuceno, Betis, Guagua, Pampanga
</td></tr>
        <tr>
            <th>OFFICIAL E-MAIL ADDRESS</th><td>vafeir27@gmail.com</td>
            <th>ALTERNATE E-MAIL ADDRESS</th><td>tfcitaxteam@gmail.com</td>
        </tr>
        <tr>
            <th>OFFICIAL MOBILE NUMBER</th><td>09271713690</td>
            <th>ALTERNATE MOBILE NUMBER</th><td>09682312875</td>
        </tr>
        <tr>
            <th>NAME OF EXTERNAL AUDITOR & SIGNING PARTNER</th><td>{{ $step1['external_auditor_name'] ?? '' }}</td>
            <th>SEC ACCREDITATION NUMBER</th><td>{{ $step1['sec_accreditation_number'] ?? '' }}</td>
        </tr>
        <tr>
            <th>TELEPHONE NUMBER(S)</th><td>{{ $step1['telephone'] ?? '' }}</td>
            <th>FAX / WEBSITE / GEOGRAPHICAL CODE</th><td>{{ $step1['fax_number'] ?? '' }} / {{ $step1['website_url'] ?? '' }} / {{ $step1['geographical_code'] ?? '' }}</td>
        </tr>
        <tr><th>PRIMARY PURPOSE/ACTIVITY</th><td colspan="3">Wholesale and retail trading of goods </td></tr>
        <tr><th>INDUSTRY CLASSIFICATION</th><td colspan="3">Retail sale via internet</td></tr>
    </table>

    <div class="section-title">Intercompany Affiliations</div>
    <table>
        <tr><th style="width:35%">PARENT COMPANY</th><th style="width:25%">SEC REG. NO.</th><th style="width:40%">ADDRESS</th></tr>
        <tr><td>{{ $step1['intercompany_parent_company'] ?? '' }}</td><td>{{ $step1['intercompany_parent_sec_no'] ?? '' }}</td><td>{{ $step1['intercompany_parent_address'] ?? '' }}</td></tr>
        <tr><th>SUBSIDIARY/AFFILIATE</th><th>SEC REG. NO.</th><th>ADDRESS</th></tr>
        <tr><td>{{ $step1['intercompany_subsidiary'] ?? '' }}</td><td>{{ $step1['intercompany_subsidiary_sec_no'] ?? '' }}</td><td>{{ $step1['intercompany_subsidiary_address'] ?? '' }}</td></tr>
    </table>

    <div class="page-no">Page 1 of 9</div>
</div>

<div class="page">
    <p class="title">GENERAL INFORMATION SHEET</p>
    <p class="subtitle">STOCK CORPORATION</p>
    <p class="rule">================================ PLEASE PRINT LEGIBLY ================================</p>

    <table>
        <tr>
            <th style="width:22%">Corporate Name:</th>
            <td>{{ $step1['corporate_name'] ?? '' }}</td>
        </tr>
    </table>

    <div class="small" style="margin-bottom: 4px;"><strong>A.</strong> Is the Corporation a covered person under the Anti Money Laundering Act (AMLA), as amended? (Rep. Acts. 9160/9164/10167/10365)</div>
    <div class="check-item small" style="margin-bottom: 6px;">
        <span class="box">{{ ($step2['amla_covered'] ?? false) ? 'X' : '' }}</span>
        Covered person under AMLA
    </div>
    <div class="small" style="margin-bottom: 4px;">Please check all applicable boxes:</div>

    <table class="gis-amla small">
        <tr>
            <td class="num">1</td>
            <td class="detail">
                <div class="option-line"><span class="box tight">{{ ($amla('one', 'a') || $legacyTypeChecked('Banks and Other Financial Institutions under BSP')) ? 'X' : '' }}</span> <span class="option-key">a.</span> Banks</div>
                <div class="option-line"><span class="box tight">{{ $amla('one', 'b') ? 'X' : '' }}</span> <span class="option-key">b.</span> Offshore Banking Units</div>
                <div class="option-line"><span class="box tight">{{ $amla('one', 'c') ? 'X' : '' }}</span> <span class="option-key">c.</span> Quasi-Banks</div>
                <div class="option-line"><span class="box tight">{{ $amla('one', 'd') ? 'X' : '' }}</span> <span class="option-key">d.</span> Trust Entities</div>
                <div class="option-line"><span class="box tight">{{ $amla('one', 'e') ? 'X' : '' }}</span> <span class="option-key">e.</span> Non-Stock Savings and Loan Associations</div>
                <div class="option-line"><span class="box tight">{{ $amla('one', 'f') ? 'X' : '' }}</span> <span class="option-key">f.</span> Pawnshops</div>
                <div class="option-line"><span class="box tight">{{ $amla('one', 'g') ? 'X' : '' }}</span> <span class="option-key">g.</span> Foreign Exchange Dealers</div>
                <div class="option-line"><span class="box tight">{{ $amla('one', 'h') ? 'X' : '' }}</span> <span class="option-key">h.</span> Money Changers</div>
                <div class="option-line"><span class="box tight">{{ $amla('one', 'i') ? 'X' : '' }}</span> <span class="option-key">i.</span> Remittance Agents</div>
                <div class="option-line"><span class="box tight">{{ $amla('one', 'j') ? 'X' : '' }}</span> <span class="option-key">j.</span> Electronic Money Issuers</div>
                <div class="option-line"><span class="box tight">{{ $amla('one', 'k') ? 'X' : '' }}</span> <span class="option-key">k.</span> Financial institutions under BSP supervision/regulation, including subsidiaries/affiliates</div>
            </td>
        </tr>
        <tr>
            <td class="num">2</td>
            <td class="detail">
                <div class="option-line"><span class="box tight">{{ ($amla('two', 'a') || $legacyTypeChecked('Insurance Commission Regulated Entities')) ? 'X' : '' }}</span> <span class="option-key">a.</span> Insurance Companies</div>
                <div class="option-line"><span class="box tight">{{ $amla('two', 'b') ? 'X' : '' }}</span> <span class="option-key">b.</span> Insurance Agents</div>
                <div class="option-line"><span class="box tight">{{ $amla('two', 'c') ? 'X' : '' }}</span> <span class="option-key">c.</span> Insurance Brokers</div>
                <div class="option-line"><span class="box tight">{{ $amla('two', 'd') ? 'X' : '' }}</span> <span class="option-key">d.</span> Professional Reinsurers</div>
                <div class="option-line"><span class="box tight">{{ $amla('two', 'e') ? 'X' : '' }}</span> <span class="option-key">e.</span> Reinsurance Brokers</div>
                <div class="option-line"><span class="box tight">{{ $amla('two', 'f') ? 'X' : '' }}</span> <span class="option-key">f.</span> Holding Companies</div>
                <div class="option-line"><span class="box tight">{{ $amla('two', 'g') ? 'X' : '' }}</span> <span class="option-key">g.</span> Holding Company Systems</div>
                <div class="option-line"><span class="box tight">{{ $amla('two', 'h') ? 'X' : '' }}</span> <span class="option-key">h.</span> Pre-need Companies</div>
                <div class="option-line"><span class="box tight">{{ $amla('two', 'i') ? 'X' : '' }}</span> <span class="option-key">i.</span> Mutual Benefit Association</div>
                <div class="option-line"><span class="box tight">{{ $amla('two', 'j') ? 'X' : '' }}</span> <span class="option-key">j.</span> All other entities supervised and/or regulated by IC</div>
            </td>
        </tr>
        <tr>
            <td class="num">3</td>
            <td class="detail">
                <div class="option-line"><span class="box tight">{{ ($amla('three', 'a') || $legacyTypeChecked('SEC Regulated Securities Entities')) ? 'X' : '' }}</span> <span class="option-key">a.</span> Securities Dealers</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'b') ? 'X' : '' }}</span> <span class="option-key">b.</span> Securities Brokers</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'c') ? 'X' : '' }}</span> <span class="option-key">c.</span> Securities Salesman</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'd') ? 'X' : '' }}</span> <span class="option-key">d.</span> Investment Houses</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'e') ? 'X' : '' }}</span> <span class="option-key">e.</span> Investment Agents and Consultants</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'f') ? 'X' : '' }}</span> <span class="option-key">f.</span> Trading Advisors</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'g') ? 'X' : '' }}</span> <span class="option-key">g.</span> Other entities managing securities or similar services</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'h') ? 'X' : '' }}</span> <span class="option-key">h.</span> Mutual Funds / Open-end Investment Companies</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'i') ? 'X' : '' }}</span> <span class="option-key">i.</span> Close-end Investment Companies</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'j') ? 'X' : '' }}</span> <span class="option-key">j.</span> Common Trust Funds / Issuers and similar entities</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'k') ? 'X' : '' }}</span> <span class="option-key">k.</span> Transfer Companies and similar entities</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'l') ? 'X' : '' }}</span> <span class="option-key">l.</span> Entities dealing in currency/commodities/financial derivatives</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'm') ? 'X' : '' }}</span> <span class="option-key">m.</span> Entities dealing in valuable objects</div>
                <div class="option-line"><span class="box tight">{{ $amla('three', 'n') ? 'X' : '' }}</span> <span class="option-key">n.</span> Entities dealing in cash substitutes and similar monetary instruments regulated by SEC</div>
            </td>
        </tr>
        <tr>
            <td class="num">4</td>
            <td class="detail">
                <div class="option-line"><span class="box tight">{{ ($amla('four') || $legacyTypeChecked('Jewelry Dealers (Precious Metals/Stones)')) ? 'X' : '' }}</span> Jewelry dealers in precious metals</div>
            </td>
        </tr>
        <tr>
            <td class="num">5</td>
            <td class="detail">
                <div class="option-line"><span class="box tight">{{ ($amla('five') || $legacyTypeChecked('Jewelry Dealers (Precious Metals/Stones)')) ? 'X' : '' }}</span> Jewelry dealers in precious stones</div>
            </td>
        </tr>
        <tr>
            <td class="num">6</td>
            <td class="detail">
                <div class="option-line"><span class="box tight">{{ ($amla('six', 'a') || $legacyTypeChecked('Company Service Providers')) ? 'X' : '' }}</span> <span class="option-key">a.</span> Acting as a formation agent of juridical persons</div>
                <div class="option-line"><span class="box tight">{{ $amla('six', 'b') ? 'X' : '' }}</span> <span class="option-key">b.</span> Acting/arranging another as director/corporate secretary/partner/similar position</div>
                <div class="option-line"><span class="box tight">{{ $amla('six', 'c') ? 'X' : '' }}</span> <span class="option-key">c.</span> Providing registered office/business/correspondence/administrative address</div>
                <div class="option-line"><span class="box tight">{{ $amla('six', 'd') ? 'X' : '' }}</span> <span class="option-key">d.</span> Acting/arranging another as nominee shareholder</div>
            </td>
        </tr>
        <tr>
            <td class="num">7</td>
            <td class="detail">
                <div class="option-line"><span class="box tight">{{ ($amla('seven', 'a') || $legacyTypeChecked('Persons Providing AMLA-Covered Services')) ? 'X' : '' }}</span> <span class="option-key">a.</span> Managing client money, securities or other assets</div>
                <div class="option-line"><span class="box tight">{{ $amla('seven', 'b') ? 'X' : '' }}</span> <span class="option-key">b.</span> Management of bank, savings or securities accounts</div>
                <div class="option-line"><span class="box tight">{{ $amla('seven', 'c') ? 'X' : '' }}</span> <span class="option-key">c.</span> Organization of contributions for creation/operation/management of companies</div>
                <div class="option-line"><span class="box tight">{{ $amla('seven', 'd') ? 'X' : '' }}</span> <span class="option-key">d.</span> Creation/operation/management of juridical persons and buying/selling business entities</div>
            </td>
        </tr>
        <tr>
            <td class="num">8</td>
            <td class="detail">
                <div class="option-line"><span class="box tight">{{ ($amla('eight') || $legacyTypeChecked('None of the above')) ? 'X' : '' }}</span> None of the above</div>
            </td>
        </tr>
    </table>

    <div class="small" style="margin: 6px 0 4px;"><strong>B.</strong> Has the Corporation complied with the requirements on Customer Due Diligence (CDD) or Know Your Customer (KYC), record-keeping, and submission of reports under the AMLA, as amended, since the last filing of its GIS?</div>
    <div class="check-item small" style="margin-bottom: 6px;">
        <span class="box">{{ ($step2['cdd_complied'] ?? false) ? 'X' : '' }}</span>
        Yes, complied
    </div>

    <table>
        <tr><th style="width:35%">Describe nature of business</th><td>{{ $step2['nature_of_business'] ?? '' }}</td></tr>
        <tr><th>Other AMLA details</th><td>{{ $step2['amla_other_details'] ?? '' }}</td></tr>
    </table>

    <div class="page-no">Page 2 of 9</div>
</div>

<div class="page">
    <p class="title">GENERAL INFORMATION SHEET</p>
    <p class="subtitle">STOCK CORPORATION</p>
    <p class="rule">================================ PLEASE PRINT LEGIBLY ================================</p>

    <div class="section-title">Capital Structure</div>
    <table class="cap-table">
        <tr>
            <th style="width:22%">CORPORATE NAME:</th>
            <td colspan="7">{{ $step1['corporate_name'] ?? '' }}</td>
        </tr>
        <tr>
            <th class="cap-label">AUTHORIZED CAPITAL STOCK</th>
            <th class="cap-label">SUBSCRIBED CAPITAL STOCK</th>
            <th class="cap-label">PAID-UP CAPITAL STOCK</th>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td class="num">{{ $step3['authorized_capital_stock'] ?? '' }}</td>
            <td class="num">{{ $step3['subscribed_capital_stock'] ?? '' }}</td>
            <td class="num">{{ $step3['paid_up_capital_stock'] ?? '' }}</td>
            <td colspan="5"></td>
        </tr>
    </table>

    <table class="cap-table">
        <tr>
            <th colspan="8" class="cap-label">AUTHORIZED CAPITAL STOCK</th>
        </tr>
        <tr>
            <th style="width:21%">TYPE OF SHARES *</th>
            <th style="width:13%">NUMBER OF SHARES</th>
            <th style="width:16%">PAR/STATED VALUE</th>
            <th style="width:18%">AMOUNT (PhP)</th>
            <th colspan="4"></th>
        </tr>
        @for($i = 0; $i < 3; $i++)
            @php($row = $authorizedRows[$i] ?? [])
            <tr>
                <td>{{ $row['type_of_shares'] ?? '' }}</td>
                <td class="num">{{ $row['number_of_shares'] ?? '' }}</td>
                <td class="num">{{ $row['par_or_stated_value'] ?? '' }}</td>
                <td class="num">{{ $row['amount'] ?? '' }}</td>
                <td colspan="4"></td>
            </tr>
        @endfor
    </table>

    <table class="cap-table">
        <tr>
            <th colspan="7" class="cap-label">SUBSCRIBED CAPITAL - FILIPINO</th>
        </tr>
        <tr>
            <th style="width:12%">NO. OF STOCKHOLDERS</th>
            <th style="width:17%">TYPE OF SHARES *</th>
            <th style="width:12%">NUMBER OF SHARES</th>
            <th style="width:15%">NO. OF SHARES IN PUBLIC **</th>
            <th style="width:14%">PAR/STATED VALUE</th>
            <th style="width:16%">AMOUNT (PhP)</th>
            <th style="width:14%">% OF OWNERSHIP</th>
        </tr>
        @for($i = 0; $i < 2; $i++)
            @php($row = $subscribedFilipinoRows[$i] ?? [])
            <tr>
                <td class="num">{{ $row['no_of_stockholders'] ?? '' }}</td>
                <td>{{ $row['type_of_shares'] ?? '' }}</td>
                <td class="num">{{ $row['number_of_shares'] ?? '' }}</td>
                <td class="num">{{ $row['public_shares'] ?? '' }}</td>
                <td class="num">{{ $row['par_or_stated_value'] ?? '' }}</td>
                <td class="num">{{ $row['amount'] ?? '' }}</td>
                <td class="num">{{ $row['ownership_percent'] ?? '' }}</td>
            </tr>
        @endfor
        <tr>
            <th class="cap-sub" colspan="2">SUBSCRIBED CAPITAL - FOREIGN (INDICATE BY NATIONALITY)</th>
            <th colspan="5"></th>
        </tr>
        <tr>
            <th>NO. OF STOCKHOLDERS</th>
            <th>TYPE OF SHARES *</th>
            <th>NUMBER OF SHARES</th>
            <th>NO. OF SHARES IN PUBLIC **</th>
            <th>PAR/STATED VALUE</th>
            <th>AMOUNT (PhP)</th>
            <th>% OF OWNERSHIP</th>
        </tr>
        @for($i = 0; $i < 2; $i++)
            @php($row = $subscribedForeignRows[$i] ?? [])
            <tr>
                <td class="num">{{ $row['no_of_stockholders'] ?? '' }}</td>
                <td>{{ $row['type_of_shares'] ?? '' }}</td>
                <td class="num">{{ $row['number_of_shares'] ?? '' }}</td>
                <td class="num">{{ $row['public_shares'] ?? '' }}</td>
                <td class="num">{{ $row['par_or_stated_value'] ?? '' }}</td>
                <td class="num">{{ $row['amount'] ?? '' }}</td>
                <td class="num">{{ $row['ownership_percent'] ?? '' }}</td>
            </tr>
        @endfor
        <tr>
            <th colspan="2" class="cap-sub">PERCENTAGE OF FOREIGN EQUITY:</th>
            <td class="num">{{ $step3['percentage_foreign_equity'] ?? '' }}</td>
            <th colspan="2" class="cap-sub">TOTAL SUBSCRIBED CAPITAL (PhP)</th>
            <td class="num" colspan="2">{{ $step3['total_subscribed_capital'] ?? '' }}</td>
        </tr>
    </table>

    <table class="cap-table">
        <tr>
            <th colspan="6" class="cap-label">PAID-UP CAPITAL - FILIPINO</th>
        </tr>
        <tr>
            <th style="width:14%">NO. OF STOCKHOLDERS</th>
            <th style="width:20%">TYPE OF SHARES *</th>
            <th style="width:14%">NUMBER OF SHARES</th>
            <th style="width:16%">PAR/STATED VALUE</th>
            <th style="width:20%">AMOUNT (PhP)</th>
            <th style="width:16%">% OF OWNERSHIP</th>
        </tr>
        @for($i = 0; $i < 2; $i++)
            @php($row = $paidupFilipinoRows[$i] ?? [])
            <tr>
                <td class="num">{{ $row['no_of_stockholders'] ?? '' }}</td>
                <td>{{ $row['type_of_shares'] ?? '' }}</td>
                <td class="num">{{ $row['number_of_shares'] ?? '' }}</td>
                <td class="num">{{ $row['par_or_stated_value'] ?? '' }}</td>
                <td class="num">{{ $row['amount'] ?? '' }}</td>
                <td class="num">{{ $row['ownership_percent'] ?? '' }}</td>
            </tr>
        @endfor
        <tr>
            <th colspan="6" class="cap-sub">PAID-UP CAPITAL - FOREIGN (INDICATE BY NATIONALITY)</th>
        </tr>
        <tr>
            <th>NO. OF STOCKHOLDERS</th>
            <th>TYPE OF SHARES *</th>
            <th>NUMBER OF SHARES</th>
            <th>PAR/STATED VALUE</th>
            <th>AMOUNT (PhP)</th>
            <th>% OF OWNERSHIP</th>
        </tr>
        @for($i = 0; $i < 2; $i++)
            @php($row = $paidupForeignRows[$i] ?? [])
            <tr>
                <td class="num">{{ $row['no_of_stockholders'] ?? '' }}</td>
                <td>{{ $row['type_of_shares'] ?? '' }}</td>
                <td class="num">{{ $row['number_of_shares'] ?? '' }}</td>
                <td class="num">{{ $row['par_or_stated_value'] ?? '' }}</td>
                <td class="num">{{ $row['amount'] ?? '' }}</td>
                <td class="num">{{ $row['ownership_percent'] ?? '' }}</td>
            </tr>
        @endfor
        <tr>
            <th colspan="4" class="cap-sub">TOTAL PAID-UP CAPITAL (PhP)</th>
            <td class="num" colspan="2">{{ $step3['total_paid_up_capital'] ?? '' }}</td>
        </tr>
    </table>

    <div class="small">* Common, Preferred or other classification. ** Other than directors, officers, shareholders owning 10% of outstanding shares.</div>
    <div class="page-no">Page 3 of 9</div>
</div>

<div class="page">
    <p class="title">GENERAL INFORMATION SHEET</p>
    <p class="subtitle">STOCK CORPORATION</p>
    <p class="rule">================================ PLEASE PRINT LEGIBLY ================================</p>

    <div class="section-title">Directors / Officers</div>
    <table>
        <tr>
            <th style="width:30%">Name / Current Residential Address</th>
            <th style="width:10%">Nationality</th>
            <th style="width:6%">INC'R</th>
            <th style="width:6%">BOARD</th>
            <th style="width:6%">Gender</th>
            <th style="width:8%">Stockholder</th>
            <th style="width:12%">Officer</th>
            <th style="width:8%">Exec. Comm.</th>
            <th style="width:14%">TIN</th>
        </tr>
        @foreach($step4 as $row)
            <tr>
                <td>{{ $row['name'] ?? '' }}</td>
                <td>{{ $row['nationality'] ?? '' }}</td>
                <td>{{ $row['incorporator'] ?? '' }}</td>
                <td>{{ $row['board'] ?? '' }}</td>
                <td>{{ $row['gender'] ?? '' }}</td>
                <td>{{ $row['stockholder'] ?? '' }}</td>
                <td>{{ $row['officer'] ?? '' }}</td>
                <td>{{ $row['exec_comm'] ?? '' }}</td>
                <td>{{ $row['tin'] ?? '' }}</td>
            </tr>
        @endforeach
    </table>

    <div class="small">Instruction: For Sex: F/M. For Board: C/M/I. For INC'R and STOCKHOLDER: Y/N.</div>
    <div class="page-no">Page 4 of 9</div>
</div>

<div class="page">
    <p class="title">GENERAL INFORMATION SHEET</p>
    <p class="subtitle">STOCK CORPORATION</p>
    <p class="rule">================================ PLEASE PRINT LEGIBLY ================================</p>
    <table>
        <tr>
            <th style="width:22%">Corporate Name:</th>
            <td>{{ $step1['corporate_name'] ?? '' }}</td>
        </tr>
    </table>

    <div class="section-title">Stockholder's Information (1 to 7)</div>
    <table>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:30%">Name / Nationality / Address</th>
            <th style="width:12%">Type</th>
            <th style="width:10%">No. of Shares</th>
            <th style="width:12%">Amount Subscribed</th>
            <th style="width:10%">% Ownership</th>
            <th style="width:12%">Amount Paid</th>
            <th style="width:10%">TIN</th>
        </tr>
        @foreach($stockRows5 as $row)
            <tr>
                <td>{{ $row['no'] ?? '' }}</td>
                <td>{{ $row['name_address'] ?? '' }} {{ $row['nationality'] ? ' / '.$row['nationality'] : '' }}</td>
                <td>{{ $row['share_type'] ?? '' }}</td>
                <td>{{ $row['number_of_shares'] ?? '' }}</td>
                <td>{{ $row['amount_subscribed'] ?? '' }}</td>
                <td>{{ $row['percent_ownership'] ?? '' }}</td>
                <td>{{ $row['amount_paid'] ?? '' }}</td>
                <td>{{ $row['tin'] ?? '' }}</td>
            </tr>
        @endforeach
    </table>
    <table>
        <tr><th style="width:40%">TOTAL NUMBER OF STOCKHOLDERS</th><td>{{ $step5['total_stockholders'] ?? '' }}</td><th style="width:30%">NO. WITH 100+ SHARES</th><td>{{ $step5['stockholders_with_100_plus'] ?? '' }}</td></tr>
        <tr><th>TOTAL ASSETS (LATEST AUDITED FS)</th><td colspan="3">{{ $step5['total_assets'] ?? '' }}</td></tr>
    </table>

    <div class="page-no">Page 5 of 9</div>
</div>

<div class="page">
    <p class="title">GENERAL INFORMATION SHEET</p>
    <p class="subtitle">STOCK CORPORATION</p>
    <p class="rule">================================ PLEASE PRINT LEGIBLY ================================</p>
    <table>
        <tr>
            <th style="width:22%">Corporate Name:</th>
            <td>{{ $step1['corporate_name'] ?? '' }}</td>
        </tr>
    </table>

    <div class="section-title">Stockholder's Information (8 to 14)</div>
    <table>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:30%">Name / Nationality / Address</th>
            <th style="width:12%">Type</th>
            <th style="width:10%">No. of Shares</th>
            <th style="width:12%">Amount Subscribed</th>
            <th style="width:10%">% Ownership</th>
            <th style="width:12%">Amount Paid</th>
            <th style="width:10%">TIN</th>
        </tr>
        @foreach($stockRows6 as $row)
            <tr>
                <td>{{ $row['no'] ?? '' }}</td>
                <td>{{ $row['name_address'] ?? '' }} {{ $row['nationality'] ? ' / '.$row['nationality'] : '' }}</td>
                <td>{{ $row['share_type'] ?? '' }}</td>
                <td>{{ $row['number_of_shares'] ?? '' }}</td>
                <td>{{ $row['amount_subscribed'] ?? '' }}</td>
                <td>{{ $row['percent_ownership'] ?? '' }}</td>
                <td>{{ $row['amount_paid'] ?? '' }}</td>
                <td>{{ $row['tin'] ?? '' }}</td>
            </tr>
        @endforeach
    </table>
    <table>
        <tr><th style="width:40%">TOTAL NUMBER OF STOCKHOLDERS</th><td>{{ $step5['total_stockholders'] ?? '' }}</td><th style="width:30%">NO. WITH 100+ SHARES</th><td>{{ $step5['stockholders_with_100_plus'] ?? '' }}</td></tr>
        <tr><th>TOTAL ASSETS (LATEST AUDITED FS)</th><td colspan="3">{{ $step5['total_assets'] ?? '' }}</td></tr>
    </table>

    <div class="page-no">Page 6 of 9</div>
</div>

<div class="page">
    <p class="title">GENERAL INFORMATION SHEET</p>
    <p class="subtitle">STOCK CORPORATION</p>
    <p class="rule">================================ PLEASE PRINT LEGIBLY ================================</p>
    <table>
        <tr>
            <th style="width:22%">Corporate Name:</th>
            <td>{{ $step1['corporate_name'] ?? '' }}</td>
        </tr>
    </table>

    <div class="section-title">Stockholder's Information (15 to 21 / Others)</div>
    <table>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:30%">Name / Nationality / Address</th>
            <th style="width:12%">Type</th>
            <th style="width:10%">No. of Shares</th>
            <th style="width:12%">Amount Subscribed</th>
            <th style="width:10%">% Ownership</th>
            <th style="width:12%">Amount Paid</th>
            <th style="width:10%">TIN</th>
        </tr>
        @foreach($stockRows7 as $row)
            <tr>
                <td>{{ $row['no'] ?? '' }}</td>
                <td>{{ $row['name_address'] ?? '' }} {{ $row['nationality'] ? ' / '.$row['nationality'] : '' }}</td>
                <td>{{ $row['share_type'] ?? '' }}</td>
                <td>{{ $row['number_of_shares'] ?? '' }}</td>
                <td>{{ $row['amount_subscribed'] ?? '' }}</td>
                <td>{{ $row['percent_ownership'] ?? '' }}</td>
                <td>{{ $row['amount_paid'] ?? '' }}</td>
                <td>{{ $row['tin'] ?? '' }}</td>
            </tr>
        @endforeach
    </table>
    <table>
        <tr><th style="width:40%">TOTAL NUMBER OF STOCKHOLDERS</th><td>{{ $step5['total_stockholders'] ?? '' }}</td><th style="width:30%">NO. WITH 100+ SHARES</th><td>{{ $step5['stockholders_with_100_plus'] ?? '' }}</td></tr>
        <tr><th>TOTAL ASSETS (LATEST AUDITED FS)</th><td colspan="3">{{ $step5['total_assets'] ?? '' }}</td></tr>
    </table>
    <table>
        <tr><th style="width:40%">OTHERS (remaining stockholders count)</th><td>{{ $step7['others_count'] ?? '' }}</td></tr>
    </table>

    <div class="page-no">Page 7 of 9</div>
</div>

<div class="page">
    <p class="title">GENERAL INFORMATION SHEET</p>
    <p class="subtitle">STOCK CORPORATION</p>
    <p class="rule">PLEASE PRINT LEGIBLY</p>
    <table>
        <tr>
            <th style="width:22%">Corporate Name:</th>
            <td>{{ $step1['corporate_name'] ?? '' }}</td>
        </tr>
    </table>

    <div class="section-title">Investments / Dividends / Licenses / Manpower</div>
    <table>
        <tr><th style="width:35%">Investment of Corporate Funds in Another Corporation - Stocks</th><td>{{ $step8['investment_stocks'] ?? '' }}</td><th>Date of Board Resolution</th><td>{{ $step8['investment_board_resolution_date'] ?? '' }}</td></tr>
        <tr><th>Bonds/Commercial Paper</th><td>{{ $step8['investment_bonds'] ?? '' }}</td><th>Loans/Credits/Advances</th><td>{{ $step8['investment_loans_advances'] ?? '' }}</td></tr>
        <tr><th>Government Treasury Bills</th><td>{{ $step8['investment_treasury_bills'] ?? '' }}</td><th>Others</th><td>{{ $step8['investment_others'] ?? '' }}</td></tr>
        <tr><th>Secondary Purpose Activity</th><td>{{ $step8['secondary_purpose_activity'] ?? '' }}</td><th>Board Resolution / Ratification</th><td>{{ $step8['secondary_purpose_board_resolution_date'] ?? '' }} / {{ $step8['secondary_purpose_ratification_date'] ?? '' }}</td></tr>
        <tr><th>Treasury Shares (No. / %)</th><td>{{ $step8['treasury_shares_count'] ?? '' }} / {{ $step8['treasury_shares_percent'] ?? '' }}</td><th>Retained Earnings</th><td>{{ $step8['retained_earnings'] ?? '' }}</td></tr>
    </table>

    <table>
        <tr><th style="width:22%">Dividend Type</th><th style="width:26%">Amount</th><th style="width:22%">Date Declared</th><th style="width:30%">Notes</th></tr>
        <tr><td>Cash</td><td>{{ $step8['dividend_cash_amount'] ?? '' }}</td><td>{{ $step8['dividend_cash_date'] ?? '' }}</td><td></td></tr>
        <tr><td>Stock</td><td>{{ $step8['dividend_stock_amount'] ?? '' }}</td><td>{{ $step8['dividend_stock_date'] ?? '' }}</td><td></td></tr>
        <tr><td>Property</td><td>{{ $step8['dividend_property_amount'] ?? '' }}</td><td>{{ $step8['dividend_property_date'] ?? '' }}</td><td></td></tr>
    </table>

    <table>
        <tr><th style="width:20%">Additional Shares Issued - Date</th><th style="width:20%">No. of Shares</th><th style="width:20%">Amount</th><th style="width:40%">Secondary License / Registration</th></tr>
        @foreach($additionalShares as $row)
            <tr><td>{{ $row['date'] ?? '' }}</td><td>{{ $row['no_of_shares'] ?? '' }}</td><td>{{ $row['amount'] ?? '' }}</td><td>{{ $step8['agency_name'] ?? '' }} / {{ $step8['secondary_license_type'] ?? '' }}</td></tr>
        @endforeach
        <tr><td colspan="2">Date Issued / Date Started Operations</td><td colspan="2">{{ $step8['secondary_license_date_issued'] ?? '' }} / {{ $step8['secondary_license_date_started'] ?? '' }}</td></tr>
        <tr><td colspan="2">Agency Flags (SEC/BSP/IC)</td><td colspan="2">{{ ($step8['secondary_license_sec'] ?? false) ? 'SEC ' : '' }}{{ ($step8['secondary_license_bsp'] ?? false) ? 'BSP ' : '' }}{{ ($step8['secondary_license_ic'] ?? false) ? 'IC' : '' }}</td></tr>
    </table>

    <table>
        <tr><th>Total Annual Compensation of Directors</th><td>{{ $step8['total_annual_compensation_directors'] ?? '' }}</td><th>Total No. of Officers</th><td>{{ $step8['total_no_officers'] ?? '' }}</td></tr>
        <tr><th>Total No. of Rank & File Employees</th><td>{{ $step8['total_rank_file_employees'] ?? '' }}</td><th>Total Manpower Complement</th><td>{{ $step8['total_manpower_complement'] ?? '' }}</td></tr>
    </table>

    <div class="page-no">Page 8 of 9</div>
</div>

<div class="page" style="font-family:'Times New Roman', Times, serif; color:#000; position:relative; box-sizing:border-box; padding:48pt 54pt 22pt 54pt;">

    <p class="title" style="margin:0; text-align:center; font-size:14pt; font-weight:bold; letter-spacing:0.5pt;">
        GENERAL INFORMATION SHEET
    </p>

    <p class="subtitle" style="margin:4pt 0 18pt 0; text-align:center; font-size:12pt; font-weight:bold;">
        STOCK CORPORATION
    </p>

    <?php
        $certifierName = $step9['certifier_name'] ?? 'Vince Anthony P. Feir';
        $signaturePath = public_path('images/Signature.png');
        $signatureDataUri = null;
        $doneDateRaw = trim((string) ($step9['done_date'] ?? ''));
        $doneDay = trim((string) ($step9['done_day'] ?? ''));
        $doneMonth = trim((string) ($step9['done_month'] ?? ''));
        $doneYear = trim((string) ($step9['done_year'] ?? ''));
        $doneDate = null;

        if (is_file($signaturePath)) {
            $signatureBinary = @file_get_contents($signaturePath);
            if ($signatureBinary !== false) {
                $signatureDataUri = 'data:image/png;base64,'.base64_encode($signatureBinary);
            }
        }

        if ($doneDateRaw !== '') {
            $doneDate = \DateTimeImmutable::createFromFormat('Y-m-d', $doneDateRaw) ?: null;
            if ($doneDate === null) {
                $timestamp = strtotime($doneDateRaw);
                if ($timestamp !== false) {
                    $doneDate = (new \DateTimeImmutable())->setTimestamp($timestamp);
                }
            }
        }

        if ($doneDay === '' && $doneDate instanceof \DateTimeImmutable) {
            $doneDay = $doneDate->format('j');
        }
        if ($doneMonth === '' && $doneDate instanceof \DateTimeImmutable) {
            $doneMonth = $doneDate->format('F');
        }
        if ($doneYear === '' && $doneDate instanceof \DateTimeImmutable) {
            $doneYear = $doneDate->format('Y');
        }
    ?>

    <div style="font-size:10.8pt; line-height:1.22;">

        <p style="margin:0 0 10pt 0; text-align:justify;">
            I, <b style="font-weight:normal; text-decoration:underline;">{{ $certifierName }}</b>, Corporate Secretary of
            <b style="font-weight:normal; text-decoration:underline;">{{ $step1['corporate_name'] ?? '[Corporate Name]' }}</b>, declare under penalty of perjury that all matters set forth in this GIS have been made in good faith, duly verified by me and to the best of my knowledge and belief are true and correct.
        </p>

        <p style="margin:0 0 10pt 0; text-align:justify;">
            I hereby attest that all the information in this GIS are being submitted in compliance with the rules and regulations of the Securities and Exchange Commission (SEC), and that the collection, processing, storage, and sharing of said information are necessary to carry out the functions of public authority for the performance of the constitutionally and statutorily mandated functions of the SEC as a regulatory agency.
        </p>

        <p style="margin:0 0 10pt 0; text-align:justify;">
            I further attest that I have been authorized by the Board of Directors/Trustees to file this GIS with the SEC.
        </p>

        <p style="margin:0 0 10pt 0; text-align:justify;">
            I understand that the Commission may place the corporation under delinquent status for failure to submit reportorial requirements three (3) times, consecutively or intermittently, within a period of five (5) years (Section 177, RA No. 11232).
        </p>

        <p style="margin-top:10pt; margin-bottom:16pt;">
            Done this
            <span style="display:inline-block; min-width:78px; border-bottom:1px solid #000; text-align:center;">{{ $doneDay }}</span>
            day of
            <span style="display:inline-block; min-width:78px; border-bottom:1px solid #000; text-align:center;">{{ $doneMonth }}</span>,
            <span style="display:inline-block; min-width:56px; border-bottom:1px solid #000; text-align:center;">{{ $doneYear }}</span>
           in
            <span style="display:inline-block; min-width:120px; border-bottom:1px solid #000; text-align:center; margin:0 6px;">
            {{ $step9['done_place'] ?? '' }}
            </span>.
        </p>

        <div style="margin:0 0 18pt 0; min-height:48pt; text-align:right;">
            @if($signatureDataUri !== null)
                <img src="{{ $signatureDataUri }}" alt="Signature" style="height:60pt; width:auto; object-fit:contain;">
            @endif
        </div>

        <p style="margin:0; text-align:left;">
            <strong>SUBSCRIBED AND SWORN TO</strong> before me in
            <span style="display:inline-block; min-width:105px; border-bottom:1px solid #000; text-align:center;">{{ $step9['notary_place'] ?? '' }}</span>
            on <span style="display:inline-block; min-width:105px; border-bottom:1px solid #000; text-align:center;">{{ $step9['notary_date'] ?? '' }}</span>
            by affiant who personally appeared before me and
            exhibited to me his/her competent evidence of identity consisting of
            <span style="display:inline-block; min-width:105px; border-bottom:1px solid #000; text-align:center;">{{ $step9['competent_evidence'] ?? '' }}</span>
            issued at
            <span style="display:inline-block; min-width:88px; border-bottom:1px solid #000; text-align:center;">{{ $step9['issued_at'] ?? '' }}</span>
            on
            <span style="display:inline-block; min-width:88px; border-bottom:1px solid #000; text-align:center;">{{ $step9['issued_on'] ?? '' }}</span>.
        </p>

        <div style="margin-top:22pt; text-align:left;">
            NOTARY PUBLIC
        </div>

        <div style="margin-top:8pt; width:235pt; margin-left:0; font-size:10.2pt; line-height:1.2;">
            <div style="margin-bottom:4pt;">DOC. NO. <span style="display:inline-block; min-width:110px; border-bottom:1px solid #000;"></span>;</div>
            <div style="margin-bottom:4pt;">PAGE NO. <span style="display:inline-block; min-width:110px; border-bottom:1px solid #000;"></span>;</div>
            <div style="margin-bottom:4pt;">BOOK NO. <span style="display:inline-block; min-width:110px; border-bottom:1px solid #000;"></span>;</div>
            <div>SERIES OF <span style="display:inline-block; min-width:110px; border-bottom:1px solid #000;"></span>.</div>
        </div>

    </div>



        <div class="page-no">Page 9 of 9</div>


</div>
</body>
</html>
