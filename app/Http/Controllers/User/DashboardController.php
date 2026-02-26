<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RegistrationUpload;
use App\Services\DocumentConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DocumentConversionService $conversionService,
    ) {
    }

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
            ->map(function (RegistrationUpload $upload) {
                $extension = Str::lower(pathinfo($upload->original_name, PATHINFO_EXTENSION));
                $isPdf = $extension === 'pdf';
                $canConvertPdf = in_array($extension, ['doc', 'docx'], true);

                return [
                    'id' => $upload->id,
                    'original_name' => $upload->original_name,
                    'size_bytes' => $upload->size_bytes,
                    'submitted_at' => $upload->created_at?->toDateTimeString(),
                    'view_raw_url' => route('user.uploads.view', ['upload' => $upload->id, 'format' => 'raw']),
                    'preview_pdf_url' => route('user.uploads.view', ['upload' => $upload->id, 'format' => 'pdf', 'strict' => 1]),
                    'download_original_url' => route('user.uploads.download', $upload->id),
                    'download_pdf_url' => route('user.uploads.download', $upload->id).'?format=pdf',
                    'print_url' => route('user.uploads.print', $upload->id),
                    'can_convert_pdf' => $canConvertPdf,
                    'is_pdf' => $isPdf,
                ];
            });

        return Inertia::render('user/Files', [
            'uploads' => $uploads,
            'batchPrintBaseUrl' => route('user.uploads.print-batch'),
        ]);
    }

    public function downloadUpload(Request $request, RegistrationUpload $upload): BinaryFileResponse
    {
        $user = $request->user();

        abort_unless($upload->registrationLink && $upload->registrationLink->email === $user->email, 403);

        $path = Storage::disk('public')->path($upload->storage_path);
        $extension = Str::lower(pathinfo($upload->original_name, PATHINFO_EXTENSION));
        $wantsPdf = $request->query('format') === 'pdf';

        if ($wantsPdf && in_array($extension, ['doc', 'docx'], true)) {
            $pdf = $this->conversionService->convertToPdf($path, $upload->original_name);

            if ($pdf !== null) {
                return response()->download($pdf['path'], $pdf['name'])->deleteFileAfterSend(true);
            }
        }

        return response()->download($path, $upload->original_name);
    }

    public function viewUpload(Request $request, RegistrationUpload $upload): BinaryFileResponse
    {
        $user = $request->user();

        abort_unless($upload->registrationLink && $upload->registrationLink->email === $user->email, 403);

        $path = Storage::disk('public')->path($upload->storage_path);
        $extension = Str::lower(pathinfo($upload->original_name, PATHINFO_EXTENSION));
        $format = $request->query('format', 'raw');
        $strict = (bool) $request->boolean('strict');

        if ($format === 'pdf' && in_array($extension, ['doc', 'docx'], true)) {
            $pdf = $this->conversionService->convertToPdf($path, $upload->original_name);

            if ($pdf !== null) {
                return response()->file($pdf['path'])->deleteFileAfterSend(true);
            }

            abort_if($strict, 422, 'PDF preview is unavailable for this document.');
        }

        if ($format === 'pdf' && $extension === 'pdf') {
            return response()->file($path);
        }

        if ($format === 'pdf') {
            abort_if($strict, 422, 'PDF preview is unavailable for this file type.');
        }

        return response()->file($path);
    }

    public function printUpload(Request $request, RegistrationUpload $upload): \Illuminate\Contracts\View\View
    {
        $user = $request->user();

        abort_unless($upload->registrationLink && $upload->registrationLink->email === $user->email, 403);

        $extension = Str::lower(pathinfo($upload->original_name, PATHINFO_EXTENSION));
        $supportsPdfPrint = in_array($extension, ['doc', 'docx', 'pdf'], true);

        return view('user/print-upload', [
            'fileName' => $upload->original_name,
            'printableUrl' => $supportsPdfPrint
                ? route('user.uploads.view', ['upload' => $upload->id, 'format' => 'pdf', 'strict' => 1])
                : null,
            'errorMessage' => $supportsPdfPrint ? null : 'This file type cannot be printed as PDF.',
        ]);
    }

    public function printBatch(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $request->user();
        $all = $request->boolean('all');
        $idCsv = trim((string) $request->query('ids', ''));
        $ids = $idCsv !== '' ? array_values(array_filter(array_map('intval', explode(',', $idCsv)))) : [];

        $query = RegistrationUpload::query()
            ->with('registrationLink')
            ->whereHas('registrationLink', fn ($q) => $q->where('email', $user->email))
            ->latest();

        if (! $all) {
            $query->whereIn('id', $ids);
        }

        $uploads = $query->get();

        if ($uploads->isEmpty()) {
            return view('user/print-upload', [
                'fileName' => 'Batch Print',
                'printableUrl' => null,
                'errorMessage' => 'No files found for batch printing.',
            ]);
        }

        $pdfPaths = [];
        $tmpGeneratedPaths = [];

        foreach ($uploads as $upload) {
            $path = Storage::disk('public')->path($upload->storage_path);
            $extension = Str::lower(pathinfo($upload->original_name, PATHINFO_EXTENSION));

            if ($extension === 'pdf') {
                $pdfPaths[] = $path;
                continue;
            }

            if (in_array($extension, ['doc', 'docx'], true)) {
                $pdf = $this->conversionService->convertToPdfViaLibreOffice($path, $upload->original_name)
                    ?? $this->conversionService->convertToPdf($path, $upload->original_name);

                if ($pdf !== null) {
                    $pdfPaths[] = $pdf['path'];
                    $tmpGeneratedPaths[] = $pdf['path'];
                }
            }
        }

        if ($pdfPaths === []) {
            return view('user/print-upload', [
                'fileName' => 'Batch Print',
                'printableUrl' => null,
                'errorMessage' => 'No printable PDF output could be generated from selected files.',
            ]);
        }

        $merged = $this->conversionService->mergePdfFiles($pdfPaths, 'batch_print.pdf');

        foreach ($tmpGeneratedPaths as $tmpPath) {
            if (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }

        if ($merged === null) {
            return view('user/print-upload', [
                'fileName' => 'Batch Print',
                'printableUrl' => null,
                'errorMessage' => 'Unable to merge files into a single printable PDF.',
            ]);
        }

        $token = Str::uuid()->toString();
        $targetPath = storage_path('app/tmp-pdf/print_batch_'.$user->id.'_'.$token.'.pdf');
        copy($merged['path'], $targetPath);
        @unlink($merged['path']);

        return view('user/print-upload', [
            'fileName' => 'Batch Print',
            'printableUrl' => route('user.uploads.print-batch.file', ['token' => $token]),
            'errorMessage' => null,
        ]);
    }

    public function printBatchFile(Request $request, string $token): SymfonyResponse
    {
        $user = $request->user();
        $safeToken = preg_replace('/[^a-zA-Z0-9\\-]/', '', $token);
        $path = storage_path('app/tmp-pdf/print_batch_'.$user->id.'_'.$safeToken.'.pdf');

        abort_unless(file_exists($path), 404);

        return response()->file($path)->deleteFileAfterSend(true);
    }
}
