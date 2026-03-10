<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: "Times New Roman", serif; font-size: 12pt; margin: 64px; line-height: 1.35; color: #111; }
p, li, div, span, td, th { font-size: 12pt; }
.title { text-align: center; font-weight: 700; text-decoration: underline; margin: 0 0 14px; }
.lead { text-align: justify; text-indent: 34px; margin: 10px 0 0; }
.line { display: inline-block; border-bottom: 1px solid #111; min-width: 180px; padding: 0 6px 2px; line-height: 1.1; vertical-align: bottom; }
ol { margin: 12px 0 0 20px; padding: 0; }
ol li { margin: 0 0 10px; text-align: justify; }
.table-title { margin: 6px 0 2px; font-size: 11pt; }
table { width: 100%; border-collapse: collapse; margin: 4px 0 10px; }
th, td { border: 1px solid #111; padding: 6px 8px; vertical-align: top; }
th { text-align: left; font-weight: 700; background: #f5f5f5; }
.witness { margin-top: 18px; text-align: justify; }
.signature-box { margin: 32px 0 14px; text-align: center; }
.signature-line { display: inline-block; width: 280px; border-bottom: 1px solid #111; height: 20px; }
.corp-sec-label { margin-top: 2px; }
.page-break { page-break-before: always; }
.notary { margin-top: 18px; text-align: justify; }
.notary-meta { margin-top: 10px; line-height: 1.45; }
.blank { display: inline-block; border-bottom: 1px solid #111; min-width: 110px; height: 14px; vertical-align: baseline; }
</style>
</head>

<body>
@php
$secretaryName = trim((string) ($fields['secretary_name'] ?? ''));
$secretaryAddress = trim((string) ($fields['secretary_address'] ?? ''));
$corporationName = trim((string) ($fields['corporation_name'] ?? ''));
$principalAddress = trim((string) ($fields['principal_address'] ?? ''));
$bankName = trim((string) ($fields['bank_name'] ?? ''));
$branch = trim((string) ($fields['branch'] ?? ''));
$corporateSecretaryName = trim((string) ($fields['corporate_secretary_name'] ?? ''));
$certificateLocation = trim((string) ($fields['certificate_location'] ?? ''));

$meetingDateRaw = trim((string) ($fields['meeting_date'] ?? ''));
$certificateDateRaw = trim((string) ($fields['certificate_date'] ?? ''));

$meetingDateDisplay = $meetingDateRaw;
$certificateDay = '';
$certificateMonth = '';
$certificateYear = '';
$normalizeSignatories = static function (mixed $inputRows, string $legacyPrefix) use ($fields): array {
    if (is_array($inputRows) && count($inputRows) > 0) {
        $rows = array_map(static fn ($row) => [
            'name' => trim((string) ($row['name'] ?? '')),
            'position' => trim((string) ($row['position'] ?? '')),
        ], $inputRows);

        return count($rows) > 0 ? $rows : [['name' => '', 'position' => '']];
    }

    $legacyRows = [];
    for ($index = 1; $index <= 20; $index++) {
        $name = trim((string) ($fields["{$legacyPrefix}_{$index}_name"] ?? ''));
        $position = trim((string) ($fields["{$legacyPrefix}_{$index}_position"] ?? ''));
        if ($name === '' && $position === '') {
            continue;
        }

        $legacyRows[] = [
            'name' => $name,
            'position' => $position,
        ];
    }

    return count($legacyRows) > 0 ? $legacyRows : [['name' => '', 'position' => '']];
};
$openingSignatories = $normalizeSignatories($fields['authorized_signatories_for_opening'] ?? null, 'authorized_signatory');
$transactingSignatories = $normalizeSignatories($fields['authorized_signatories_for_transacting'] ?? null, 'withdrawal_signatory');

if ($meetingDateRaw !== '') {
    try {
        $meetingDateDisplay = \Carbon\Carbon::parse($meetingDateRaw)->format('F j, Y');
    } catch (\Throwable) {
    }
}

if ($certificateDateRaw !== '') {
    try {
        $certificateParsed = \Carbon\Carbon::parse($certificateDateRaw);
        $certificateDay = $certificateParsed->format('jS');
        $certificateMonth = $certificateParsed->format('F');
        $certificateYear = $certificateParsed->format('Y');
    } catch (\Throwable) {
    }
}
@endphp

<h3 class="title">SECRETARY'S CERTIFICATE</h3>

<p class="lead">
I, <span class="line" style="min-width: 220px;">{{ $secretaryName }}</span>, of legal age, Filipino and a resident of
<span class="line" style="min-width: 260px;">{{ $secretaryAddress }}</span>, after having been sworn to in accordance with law, hereby depose and state that:
</p>

<ol>
<li>
I am the duly elected and qualified Corporate Secretary of
<span class="line" style="min-width: 240px;">{{ $corporationName }}</span>,
a corporation duly organized and existing under the laws of the Republic of the Philippines, with principal address at
<span class="line" style="min-width: 280px;">{{ $principalAddress }}</span>.
</li>
<li>
In a regular meeting held last <span class="line" style="min-width: 180px;">{{ $meetingDateDisplay }}</span> with all the board of directors present,
it was resolved that <span class="line" style="min-width: 260px;">{{ $bankName }} - {{ $branch }} Branch</span> will be the designated bank and branch
for the Savings Account, Checking Account, Electronic Banking Account, Foreign Deposit Account and any other products
that could be offered by the bank for the Corporation.
</li>
<li>
Resolved further that the following are authorized to process, open and represent the corporation for bank transactions.
<div class="table-title"></div>
<table>
<thead>
<tr>
<th style="width: 38%;">Name</th>
<th style="width: 32%;">Position</th>
<th style="width: 30%;">Signature</th>
</tr>
</thead>
<tbody>
@foreach ($openingSignatories as $signatory)
<tr>
<td>{{ $signatory['name'] }}</td>
<td>{{ $signatory['position'] }}</td>
<td>&nbsp;</td>
</tr>
@endforeach
</tbody>
</table>
</li>
<li>
Resolved further that the authorized signatories for withdrawal, loans and any other bank transaction would be any one between the following:
<div class="table-title"></div>
<table>
<thead>
<tr>
<th style="width: 38%;">Name</th>
<th style="width: 32%;">Position</th>
<th style="width: 30%;">Signature</th>
</tr>
</thead>
<tbody>
@foreach ($transactingSignatories as $signatory)
<tr>
<td>{{ $signatory['name'] }}</td>
<td>{{ $signatory['position'] }}</td>
<td>&nbsp;</td>
</tr>
@endforeach
</tbody>
</table>
</li>
</ol>

<div class="page-break"></div>

<p class="witness">
IN WITNESS WHEREOF, I have hereunto signed this Certificate this
<span class="line" style="min-width: 80px;">{{ $certificateDay }}</span> day of
<span class="line" style="min-width: 140px;">{{ $certificateMonth }}</span>
<span class="line" style="min-width: 90px;">{{ $certificateYear }}</span>
in <span class="line" style="min-width: 150px;">{{ $certificateLocation }}</span>, Philippines.
</p>

<div class="signature-box">
<div class="signature-line">{{ $corporateSecretaryName }}</div>
<div class="corp-sec-label">Corporate Secretary</div>
</div>

<p class="notary">
SUBSCRIBED AND SWORN TO before me this <span class="blank"></span> day of <span class="blank" style="min-width: 140px;"></span> <span class="blank" style="min-width: 80px;"></span>,
affiant exhibiting to me her/his Tax Identification Number (TIN) <span class="blank" style="min-width: 170px;"></span>.
</p>

<div class="notary-meta">
<div>DOC. NO. <span class="blank"></span>;</div>
<div>PAGE NO. <span class="blank"></span>;</div>
<div>BOOK NO. <span class="blank"></span>.</div>
<div>SERIES OF <span class="blank"></span>.</div>
</div>

</body>
</html>
