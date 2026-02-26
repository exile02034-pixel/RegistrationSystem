<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RegistrationUpload;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use ZipArchive;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $latestSubmissionAt = RegistrationUpload::query()
            ->whereHas('registrationLink', fn ($query) => $query->where('email', $user->email))
            ->latest()
            ->value('created_at');

        return Inertia::render('user/Dashboard', [
            'stats' => [
                'totalUploads' => RegistrationUpload::query()
                    ->whereHas('registrationLink', fn ($query) => $query->where('email', $user->email))
                    ->count(),
                'pdfUploads' => RegistrationUpload::query()
                    ->whereHas('registrationLink', fn ($query) => $query->where('email', $user->email))
                    ->where(fn ($query) => $query
                        ->where('mime_type', 'application/pdf')
                        ->orWhere('original_name', 'like', '%.pdf'))
                    ->count(),
                'latestSubmissionAt' => $latestSubmissionAt?->toDateTimeString(),
            ],
        ]);
    }

    public function files(Request $request): Response
    {
        $user = $request->user();

        $uploads = RegistrationUpload::query()
            ->with('registrationLink')
            ->whereHas('registrationLink', fn ($query) => $query->where('email', $user->email))
            ->latest()
            ->get()
            ->map(fn (RegistrationUpload $upload) => [
                'id' => $upload->id,
                'original_name' => $upload->original_name,
                'pdf_name' => pathinfo($upload->original_name, PATHINFO_FILENAME).'.pdf',
                'size_bytes' => $upload->size_bytes,
                'submitted_at' => $upload->created_at?->toDateTimeString(),
                'view_url' => route('user.uploads.view', $upload->id),
                'download_url' => route('user.uploads.download', $upload->id),
                'is_pdf' => $this->isPdfUpload($upload),
            ]);

        return Inertia::render('user/Files', [
            'uploads' => $uploads,
        ]);
    }

    public function downloadUpload(Request $request, RegistrationUpload $upload): HttpResponse
    {
        $user = $request->user();

        abort_unless($upload->registrationLink && $upload->registrationLink->email === $user->email, 403);

        return $this->renderUploadAsPdfResponse(
            $upload,
            asInline: false
        );
    }

    public function viewUpload(Request $request, RegistrationUpload $upload): HttpResponse
    {
        $user = $request->user();

        abort_unless($upload->registrationLink && $upload->registrationLink->email === $user->email, 403);

        return $this->renderUploadAsPdfResponse(
            $upload,
            asInline: true
        );
    }

    private function renderUploadAsPdfResponse(RegistrationUpload $upload, bool $asInline): HttpResponse
    {
        $path = Storage::disk('public')->path($upload->storage_path);
        $pdfFileName = pathinfo($upload->original_name, PATHINFO_FILENAME).'.pdf';
        $disposition = $asInline ? 'inline' : 'attachment';

        if ($this->isPdfUpload($upload)) {
            return response(
                file_get_contents($path),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => $disposition.'; filename="'.$pdfFileName.'"',
                ]
            );
        }

        $pdfContent = $this->convertUploadToPdfBinary($upload, $path);

        return response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => $disposition.'; filename="'.$pdfFileName.'"',
            ]
        );
    }

    private function convertUploadToPdfBinary(RegistrationUpload $upload, string $absolutePath): string
    {
        $extension = Str::lower(pathinfo($upload->original_name, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            return $this->convertImageToPdfBinary($absolutePath);
        }

        if ($extension === 'docx') {
            $text = $this->extractDocxText($absolutePath);

            return $this->buildSimplePdf(
                title: $upload->original_name,
                text: $text ?: 'No readable text found in this DOCX file.'
            );
        }

        return $this->buildSimplePdf(
            title: $upload->original_name,
            text: 'This file was converted to a simple PDF view for client access.'
        );
    }

    private function convertImageToPdfBinary(string $absolutePath): string
    {
        $imagick = new \Imagick($absolutePath);
        $imagick->setImageFormat('pdf');

        return $imagick->getImagesBlob();
    }

    private function extractDocxText(string $absolutePath): ?string
    {
        $zip = new ZipArchive();

        if ($zip->open($absolutePath) !== true) {
            return null;
        }

        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! $documentXml) {
            return null;
        }

        $cleanText = trim(preg_replace('/\s+/', ' ', strip_tags($documentXml)) ?? '');

        return $cleanText !== '' ? $cleanText : null;
    }

    private function buildSimplePdf(string $title, string $text): string
    {
        $lines = preg_split('/\r\n|\r|\n/', wordwrap($title."\n\n".$text, 95, "\n", true)) ?: [];
        $content = "BT\n/F1 11 Tf\n14 TL\n40 800 Td\n";

        foreach ($lines as $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $content .= "({$escaped}) Tj\nT*\n";
        }

        $content .= "ET";
        $length = strlen($content);

        $objects = [];
        $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
        $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
        $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj";
        $objects[] = "4 0 obj << /Length {$length} >> stream\n{$content}\nendstream endobj";
        $objects[] = "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($index = 1; $index <= count($objects); $index++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$index]);
        }

        $pdf .= "trailer << /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefPosition}\n%%EOF";

        return $pdf;
    }

    private function isPdfUpload(RegistrationUpload $upload): bool
    {
        return $upload->mime_type === 'application/pdf'
            || Str::endsWith(Str::lower($upload->original_name), '.pdf');
    }
}
