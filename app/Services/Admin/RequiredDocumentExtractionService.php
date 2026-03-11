<?php

namespace App\Services\Admin;

use DateTimeImmutable;
use ZipArchive;

class RequiredDocumentExtractionService
{
    /**
     * @return array<string, string>
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
     * @return array<string, string>
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
     * @return array<string, string>
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
     * @return array<string, string>
     */
    private function extractArticlesOfCorporationFieldsFromText(string $text, string $normalized): array
    {
        $primaryPurpose = $this->extractByPattern(
            $text,
            '/Primary:\s*(.+?)\s*Secondary:/is'
        );

        $payload = array_filter([
            'primary_purpose' => $this->cleanInlineValue($primaryPurpose),
        ], static fn ($value): bool => is_string($value) && trim($value) !== '');

        return array_map(static fn ($value) => trim((string) $value), $payload);
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
