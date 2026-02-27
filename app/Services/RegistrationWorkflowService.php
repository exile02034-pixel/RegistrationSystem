<?php

namespace App\Services;

use App\Mail\ClientUploadReceivedMail;
use App\Mail\RegistrationLinkMail;
use App\Models\RegistrationLink;
use App\Models\RegistrationUpload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class RegistrationWorkflowService
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly ActivityLogService $activityLogService,
        private readonly RegistrationQrCodeService $qrCodeService,
    ) {}

    public function createRegistrationLinkAndSend(string $email, string $companyType): RegistrationLink
    {
        $link = RegistrationLink::create([
            'email' => $email,
            'token' => Str::random(40),
            'company_type' => $companyType,
            'status' => 'pending',
        ]);

        $registrationUrl = route('registration.form.show', $link->token);
        $qrCodeDataUri = $this->qrCodeService->makeDataUri($registrationUrl);

        Mail::to($email)->send(new RegistrationLinkMail(
            registrationUrl: $registrationUrl,
            companyTypeLabel: $this->templateService->labelFor($companyType),
            qrCodeDataUri: $qrCodeDataUri,
        ));

        return $link;
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function storeClientUploads(RegistrationLink $registrationLink, array $files, ?User $performedBy = null): void
    {
        $uploadedCount = 0;
        $uploadedFilenames = [];
        $uploadedFileTypes = [];

        foreach ($files as $file) {
            $storedName = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
            $storagePath = $file->storeAs('client-uploads/'.$registrationLink->token, $storedName, 'public');
            $originalName = $file->getClientOriginalName();

            RegistrationUpload::create([
                'registration_link_id' => $registrationLink->id,
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'storage_path' => $storagePath,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'extracted_text' => $this->extractText($file, $storagePath),
            ]);

            $uploadedCount++;
            $uploadedFilenames[] = $originalName;
            $uploadedFileTypes[] = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        }

        Mail::to($registrationLink->email)->send(new ClientUploadReceivedMail(
            companyTypeLabel: $this->templateService->labelFor($registrationLink->company_type),
            filesCount: $uploadedCount,
        ));

        $companyTypeLabel = $this->templateService->labelFor($registrationLink->company_type);
        $guestName = $this->guessClientNameFromEmail($registrationLink->email);
        $isAuthenticatedUserUpload = $performedBy !== null && $performedBy->role === 'user';
        $fileSummary = implode(', ', array_slice($uploadedFilenames, 0, 3));
        $hasMore = count($uploadedFilenames) > 3;
        $fileSummaryText = $fileSummary !== '' ? " Files: {$fileSummary}".($hasMore ? ', ...' : '') : '';

        $this->activityLogService->log(
            type: $isAuthenticatedUserUpload ? 'user.files.submitted' : 'client.registration.submitted',
            description: $isAuthenticatedUserUpload
                ? "{$performedBy->name} ({$performedBy->email}) submitted file(s) for {$companyTypeLabel}.{$fileSummaryText}"
                : "{$guestName} ({$registrationLink->email}) submitted a registration for {$companyTypeLabel}.{$fileSummaryText}",
            performedBy: $performedBy,
            guestEmail: $isAuthenticatedUserUpload ? null : $registrationLink->email,
            guestName: $isAuthenticatedUserUpload ? null : $guestName,
            role: $isAuthenticatedUserUpload ? 'user' : 'client',
            metadata: [
                'registration_id' => $registrationLink->id,
                'company_type' => $registrationLink->company_type,
                'company_type_label' => $companyTypeLabel,
                'filenames' => $uploadedFilenames,
                'files_count' => $uploadedCount,
                'file_types' => array_values(array_unique(array_filter($uploadedFileTypes))),
            ],
        );
    }

    private function extractText(UploadedFile $file, string $storagePath): ?string
    {
        if (strtolower($file->getClientOriginalExtension()) !== 'docx') {
            return null;
        }

        $absolutePath = Storage::disk('public')->path($storagePath);
        $zip = new ZipArchive;

        if ($zip->open($absolutePath) !== true) {
            return null;
        }

        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! $documentXml) {
            return null;
        }

        $text = trim(preg_replace('/\s+/', ' ', strip_tags($documentXml)) ?? '');

        return $text !== '' ? $text : null;
    }

    private function guessClientNameFromEmail(string $email): string
    {
        $localPart = explode('@', $email)[0] ?? $email;
        $normalized = trim(str_replace(['.', '_', '-'], ' ', $localPart));

        return $normalized !== '' ? ucwords($normalized) : $email;
    }
}
