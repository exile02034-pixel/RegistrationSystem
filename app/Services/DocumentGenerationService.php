<?php

namespace App\Services;

use App\Models\RegistrationGeneratedDocument;
use App\Models\RegistrationLink;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DocumentGenerationService
{
    public const SECRETARY_CERTIFICATE = 'secretary_certificate';

    public const APPOINTMENT_FORM_OPC = 'appointment_form_opc';

    public const GIS_STOCK_CORPORATION = 'gis_stock_corporation';

    public function availableDocuments(): array
    {
        return [
            [
                'type' => self::SECRETARY_CERTIFICATE,
                'name' => "Secretary's Certificate",
                'description' => 'Generate Secretary Certificate PDF from the admin input form.',
            ],
            [
                'type' => self::APPOINTMENT_FORM_OPC,
                'name' => 'Appointment Form - OPC',
                'description' => 'Generate Appointment Form (OPC) PDF from the document form.',
            ],
            [
                'type' => self::GIS_STOCK_CORPORATION,
                'name' => 'General Information Sheet (GIS) - Stock Corporation',
                'description' => 'Generate GIS Stock Corporation PDF (9 sections) from the wizard form.',
            ],
        ];
    }

    public function generate(
        RegistrationLink $registrationLink,
        string $documentType,
        array $fields,
        ?User $generatedBy = null,
    ): RegistrationGeneratedDocument {
        $documentConfig = collect($this->availableDocuments())->firstWhere('type', $documentType);

        if (! is_array($documentConfig)) {
            throw new RuntimeException('Unknown document type.');
        }

        $relativeOutputDir = 'generated/'.$registrationLink->id.'/'.now()->format('Ymd_His').'_'.Str::lower(Str::random(6));
        $absoluteOutputDir = storage_path('app/'.$relativeOutputDir);
        File::ensureDirectoryExists($absoluteOutputDir);

        $slug = Str::slug((string) $documentConfig['name'], '-');
        $pdfRelativePath = "{$relativeOutputDir}/{$slug}.pdf";
        $templateView = $this->templateViewFor($documentType);
        $html = view($templateView, [
            'registrationLink' => $registrationLink,
            'fields' => $fields,
            'generatedAt' => now(),
        ])->render();

        $htmlRelativePath = "{$relativeOutputDir}/{$slug}.html";
        $htmlSaved = Storage::disk('local')->put($htmlRelativePath, $html);
        if ($htmlSaved !== true || ! Storage::disk('local')->exists($htmlRelativePath)) {
            throw new RuntimeException('Failed to write generated document HTML file.');
        }

        $pdfBinary = $this->renderPdfFromHtml($html);
        $pdfSaved = Storage::disk('local')->put($pdfRelativePath, $pdfBinary);
        if ($pdfSaved !== true || ! Storage::disk('local')->exists($pdfRelativePath)) {
            throw new RuntimeException('Failed to write generated PDF file.');
        }

        return RegistrationGeneratedDocument::query()->create([
            'registration_link_id' => $registrationLink->id,
            'generated_by' => $generatedBy?->id,
            'document_type' => $documentType,
            'document_name' => (string) $documentConfig['name'],
            'template_path' => $templateView,
            'filled_file_path' => $htmlRelativePath,
            'pdf_path' => $pdfRelativePath,
            'input_payload' => $fields,
        ]);
    }

    public function delete(RegistrationGeneratedDocument $document): void
    {
        Storage::disk('local')->delete([$document->filled_file_path, $document->pdf_path]);
        $document->delete();
    }

    public function ensurePdfExists(RegistrationGeneratedDocument $document): bool
    {
        $disk = Storage::disk('local');

        if ($disk->exists($document->pdf_path)) {
            return true;
        }

        if (str_ends_with($document->filled_file_path, '.html')) {
            if (! $disk->exists($document->filled_file_path)) {
                return false;
            }

            $html = $disk->get($document->filled_file_path);
            if ($html === '') {
                return false;
            }

            $pdfBinary = $this->renderPdfFromHtml($html);
            $saved = $disk->put($document->pdf_path, $pdfBinary);

            return $saved === true && $disk->exists($document->pdf_path);
        }

        return false;
    }

    private function renderPdfFromHtml(string $html): string
    {
        if (! class_exists(Dompdf::class)) {
            throw new RuntimeException('Dompdf is not installed. Run "composer install" on the running environment.');
        }

        $dompdf = class_exists(Options::class)
            ? new Dompdf($this->dompdfOptions())
            : new Dompdf;

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    private function dompdfOptions(): Options
    {
        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('isRemoteEnabled', false);
        $options->set('isFontSubsettingEnabled', true);

        return $options;
    }

    private function templateViewFor(string $documentType): string
    {
        return match ($documentType) {
            self::SECRETARY_CERTIFICATE => 'pdf.documents.secretary-certificate',
            self::APPOINTMENT_FORM_OPC => 'pdf.documents.appointment-form-opc',
            self::GIS_STOCK_CORPORATION => 'pdf.documents.gis-stock-corporation',
            default => throw new RuntimeException('Unknown document type.'),
        };
    }
}
