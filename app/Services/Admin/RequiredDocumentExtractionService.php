<?php

namespace App\Services\Admin;

use DateTimeImmutable;
use ZipArchive;

class RequiredDocumentExtractionService
{
    /**
     * @return array<string, string>
     */
    public function extractCertificateOfRegistrationFields(string $absolutePath, string $originalFilename): array
    {
        $text = $this->extractText($absolutePath, $originalFilename);
        if ($text === '') {
            return [];
        }

        $normalized = preg_replace('/\s+/', ' ', $text) ?? '';
        if ($normalized === '') {
            return [];
        }

        $registrationDate = $this->extractByPattern(
            $normalized,
            '/(?:date\s+of\s+registration|date\s+registered|registration\s+date)\s*[:\-]?\s*([A-Za-z0-9,\/\-\s]{4,40})/i'
        );
        $secRegistrationNumber = $this->extractByPattern(
            $normalized,
            '/(?:sec\s*(?:registration)?\s*(?:no\.?|number)?|registration\s*(?:no\.?|number)?)\s*[:\-]?\s*([A-Za-z0-9\-]{4,40})/i'
        );
        $tradeName = $this->extractByPattern(
            $normalized,
            '/(?:trade\s*name|business\s*trade\s*name|business\s*name)\s*[:\-]?\s*([^,\n\r]{2,120})/i'
        );
        $registeredAddress = $this->extractByPattern(
            $normalized,
            '/(?:registered\s+address|principal\s+office\s+address|business\s+address)\s*[:\-]?\s*([^,\n\r]{8,220})/i'
        );

        preg_match('/\b(\d{3}-\d{3}-\d{3}(?:-\d{3,4})?)(?:\s*\/\s*([0-9A-Za-z\-]{2,20}))?\b/', $normalized, $tinMatch);
        $tin = trim((string) ($tinMatch[1] ?? ''));
        $branchCode = trim((string) ($tinMatch[2] ?? ''));

        $dateRegistered = $this->normalizeDate($registrationDate);

        $payload = array_filter([
            'date_registered' => $dateRegistered,
            'sec_registration_number' => $secRegistrationNumber,
            'business_trade_name' => $tradeName,
            'registered_address' => $registeredAddress,
            'corporate_tin' => $tin,
            'branch_code' => $branchCode,
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

        // Limit OCR to first 3 pages for speed and to avoid heavy server load.
        if ($this->commandAvailable('pdftoppm')) {
            $cmd = 'pdftoppm -png -f 1 -l 3 -r 250 '
                .$escapedPdfPath.' '.escapeshellarg($tempDir.'/page').' 2>/dev/null';
            @shell_exec($cmd);
            $generated = true;
        } elseif ($this->commandAvailable('mutool')) {
            $cmd = 'mutool draw -F png -r 250 -o '
                .escapeshellarg($tempDir.'/page-%d.png')
                .' '.$escapedPdfPath.' 1-3 2>/dev/null';
            @shell_exec($cmd);
            $generated = true;
        } elseif ($this->commandAvailable('magick')) {
            $cmd = 'magick -density 250 '
                .$escapedPdfPath.'[0-2] '
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
}
