<?php

namespace App\Services\Admin;

use DateTimeImmutable;
use ZipArchive;

class RequiredDocumentExtractionService
{
    /**
     * @return array<string, mixed>
     */
    public function extractFieldsForDocument(string $documentType, string $absolutePath, string $originalFilename): array
    {
        $text = $this->extractText($absolutePath, $originalFilename);
        if ($text === '') {
            return [];
        }

        $normalized = preg_replace('/\s+/', ' ', $text) ?? '';
        if ($normalized === '') {
            return [];
        }

        return match ($documentType) {
            'certificate_of_registration' => $this->extractCertificateOfRegistrationFieldsFromText($text, $normalized),
            'cover_sheet' => $this->extractCoverSheetFieldsFromText($text, $normalized),
            'articles_of_corporation' => $this->extractArticlesOfCorporationFieldsFromText($text, $normalized),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function extractCertificateOfRegistrationFieldsFromText(string $text, string $normalized): array
    {
        $lines = $this->normalizedLines($text);
        $registrationDate = $this->extractByPattern(
            $text,
            '/REGISTRATION DATE\s+([A-Za-z]+\s+\d{1,2},\s+\d{4})/i'
        );
        $tradeName = $this->extractByPattern(
            $text,
            '/TRADE NAME\s*\d*\s+(.+?)\s+CATEGORY/i'
        );
        $registeredAddress = $this->extractByPattern(
            $text,
            '/REGISTERING ADDRESS\s+(.+?)\s+TAX TYPES/is'
        );

        if (trim($tradeName) === '') {
            $tradeName = $this->nextLineAfter($lines, 'BUSINESS INFORMATION DETAILS');
        }
        if (stripos($tradeName, 'CATEGORY') !== false || stripos($tradeName, 'REGISTRATION DATE') !== false) {
            $tradeName = $this->lineBeforeContaining($lines, 'TRADE NAME');
        }

        if (trim($registrationDate) === '') {
            $tradeLine = $this->lineContaining($lines, 'TRADE NAME');
            if ($tradeLine !== '') {
                $registrationDate = $this->extractByPattern($tradeLine, '/([A-Za-z]+\s+\d{1,2},\s+\d{4})/');
            }
        }

        preg_match('/\b(\d{3}-\d{3}-\d{3}(?:-\d{3,4})?)(?:\s*\/\s*([0-9A-Za-z\-]{2,20}))?\b/', $normalized, $tinMatch);
        $tin = trim((string) ($tinMatch[1] ?? ''));
        $branchCode = trim((string) ($tinMatch[2] ?? ''));

        $dateRegistered = $this->normalizeDate($registrationDate);

        $payload = array_filter([
            'date_registered' => $dateRegistered,
            'business_trade_name' => $this->cleanInlineValue($tradeName),
            'business_address' => $this->cleanInlineValue($registeredAddress),
            'corporate_tin' => $tin,
            'branch_code' => $branchCode,
        ], static fn ($value): bool => is_string($value) && trim($value) !== '');

        return array_map(static fn ($value) => trim((string) $value), $payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractCoverSheetFieldsFromText(string $text, string $normalized): array
    {
        $lines = $this->normalizedLines($text);
        $secRegistrationNumber = $this->extractByPattern(
            $text,
            '/SEC Registration Number\s+([0-9]{8,}(?:-[0-9]{2,4})?)/i'
        );
        $industryClassification = $this->extractByPattern(
            $text,
            '/Industry Description\s+(.+?)\s+Company(?:\'s)?\s+Email/is'
        );
        $corporateName = $this->extractByPattern(
            $text,
            '/COMPANY NAME\s+(.+?)\s+Principal Office/is'
        );
        $principalOfficeAddress = $this->extractByPattern(
            $text,
            '/Principal Office\s*\(.*?\)\s+(.+?)\s+COMPANY INFORMATION/is'
        );

        $officialEmail = $this->extractByPattern(
            $text,
            '/(?:company(?:\'s)?\s+email)\s*[:\-]?\s*([A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,})/i'
        );
        $alternateEmail = $this->extractByPattern(
            $text,
            '/CONTACT PERSON INFORMATION.+?Email Address\s+([A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,})/is'
        );

        $officialMobileRaw = $this->extractByPattern(
            $text,
            '/Company(?:\'s)?\s+Mobile\s+Number\s+([\+\d\-\s\(\)]{7,30})/i'
        );
        $alternateMobileRaw = $this->extractByPattern(
            $text,
            '/CONTACT PERSON INFORMATION.+?Mobile Number\/s\s+([\+\d\-\s\(\)]{7,30})/is'
        );

        if (trim($secRegistrationNumber) === '') {
            $secRegistrationNumber = $this->nextLineAfter($lines, 'SEC Registration Number');
        }
        $secRegistrationNumber = $this->normalizeSecRegistrationNumber($secRegistrationNumber);

        if (trim($corporateName) === '') {
            $corporateName = $this->nextLineAfter($lines, 'COMPANY NAME');
        }

        if (trim($industryClassification) === '') {
            $industryClassification = $this->nextLineAfter($lines, 'Industry Description');
        }
        $industryClassification = preg_replace('/\s+Company(?:\'s)?\b.*$/i', '', $industryClassification) ?? $industryClassification;
        $industryClassification = preg_replace('/\s+Company(?:\'s)?$/i', '', $industryClassification) ?? $industryClassification;

        if (trim($principalOfficeAddress) === '') {
            $principalOfficeAddress = $this->nextLineAfter($lines, 'Principal Office');
        }

        if (trim($officialEmail) === '' || trim($alternateEmail) === '') {
            preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $text, $emails);
            $allEmails = array_values(array_unique(array_map('trim', $emails[0] ?? [])));
            if (trim($officialEmail) === '' && isset($allEmails[0])) {
                $officialEmail = $allEmails[0];
            }
            if (trim($alternateEmail) === '' && isset($allEmails[1])) {
                $alternateEmail = $allEmails[1];
            }
        }

        if (trim($officialMobileRaw) === '' || trim($alternateMobileRaw) === '') {
            preg_match_all('/(?:\+63|0)\d{10}|\b\d{11}\b/', $text, $mobiles);
            $allMobiles = array_values(array_unique(array_map('trim', $mobiles[0] ?? [])));
            $preferred = collect($allMobiles)->first(fn (string $number): bool => str_starts_with($number, '09') || str_starts_with($number, '+639'));
            if (trim($officialMobileRaw) === '' && is_string($preferred) && $preferred !== '') {
                $officialMobileRaw = $preferred;
            } elseif (trim($officialMobileRaw) === '' && isset($allMobiles[0])) {
                $officialMobileRaw = $allMobiles[0];
            }

            $alternateCandidate = collect($allMobiles)->first(fn (string $number): bool => $number !== $officialMobileRaw);
            if (trim($alternateMobileRaw) === '' && is_string($alternateCandidate) && $alternateCandidate !== '') {
                $alternateMobileRaw = $alternateCandidate;
            } elseif (trim($alternateMobileRaw) === '' && isset($allMobiles[1])) {
                $alternateMobileRaw = $allMobiles[1];
            }
        }

        $mobileFromHeader = $this->nextLineAfter($lines, "Company's Mobile Number");
        if ($this->isLikelyPhoneNumber($mobileFromHeader) && (str_starts_with($mobileFromHeader, '09') || str_starts_with($mobileFromHeader, '+639'))) {
            $officialMobileRaw = $mobileFromHeader;
        }

        $payload = array_filter([
            'sec_registration_number' => $this->cleanInlineValue($secRegistrationNumber),
            'industry_classification' => $this->cleanInlineValue(preg_replace('/^[A-Z0-9]+\s+/', '', $industryClassification) ?? $industryClassification),
            'corporate_name' => $this->cleanInlineValue($corporateName),
            'principal_office_address' => $this->cleanInlineValue($principalOfficeAddress),
            'email' => trim($officialEmail),
            'official_mobile' => $this->normalizeMobileStrict($officialMobileRaw),
            'alternate_email' => trim($alternateEmail),
            'alternate_mobile' => $this->normalizeMobileStrict($alternateMobileRaw),
        ], static fn ($value): bool => is_string($value) && trim($value) !== '');

        return array_map(static fn ($value) => trim((string) $value), $payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractArticlesOfCorporationFieldsFromText(string $text, string $normalized): array
    {
        $primaryPurpose = $this->extractByPattern(
            $text,
            '/Primary:\s*(.+?)\s*Secondary:/is'
        );
        $gisStep3 = $this->extractGisStep3CapitalStockFromArticles($text);

        $payload = array_filter([
            'primary_purpose' => $this->cleanInlineValue($primaryPurpose),
        ], static fn ($value): bool => is_string($value) && trim($value) !== '');

        if ($gisStep3 !== []) {
            $payload['gis_step_3'] = $gisStep3;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractGisStep3CapitalStockFromArticles(string $text): array
    {
        $seventhClause = $this->extractClauseText($text, 'seventh', 'eighth');
        $eighthClause = $this->extractClauseText($text, 'eighth', 'ninth');

        if ($seventhClause === '' && $eighthClause === '') {
            return [];
        }

        $authorizedCapital = $this->extractAuthorizedCapitalFromSeventh($seventhClause);
        $authorizedShares = $this->extractAuthorizedSharesFromSeventh($seventhClause);
        $parValue = $this->extractParValueFromSeventh($seventhClause);
        $shareType = $this->extractShareTypeFromSeventh($seventhClause);

        $authorizedAmount = null;
        if ($authorizedShares !== null && $parValue !== null) {
            $authorizedAmount = $authorizedShares * $parValue;
        } elseif ($authorizedCapital !== null) {
            $authorizedAmount = $authorizedCapital;
        }

        $authorizedRows = [];
        if ($authorizedShares !== null || $parValue !== null || $authorizedAmount !== null || $shareType !== '') {
            $authorizedRows[] = array_filter([
                'type_of_shares' => $shareType,
                'number_of_shares' => $authorizedShares !== null ? number_format($authorizedShares, 0, '.', ',') : '',
                'par_or_stated_value' => $parValue !== null ? $this->formatPeso($parValue) : '',
                'amount' => $authorizedAmount !== null ? $this->formatPeso($authorizedAmount) : '',
            ], static fn ($value): bool => is_string($value) ? trim($value) !== '' : $value !== null);
        }

        $subscribers = $this->extractSubscribersFromEighth($eighthClause);
        $totalsFromEighth = $this->extractSubscribedAndPaidTotalsFromEighth($eighthClause);
        $subscribedTotal = $totalsFromEighth['subscribed'] ?? $this->sumNumericField($subscribers, 'amount_subscribed');
        $paidTotal = $totalsFromEighth['paid'] ?? $this->sumNumericField($subscribers, 'amount_paid');

        $filipinoSubscribers = array_values(array_filter(
            $subscribers,
            static fn (array $row): bool => str_contains(strtolower((string) ($row['nationality'] ?? '')), 'filipino')
        ));
        $foreignSubscribers = array_values(array_filter(
            $subscribers,
            static fn (array $row): bool => !str_contains(strtolower((string) ($row['nationality'] ?? '')), 'filipino')
        ));

        $subscribedFilipinoRows = $this->buildStep3SubscribedRows($filipinoSubscribers, $shareType, $parValue, $authorizedShares);
        $subscribedForeignRows = $this->buildStep3SubscribedRows($foreignSubscribers, $shareType, $parValue, $authorizedShares);
        $paidupFilipinoRows = $this->buildStep3PaidupRows($filipinoSubscribers, $shareType, $parValue, $authorizedShares);
        $paidupForeignRows = $this->buildStep3PaidupRows($foreignSubscribers, $shareType, $parValue, $authorizedShares);

        $foreignSubscribed = $this->sumNumericField($foreignSubscribers, 'amount_subscribed');
        $foreignEquityPercent = null;
        if ($subscribedTotal !== null && $subscribedTotal > 0 && $foreignSubscribed !== null) {
            $foreignEquityPercent = ($foreignSubscribed / $subscribedTotal) * 100;
        }

        return array_filter([
            'authorized_capital_stock' => $authorizedCapital !== null ? number_format($authorizedCapital, 2, '.', '') : '',
            'subscribed_capital_stock' => $subscribedTotal !== null ? number_format($subscribedTotal, 2, '.', '') : '',
            'paid_up_capital_stock' => $paidTotal !== null ? number_format($paidTotal, 2, '.', '') : '',
            'authorized_rows' => $authorizedRows,
            'subscribed_filipino_rows' => $subscribedFilipinoRows,
            'subscribed_foreign_rows' => $subscribedForeignRows,
            'paidup_filipino_rows' => $paidupFilipinoRows,
            'paidup_foreign_rows' => $paidupForeignRows,
            'percentage_foreign_equity' => $foreignEquityPercent !== null ? number_format($foreignEquityPercent, 2).'%' : '',
            'total_subscribed_capital' => $subscribedTotal !== null ? $this->formatPeso($subscribedTotal) : '',
            'total_paid_up_capital' => $paidTotal !== null ? $this->formatPeso($paidTotal) : '',
        ], static function ($value): bool {
            if (is_array($value)) {
                return $value !== [];
            }

            return is_string($value) ? trim($value) !== '' : $value !== null;
        });
    }

    private function extractClauseText(string $text, string $startClause, string $nextClause): string
    {
        $pattern = '/\b'.preg_quote($startClause, '/').'\b\s*[:.\-]?\s*(.+?)\s*\b'
            .preg_quote($nextClause, '/').'\b\s*[:.\-]?/is';
        if (preg_match($pattern, $text, $matches) === 1) {
            return trim((string) ($matches[1] ?? ''));
        }

        $fallbackPattern = '/\b'.preg_quote($startClause, '/').'\b\s*[:.\-]?\s*(.+)$/is';
        if (preg_match($fallbackPattern, $text, $fallbackMatches) !== 1) {
            return '';
        }

        $tail = (string) ($fallbackMatches[1] ?? '');
        $tail = preg_replace('/\b(ninth|tenth|in witness whereof)\b.*$/is', '', $tail) ?? $tail;

        return trim($tail);
    }

    private function extractAuthorizedCapitalFromSeventh(string $seventhClause): ?float
    {
        if ($seventhClause === '') {
            return null;
        }

        if (preg_match('/capital\s+stock[^P₱]*(?:₱|PHP|P)\s*([0-9][0-9,]*(?:\.[0-9]{2})?)/i', $seventhClause, $matches) === 1) {
            return $this->parseNumber((string) ($matches[1] ?? ''));
        }

        if (preg_match('/(?:₱|PHP|P)\s*([0-9][0-9,]*(?:\.[0-9]{2})?)/i', $seventhClause, $fallback) === 1) {
            return $this->parseNumber((string) ($fallback[1] ?? ''));
        }

        return null;
    }

    private function extractAuthorizedSharesFromSeventh(string $seventhClause): ?float
    {
        if ($seventhClause === '') {
            return null;
        }

        if (preg_match('/\b([0-9][0-9,]*)\s+shares?\b/i', $seventhClause, $matches) === 1) {
            return $this->parseNumber((string) ($matches[1] ?? ''));
        }

        if (preg_match('/\(\s*([0-9][0-9,]*)\s*\)\s*shares?/i', $seventhClause, $fallback) === 1) {
            return $this->parseNumber((string) ($fallback[1] ?? ''));
        }

        return null;
    }

    private function extractParValueFromSeventh(string $seventhClause): ?float
    {
        if ($seventhClause === '') {
            return null;
        }

        if (preg_match('/par\s+value\s+of[^P₱]*(?:₱|PHP|P)\s*([0-9][0-9,]*(?:\.[0-9]{2})?)/i', $seventhClause, $matches) === 1) {
            return $this->parseNumber((string) ($matches[1] ?? ''));
        }

        if (preg_match('/(?:₱|PHP|P)\s*([0-9][0-9,]*(?:\.[0-9]{2})?)\s+per\s+share/i', $seventhClause, $fallback) === 1) {
            return $this->parseNumber((string) ($fallback[1] ?? ''));
        }

        return null;
    }

    private function extractShareTypeFromSeventh(string $seventhClause): string
    {
        if ($seventhClause === '') {
            return '';
        }

        if (preg_match('/\b(common|preferred)([^.,;\n]*?)shares?\b/i', $seventhClause, $matches) === 1) {
            return $this->cleanInlineValue(trim((string) ($matches[0] ?? '')));
        }

        if (preg_match('/\b([a-z][a-z\-\s]{2,60})\s+shares?\b/i', $seventhClause, $fallback) === 1) {
            return $this->cleanInlineValue(trim((string) ($fallback[1] ?? '')).' shares');
        }

        return '';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractSubscribersFromEighth(string $eighthClause): array
    {
        if ($eighthClause === '') {
            return [];
        }

        $rows = [];
        $lines = $this->normalizedLines($eighthClause);

        foreach ($lines as $line) {
            if (stripos($line, 'total') === 0) {
                continue;
            }

            if (preg_match(
                '/^(.+?)\s+(Filipino|[A-Z][a-z]+(?:\s+[A-Z][a-z]+){0,2})\s+([0-9][0-9,]*)\s+(?:₱|PHP|P)?\s*([0-9][0-9,]*(?:\.[0-9]{2})?)(?:\s+(?:₱|PHP|P)?\s*([0-9][0-9,]*(?:\.[0-9]{2})?))?$/i',
                $line,
                $matches
            ) !== 1) {
                continue;
            }

            $shares = $this->parseNumber((string) ($matches[3] ?? ''));
            $amountSubscribed = $this->parseNumber((string) ($matches[4] ?? ''));
            $amountPaid = $this->parseNumber((string) ($matches[5] ?? ''));

            $rows[] = array_filter([
                'name' => $this->normalizePersonName((string) ($matches[1] ?? '')),
                'nationality' => trim((string) ($matches[2] ?? '')),
                'shares' => $shares,
                'amount_subscribed' => $amountSubscribed,
                'amount_paid' => $amountPaid,
            ], static fn ($value): bool => $value !== null && $value !== '');
        }

        return $rows;
    }

    /**
     * @return array{subscribed:?float, paid:?float}
     */
    private function extractSubscribedAndPaidTotalsFromEighth(string $eighthClause): array
    {
        if ($eighthClause === '') {
            return ['subscribed' => null, 'paid' => null];
        }

        if (preg_match(
            '/\bTOTAL\b[^0-9P₱]*(?:₱|PHP|P)?\s*([0-9][0-9,]*(?:\.[0-9]{2})?)(?:[^0-9P₱]+(?:₱|PHP|P)?\s*([0-9][0-9,]*(?:\.[0-9]{2})?))?/i',
            $eighthClause,
            $matches
        ) === 1) {
            return [
                'subscribed' => $this->parseNumber((string) ($matches[1] ?? '')),
                'paid' => $this->parseNumber((string) ($matches[2] ?? '')),
            ];
        }

        return ['subscribed' => null, 'paid' => null];
    }

    /**
     * @param array<int, array<string, mixed>> $subscribers
     * @return array<int, array<string, string>>
     */
    private function buildStep3SubscribedRows(
        array $subscribers,
        string $shareType,
        ?float $parValue,
        ?float $authorizedShares,
    ): array {
        if ($subscribers === []) {
            return [];
        }

        $shares = $this->sumNumericField($subscribers, 'shares');
        $amount = $this->sumNumericField($subscribers, 'amount_subscribed');
        $ownership = null;
        if ($shares !== null && $authorizedShares !== null && $authorizedShares > 0) {
            $ownership = ($shares / $authorizedShares) * 100;
        }

        return [[
            'no_of_stockholders' => (string) count($subscribers),
            'type_of_shares' => $shareType,
            'number_of_shares' => $shares !== null ? number_format($shares, 0, '.', ',') : '',
            'public_shares' => '',
            'par_or_stated_value' => $parValue !== null ? $this->formatPeso($parValue) : '',
            'amount' => $amount !== null ? $this->formatPeso($amount) : '',
            'ownership_percent' => $ownership !== null ? number_format($ownership, 2) : '',
        ]];
    }

    /**
     * @param array<int, array<string, mixed>> $subscribers
     * @return array<int, array<string, string>>
     */
    private function buildStep3PaidupRows(
        array $subscribers,
        string $shareType,
        ?float $parValue,
        ?float $authorizedShares,
    ): array {
        if ($subscribers === []) {
            return [];
        }

        $shares = $this->sumNumericField($subscribers, 'shares');
        $amount = $this->sumNumericField($subscribers, 'amount_paid');
        $ownership = null;
        if ($shares !== null && $authorizedShares !== null && $authorizedShares > 0) {
            $ownership = ($shares / $authorizedShares) * 100;
        }

        return [[
            'no_of_stockholders' => (string) count($subscribers),
            'type_of_shares' => $shareType,
            'number_of_shares' => $shares !== null ? number_format($shares, 0, '.', ',') : '',
            'par_or_stated_value' => $parValue !== null ? $this->formatPeso($parValue) : '',
            'amount' => $amount !== null ? $this->formatPeso($amount) : '',
            'ownership_percent' => $ownership !== null ? number_format($ownership, 2) : '',
        ]];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function sumNumericField(array $rows, string $field): ?float
    {
        $sum = 0.0;
        $hasValue = false;

        foreach ($rows as $row) {
            $value = $row[$field] ?? null;
            if (! is_numeric($value)) {
                continue;
            }

            $sum += (float) $value;
            $hasValue = true;
        }

        return $hasValue ? $sum : null;
    }

    private function parseNumber(string $value): ?float
    {
        $clean = preg_replace('/[^0-9.]/', '', $value) ?? '';
        if ($clean === '' || ! is_numeric($clean)) {
            return null;
        }

        return (float) $clean;
    }

    private function formatPeso(float $value): string
    {
        return 'P'.number_format($value, 2, '.', ',');
    }

    private function normalizePersonName(string $name): string
    {
        $cleaned = $this->cleanInlineValue($name);
        if ($cleaned === '') {
            return '';
        }

        if (str_contains($cleaned, ',')) {
            return $cleaned;
        }

        $parts = preg_split('/\s+/', $cleaned) ?: [];
        if (count($parts) < 2) {
            return $cleaned;
        }

        $lastName = array_pop($parts);
        if ($lastName === null) {
            return $cleaned;
        }

        return trim($lastName.', '.implode(' ', $parts));
    }

    private function extractText(string $absolutePath, string $originalFilename): string
    {
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));

        return match ($extension) {
            'txt' => $this->safeFileContents($absolutePath),
            'docx' => $this->extractDocxText($absolutePath),
            'pdf' => $this->extractPdfText($absolutePath),
            default => $this->safeFileContents($absolutePath),
        };
    }

    private function extractPdfText(string $absolutePath): string
    {
        $path = escapeshellarg($absolutePath);

        $fromPdfToText = @shell_exec("pdftotext -layout -q {$path} - 2>/dev/null");
        if (is_string($fromPdfToText) && trim($fromPdfToText) !== '') {
            return $fromPdfToText;
        }

        $fromMuTool = @shell_exec("mutool draw -F txt -i {$path} 2>/dev/null");
        if (is_string($fromMuTool) && trim($fromMuTool) !== '') {
            return $fromMuTool;
        }

        $fromOcr = $this->extractPdfTextViaOcr($absolutePath);
        if ($fromOcr !== '') {
            return $fromOcr;
        }

        $raw = $this->safeFileContents($absolutePath);
        if ($raw === '') {
            return '';
        }

        $decoded = preg_replace('/[^[:print:]\r\n\t]/', ' ', $raw) ?? '';

        return trim($decoded);
    }

    private function extractDocxText(string $absolutePath): string
    {
        $zip = new ZipArchive;
        if ($zip->open($absolutePath) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        if ($xml === '') {
            return '';
        }

        $text = strip_tags($xml);

        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function safeFileContents(string $absolutePath): string
    {
        $contents = @file_get_contents($absolutePath);

        return is_string($contents) ? $contents : '';
    }

    private function extractPdfTextViaOcr(string $absolutePath): string
    {
        if (! $this->commandAvailable('tesseract')) {
            return '';
        }

        $tempDir = $this->createTempDir();
        if ($tempDir === '') {
            return '';
        }

        $escapedPdfPath = escapeshellarg($absolutePath);
        $generated = false;

        if ($this->commandAvailable('pdftoppm')) {
            $cmd = 'pdftoppm -png -r 250 '
                .$escapedPdfPath.' '.escapeshellarg($tempDir.'/page').' 2>/dev/null';
            @shell_exec($cmd);
            $generated = true;
        } elseif ($this->commandAvailable('mutool')) {
            $cmd = 'mutool draw -F png -r 250 -o '
                .escapeshellarg($tempDir.'/page-%d.png')
                .' '.$escapedPdfPath.' 1-end 2>/dev/null';
            @shell_exec($cmd);
            $generated = true;
        } elseif ($this->commandAvailable('magick')) {
            $cmd = 'magick -density 250 '
                .$escapedPdfPath.' '
                .escapeshellarg($tempDir.'/page-%d.png')
                .' 2>/dev/null';
            @shell_exec($cmd);
            $generated = true;
        }

        if (! $generated) {
            $this->cleanupTempDir($tempDir);

            return '';
        }

        $images = glob($tempDir.'/page*.png') ?: [];
        sort($images);

        $textChunks = [];
        foreach ($images as $imagePath) {
            $ocrText = @shell_exec('tesseract '.escapeshellarg($imagePath).' stdout --dpi 250 2>/dev/null');
            if (is_string($ocrText) && trim($ocrText) !== '') {
                $textChunks[] = $ocrText;
            }
        }

        $this->cleanupTempDir($tempDir);

        return trim(implode("\n", $textChunks));
    }

    private function commandAvailable(string $command): bool
    {
        $result = @shell_exec('command -v '.escapeshellarg($command).' 2>/dev/null');

        return is_string($result) && trim($result) !== '';
    }

    private function createTempDir(): string
    {
        try {
            $suffix = bin2hex(random_bytes(6));
        } catch (\Throwable) {
            $suffix = uniqid('', true);
        }

        $dir = rtrim(sys_get_temp_dir(), '/').'/reg_doc_ocr_'.$suffix;

        return @mkdir($dir, 0777, true) ? $dir : '';
    }

    private function cleanupTempDir(string $tempDir): void
    {
        $files = glob($tempDir.'/*') ?: [];
        foreach ($files as $file) {
            @unlink($file);
        }

        @rmdir($tempDir);
    }

    private function extractByPattern(string $text, string $pattern): string
    {
        preg_match($pattern, $text, $matches);

        return isset($matches[1]) ? trim((string) $matches[1]) : '';
    }

    private function normalizeDate(string $value): string
    {
        $raw = trim($value);
        if ($raw === '') {
            return '';
        }

        $timestamp = strtotime($raw);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        try {
            return (new DateTimeImmutable($raw))->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }

    private function normalizePhone(string $value): string
    {
        $raw = trim($value);
        if ($raw === '') {
            return '';
        }

        preg_match('/\+?\d[\d\-\s\(\)]{6,20}\d/', $raw, $matches);

        return trim((string) ($matches[0] ?? $raw));
    }

    private function normalizeMobileStrict(string $value): string
    {
        $normalized = $this->normalizePhone($value);
        $digitsOnly = preg_replace('/\D+/', '', $normalized) ?? '';

        if (str_starts_with($normalized, '+639') && strlen($digitsOnly) === 12) {
            return $normalized;
        }

        if (str_starts_with($normalized, '09') && strlen($digitsOnly) === 11) {
            return $normalized;
        }

        return '';
    }

    private function normalizeSecRegistrationNumber(string $value): string
    {
        $raw = trim($value);
        if ($raw === '') {
            return '';
        }

        preg_match('/\b(\d{8,}(?:-\d{2,4})?)\b/', $raw, $match);

        return trim((string) ($match[1] ?? $raw));
    }

    private function isLikelyPhoneNumber(string $value): bool
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return strlen($digits) >= 10 && strlen($digits) <= 12;
    }

    private function cleanInlineValue(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? '';

        return trim($normalized, " \t\n\r\0\x0B.;,:");
    }

    /**
     * @return array<int, string>
     */
    private function normalizedLines(string $text): array
    {
        $lines = preg_split('/\R/', $text) ?: [];

        return array_values(array_filter(array_map(
            fn (string $line): string => trim(preg_replace('/\s+/', ' ', $line) ?? ''),
            $lines
        ), fn (string $line): bool => $line !== ''));
    }

    /**
     * @param array<int, string> $lines
     */
    private function nextLineAfter(array $lines, string $label): string
    {
        $labelLower = strtolower($label);
        $count = count($lines);

        for ($index = 0; $index < $count; $index++) {
            if (! str_contains(strtolower($lines[$index]), $labelLower)) {
                continue;
            }

            for ($next = $index + 1; $next < $count; $next++) {
                $value = trim($lines[$next]);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return '';
    }

    /**
     * @param array<int, string> $lines
     */
    private function lineContaining(array $lines, string $label): string
    {
        $labelLower = strtolower($label);

        foreach ($lines as $line) {
            if (str_contains(strtolower($line), $labelLower)) {
                return $line;
            }
        }

        return '';
    }

    /**
     * @param array<int, string> $lines
     */
    private function lineBeforeContaining(array $lines, string $label): string
    {
        $labelLower = strtolower($label);
        $count = count($lines);

        for ($index = 0; $index < $count; $index++) {
            if (! str_contains(strtolower($lines[$index]), $labelLower)) {
                continue;
            }

            for ($prev = $index - 1; $prev >= 0; $prev--) {
                $value = trim($lines[$prev]);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return '';
    }
}
