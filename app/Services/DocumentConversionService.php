<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use Symfony\Component\Process\Process;
use Throwable;

class DocumentConversionService
{
    public function convertToPdf(string $sourcePath, string $downloadFileName): ?array
    {
        $outputDir = storage_path('app/tmp-pdf');

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $pdfFileName = pathinfo($downloadFileName, PATHINFO_FILENAME).'.pdf';
        $outputPath = $outputDir.'/'.uniqid('template_', true).'_'.$pdfFileName;

        if ($this->convertViaLibreOffice($sourcePath, $outputDir, $outputPath)) {
            return ['path' => $outputPath, 'name' => $pdfFileName];
        }

        if ($this->isDocxFile($sourcePath) && $this->convertViaPhpWord($sourcePath, $outputPath)) {
            return ['path' => $outputPath, 'name' => $pdfFileName];
        }

        return null;
    }

    private function convertViaLibreOffice(string $sourcePath, string $outputDir, string $outputPath): bool
    {
        $binary = $this->detectLibreOfficeBinary();

        if ($binary === null) {
            return false;
        }

        $process = new Process([
            $binary,
            '--headless',
            '--convert-to',
            'pdf:writer_pdf_Export',
            '--outdir',
            $outputDir,
            $sourcePath,
        ]);

        $process->run();

        if (! $process->isSuccessful()) {
            return false;
        }

        $generated = $outputDir.'/'.pathinfo($sourcePath, PATHINFO_FILENAME).'.pdf';

        if (! file_exists($generated)) {
            return false;
        }

        rename($generated, $outputPath);

        return file_exists($outputPath);
    }

    private function convertViaPhpWord(string $sourcePath, string $outputPath): bool
    {
        if (! class_exists(\Dompdf\Dompdf::class)) {
            return false;
        }

        try {
            Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
            Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));

            $phpWord = IOFactory::load($sourcePath, 'Word2007');
            $writer = IOFactory::createWriter($phpWord, 'PDF');
            $writer->save($outputPath);

            return file_exists($outputPath);
        } catch (Throwable) {
            return false;
        }
    }

    private function detectLibreOfficeBinary(): ?string
    {
        foreach (['soffice', 'libreoffice'] as $binary) {
            $process = new Process(['which', $binary]);
            $process->run();

            if ($process->isSuccessful()) {
                return trim($process->getOutput());
            }
        }

        return null;
    }

    private function isDocxFile(string $sourcePath): bool
    {
        return strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION)) === 'docx';
    }
}
