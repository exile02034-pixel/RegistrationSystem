<?php

namespace App\Services;

use App\Mail\ClientUploadReceivedMail;
use App\Mail\RegistrationLinkMail;
use App\Models\RegistrationLink;
use App\Models\RegistrationUpload;
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
    ) {
    }

    public function createRegistrationLinkAndSend(string $email, string $companyType): RegistrationLink
    {
        $link = RegistrationLink::create([
            'email' => $email,
            'token' => Str::random(40),
            'company_type' => $companyType,
            'status' => 'pending',
        ]);

        $registrationUrl = route('client.registration.show', $link->token);
        $templates = $this->templateService->templatesFor($companyType);

        Mail::to($email)->send(new RegistrationLinkMail(
            registrationUrl: $registrationUrl,
            companyTypeLabel: $this->templateService->labelFor($companyType),
            templateAttachments: $templates,
        ));

        return $link;
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function storeClientUploads(RegistrationLink $registrationLink, array $files): void
    {
        $uploadedCount = 0;
        $uploadedFilenames = [];

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
        }

        Mail::to($registrationLink->email)->send(new ClientUploadReceivedMail(
            companyTypeLabel: $this->templateService->labelFor($registrationLink->company_type),
            filesCount: $uploadedCount,
        ));

        $companyTypeLabel = $this->templateService->labelFor($registrationLink->company_type);
        $guestName = $this->guessClientNameFromEmail($registrationLink->email);

        $this->activityLogService->log(
            type: 'client.registration.submitted',
            description: "{$guestName} ({$registrationLink->email}) submitted a registration for {$companyTypeLabel}",
            guestEmail: $registrationLink->email,
            guestName: $guestName,
            role: 'client',
            metadata: [
                'registration_id' => $registrationLink->id,
                'company_type' => $registrationLink->company_type,
                'company_type_label' => $companyTypeLabel,
                'filenames' => $uploadedFilenames,
                'files_count' => $uploadedCount,
            ],
        );
    }

    private function extractText(UploadedFile $file, string $storagePath): ?string
    {
        if (strtolower($file->getClientOriginalExtension()) !== 'docx') {
            return null;
        }

        $absolutePath = Storage::disk('public')->path($storagePath);
        $zip = new ZipArchive();

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
