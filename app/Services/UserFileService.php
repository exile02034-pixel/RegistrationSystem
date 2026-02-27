<?php

namespace App\Services;

use App\Models\RegistrationUpload;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserFileService
{
    public function __construct(
        private readonly DocumentConversionService $conversionService,
    ) {
    }

    public function getFilesForUser(User $user, array $input): array
    {
        $filters = $this->normalizeFilters($input);

        $uploads = $this->userUploadsQuery($user)
            ->orderBy($filters['sort'], $filters['direction'])
            ->get()
            ->map(fn (RegistrationUpload $upload) => $this->mapUpload($upload));

        return [
            'uploadGroups' => $this->groupUploads($uploads),
            'filters' => $filters,
        ];
    }

    public function downloadUploadForUser(User $user, RegistrationUpload $upload, bool $wantsPdf): BinaryFileResponse
    {
        $upload = $this->authorizeUpload($user, $upload);
        $path = Storage::disk('public')->path($upload->storage_path);
        $extension = Str::lower(pathinfo($upload->original_name, PATHINFO_EXTENSION));

        if ($wantsPdf && in_array($extension, ['doc', 'docx'], true)) {
            $pdf = $this->conversionService->convertToPdf($path, $upload->original_name);

            if ($pdf !== null) {
                return response()->download($pdf['path'], $pdf['name'])->deleteFileAfterSend(true);
            }
        }

        return response()->download($path, $upload->original_name);
    }

    public function viewUploadForUser(User $user, RegistrationUpload $upload, string $format, bool $strict): BinaryFileResponse
    {
        $upload = $this->authorizeUpload($user, $upload);
        $path = Storage::disk('public')->path($upload->storage_path);
        $extension = Str::lower(pathinfo($upload->original_name, PATHINFO_EXTENSION));

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

        return $this->inlineFileResponse($path, $upload->original_name, $upload->mime_type);
    }

    public function viewSignedRawUpload(RegistrationUpload $upload): BinaryFileResponse
    {
        $path = Storage::disk('public')->path($upload->storage_path);

        return $this->inlineFileResponse($path, $upload->original_name, $upload->mime_type);
    }

    public function getPrintUploadViewData(User $user, RegistrationUpload $upload): array
    {
        $upload = $this->authorizeUpload($user, $upload);
        $extension = Str::lower(pathinfo($upload->original_name, PATHINFO_EXTENSION));
        $supportsPdfPrint = in_array($extension, ['doc', 'docx', 'pdf'], true);

        return [
            'fileName' => $upload->original_name,
            'printableUrl' => $supportsPdfPrint
                ? route('user.uploads.view', ['upload' => $upload->id, 'format' => 'pdf', 'strict' => 1])
                : null,
            'errorMessage' => $supportsPdfPrint ? null : 'This file type cannot be printed as PDF.',
        ];
    }

    public function getBatchPrintViewData(User $user, array $ids): array
    {
        $uploads = $this->userUploadsQuery($user)
            ->whereIn('id', $ids)
            ->latest()
            ->get();

        if ($uploads->isEmpty()) {
            return $this->buildPrintError('No files found for batch printing.');
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
            return $this->buildPrintError('No printable PDF output could be generated from selected files.');
        }

        $merged = $this->conversionService->mergePdfFiles($pdfPaths, 'batch_print.pdf');

        foreach ($tmpGeneratedPaths as $tmpPath) {
            if (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }

        if ($merged === null) {
            return $this->buildPrintError('Unable to merge files into a single printable PDF.');
        }

        $token = Str::uuid()->toString();
        $targetPath = storage_path('app/tmp-pdf/print_batch_'.$user->id.'_'.$token.'.pdf');
        copy($merged['path'], $targetPath);
        @unlink($merged['path']);

        return [
            'fileName' => 'Batch Print',
            'printableUrl' => route('user.uploads.print-batch.file', ['token' => $token]),
            'errorMessage' => null,
        ];
    }

    public function getBatchPrintFilePath(User $user, string $token): string
    {
        $safeToken = preg_replace('/[^a-zA-Z0-9\\-]/', '', $token);
        $path = storage_path('app/tmp-pdf/print_batch_'.$user->id.'_'.$safeToken.'.pdf');

        abort_unless(file_exists($path), 404);

        return $path;
    }

    private function userUploadsQuery(User $user): Builder
    {
        return RegistrationUpload::query()
            ->with('registrationLink')
            ->whereHas('registrationLink', fn ($query) => $query->where('email', $user->email));
    }

    private function mapUpload(RegistrationUpload $upload): array
    {
        $extension = Str::lower(pathinfo($upload->original_name, PATHINFO_EXTENSION));
        $isPdf = $extension === 'pdf';
        $canConvertPdf = in_array($extension, ['doc', 'docx'], true);

        return [
            'id' => $upload->id,
            'original_name' => $upload->original_name,
            'size_bytes' => $upload->size_bytes,
            'submitted_at' => $upload->created_at?->toDateTimeString(),
            'company_type' => (string) ($upload->registrationLink?->company_type ?? ''),
            'view_raw_url' => URL::temporarySignedRoute(
                'user.uploads.view-signed',
                now()->addMinutes(30),
                ['upload' => $upload->id],
            ),
            'preview_pdf_url' => route('user.uploads.view', ['upload' => $upload->id, 'format' => 'pdf', 'strict' => 1]),
            'download_original_url' => route('user.uploads.download', $upload->id),
            'download_pdf_url' => route('user.uploads.download', $upload->id).'?format=pdf',
            'print_url' => route('user.uploads.print', $upload->id),
            'can_convert_pdf' => $canConvertPdf,
            'is_pdf' => $isPdf,
        ];
    }

    private function groupUploads(Collection $uploads): array
    {
        $companyTypes = [
            'opc' => 'OPC',
            'sole_prop' => 'Proprietorship',
            'corp' => 'Regular Corporation',
        ];

        $grouped = $uploads->groupBy('company_type');

        $orderedGroups = collect($companyTypes)
            ->map(function (string $label, string $type) use ($grouped) {
                $typeUploads = $grouped->get($type, collect())->values()->all();

                return [
                    'value' => $type,
                    'label' => $label,
                    'uploads' => $typeUploads,
                ];
            })
            ->filter(fn (array $group) => $group['uploads'] !== [])
            ->values();

        $otherGroups = $grouped
            ->except(array_keys($companyTypes))
            ->map(function (Collection $items, string $type) {
                $fallback = str_replace('_', ' ', trim($type));

                return [
                    'value' => $type,
                    'label' => $fallback !== '' ? $fallback : 'N/A',
                    'uploads' => $items->values()->all(),
                ];
            })
            ->filter(fn (array $group) => $group['uploads'] !== [])
            ->values();

        return $orderedGroups->concat($otherGroups)->all();
    }

    private function normalizeFilters(array $input): array
    {
        return [
            'sort' => 'created_at',
            'direction' => ($input['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc',
        ];
    }

    private function authorizeUpload(User $user, RegistrationUpload $upload): RegistrationUpload
    {
        abort_unless($upload->registrationLink && $upload->registrationLink->email === $user->email, 403);

        return $upload;
    }

    private function buildPrintError(string $message): array
    {
        return [
            'fileName' => 'Batch Print',
            'printableUrl' => null,
            'errorMessage' => $message,
        ];
    }

    private function inlineFileResponse(string $path, string $fileName, ?string $mimeType = null): BinaryFileResponse
    {
        $resolvedMime = $mimeType;

        if (! is_string($resolvedMime) || trim($resolvedMime) === '') {
            $resolvedMime = function_exists('mime_content_type') ? mime_content_type($path) : null;
        }

        $safeName = addcslashes($fileName, "\"\\");

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="'.$safeName.'"',
            'Content-Type' => is_string($resolvedMime) && $resolvedMime !== '' ? $resolvedMime : 'application/octet-stream',
        ]);
    }
}
