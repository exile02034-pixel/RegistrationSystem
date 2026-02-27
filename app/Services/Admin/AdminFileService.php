<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\AdminFileResource;
use App\Models\RegistrationLink;
use App\Models\RegistrationUpload;
use App\Services\DocumentConversionService;
use App\Services\NotificationService;
use App\Services\RegistrationTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminFileService
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly NotificationService $notificationService,
        private readonly DocumentConversionService $conversionService,
    ) {
    }

    public function mapUploadsForUserLinks(Collection $links): array
    {
        $uploads = $links
            ->flatMap(function (RegistrationLink $link) {
                return $link->uploads->map(fn (RegistrationUpload $upload) => [
                    'id' => $upload->id,
                    'registration_link_id' => $link->id,
                    'company_type' => $link->company_type,
                    'company_type_label' => $this->templateService->labelFor($link->company_type),
                    'original_name' => $upload->original_name,
                    'mime_type' => $upload->mime_type,
                    'size_bytes' => $upload->size_bytes,
                    'created_at' => $upload->created_at?->toDateTimeString(),
                    'view_url' => route('admin.register.uploads.view', [$link->id, $upload->id]),
                    'download_url' => route('admin.register.uploads.download', [$link->id, $upload->id]),
                    'download_pdf_url' => route('admin.register.uploads.download', [$link->id, $upload->id]).'?format=pdf',
                    'can_convert_pdf' => $this->canConvertToPdf($upload->original_name),
                ]);
            })
            ->values();

        return AdminFileResource::collection($uploads)->resolve();
    }

    public function mapUploadsForRegistration(RegistrationLink $registrationLink): array
    {
        return AdminFileResource::collection(
            $registrationLink->uploads->map(fn (RegistrationUpload $upload) => [
                'id' => $upload->id,
                'original_name' => $upload->original_name,
                'mime_type' => $upload->mime_type,
                'size_bytes' => $upload->size_bytes,
                'created_at' => $upload->created_at?->toDateTimeString(),
                'download_url' => route('admin.register.uploads.download', [$registrationLink->id, $upload->id]),
                'download_pdf_url' => route('admin.register.uploads.download', [$registrationLink->id, $upload->id]).'?format=pdf',
                'delete_url' => route('admin.register.uploads.destroy', [$registrationLink->id, $upload->id]),
                'can_convert_pdf' => $this->canConvertToPdf($upload->original_name),
            ])
        )->resolve();
    }

    public function downloadUpload(RegistrationLink $registrationLink, RegistrationUpload $upload, bool $wantsPdf): BinaryFileResponse
    {
        abort_unless($upload->registration_link_id === $registrationLink->id, 404);

        $sourcePath = Storage::disk('public')->path($upload->storage_path);

        if ($wantsPdf && $this->canConvertToPdf($upload->original_name)) {
            $pdf = $this->conversionService->convertToPdf($sourcePath, $upload->original_name);

            if ($pdf !== null) {
                return response()->download($pdf['path'], $pdf['name'])->deleteFileAfterSend(true);
            }
        }

        return response()->download($sourcePath, $upload->original_name);
    }

    public function viewUpload(
        RegistrationLink $registrationLink,
        RegistrationUpload $upload,
        string $format,
        bool $strict,
    ): BinaryFileResponse {
        abort_unless($upload->registration_link_id === $registrationLink->id, 404);

        $sourcePath = Storage::disk('public')->path($upload->storage_path);
        $extension = strtolower(pathinfo($upload->original_name, PATHINFO_EXTENSION));

        if ($format === 'pdf' && $this->canConvertToPdf($upload->original_name)) {
            $pdf = $this->conversionService->convertToPdf($sourcePath, $upload->original_name);

            if ($pdf !== null) {
                return response()->file($pdf['path'])->deleteFileAfterSend(true);
            }

            abort_if($strict, 422, 'PDF preview is unavailable for this document.');
        }

        if ($format === 'pdf' && $extension === 'pdf') {
            return response()->file($sourcePath);
        }

        if ($format === 'pdf') {
            abort_if($strict, 422, 'PDF preview is unavailable for this file type.');
        }

        return response()->file($sourcePath);
    }

    public function deleteUpload(RegistrationLink $registrationLink, RegistrationUpload $upload): RedirectResponse
    {
        abort_unless($upload->registration_link_id === $registrationLink->id, 404);

        $fileName = $upload->original_name;
        $email = $registrationLink->email;

        Storage::disk('public')->delete($upload->storage_path);
        $upload->delete();

        $this->notificationService->notifyAdmins(
            category: 'registration_file_deleted',
            title: 'Registration file deleted',
            message: "File {$fileName} was deleted for {$email}.",
            actionUrl: route('admin.register.show', $registrationLink->id),
            meta: [
                'email' => $email,
                'file_name' => $fileName,
            ],
        );

        return back()->with('success', 'File deleted successfully.');
    }

    private function canConvertToPdf(string $fileName): bool
    {
        return in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), ['doc', 'docx'], true);
    }
}
