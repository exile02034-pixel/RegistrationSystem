<?php

namespace App\Http\Controllers\Admin;

use App\Mail\MissingDocumentsFollowUpMail;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendRegistrationLinkRequest;
use App\Http\Requests\Admin\UpdateRegistrationStatusRequest;
use App\Models\RegistrationLink;
use App\Models\RegistrationUpload;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\DocumentConversionService;
use App\Services\NotificationService;
use App\Services\RegistrationTemplateService;
use App\Services\RegistrationWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly RegistrationWorkflowService $workflowService,
        private readonly DocumentConversionService $conversionService,
        private readonly NotificationService $notificationService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function index(): Response
    {
        $search = trim((string) request('search', ''));
        $sort = request('sort') === 'created_at' ? 'created_at' : 'created_at';
        $direction = request('direction') === 'asc' ? 'asc' : 'desc';
        $companyType = request('company_type');

        $links = RegistrationLink::query()
            ->with(['uploads:id,registration_link_id,original_name'])
            ->withCount('uploads')
            ->when(in_array($companyType, ['corp', 'sole_prop', 'opc'], true), function ($query) use ($companyType) {
                $query->where('company_type', $companyType);
            })
            ->when($search !== '', function ($query) use ($search) {
                $innerSearch = strtolower($search);
                $searchType = match (true) {
                    str_contains($innerSearch, 'opc') => 'opc',
                    str_contains($innerSearch, 'sole') || str_contains($innerSearch, 'prop') || str_contains($innerSearch, 'proprietorship') => 'sole_prop',
                    str_contains($innerSearch, 'corp') || str_contains($innerSearch, 'corporation') || str_contains($innerSearch, 'regular') => 'corp',
                    default => null,
                };

                $query->where(function ($inner) use ($search, $searchType) {
                    $inner
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('token', 'like', "%{$search}%")
                        ->orWhere('company_type', 'like', "%{$search}%");

                    if ($searchType !== null) {
                        $inner->orWhere('company_type', $searchType);
                    }
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        $links->setCollection($links->getCollection()->map(function (RegistrationLink $link): array {
            $missingTemplates = $this->missingTemplates($link);

            return [
                'id' => $link->id,
                'email' => $link->email,
                'company_type' => $link->company_type,
                'company_type_label' => $this->templateService->labelFor($link->company_type),
                'status' => $link->status,
                'token' => $link->token,
                'uploads_count' => $link->uploads_count,
                'missing_documents_count' => count($missingTemplates),
                'has_missing_documents' => count($missingTemplates) > 0,
                'follow_up_url' => route('admin.register.follow-up-missing-documents', $link->id),
                'created_at' => $link->created_at?->toDateTimeString(),
                'client_url' => route('client.registration.show', $link->token),
                'show_url' => route('admin.register.show', $link->id),
            ];
        }));

        return Inertia::render('admin/registration/index', [
            'links' => $links,
            'companyTypes' => $this->templateService->availableCompanyTypes(),
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'company_type' => in_array($companyType, ['corp', 'sole_prop', 'opc'], true) ? $companyType : '',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/registration/create', [
            'companyTypes' => $this->templateService->availableCompanyTypes(),
        ]);
    }

    public function sendLink(SendRegistrationLinkRequest $request): RedirectResponse
    {
        $email = $request->string('email')->toString();
        $companyType = $request->string('company_type')->toString();

        $this->workflowService->createRegistrationLinkAndSend(
            email: $email,
            companyType: $companyType,
        );

        $this->notificationService->notifyAdmins(
            category: 'registration_email_sent',
            title: 'Registration email sent',
            message: "A registration email was sent to {$email}.",
            actionUrl: route('admin.register.index'),
            meta: [
                'email' => $email,
                'company_type' => $companyType,
            ],
        );

        return back()->with('success', 'Registration email sent successfully.');
    }

    public function destroy(Request $request, RegistrationLink $registrationLink): RedirectResponse
    {
        $email = $registrationLink->email;
        $companyType = $registrationLink->company_type;
        $companyTypeLabel = $this->templateService->labelFor($companyType);
        $registrationId = $registrationLink->id;
        $clientName = $this->guessClientNameFromEmail($email);
        $linkedUser = User::query()
            ->where('role', 'user')
            ->where('email', $email)
            ->first();

        $registrationLink->delete();

        $this->notificationService->notifyAdmins(
            category: 'registration_deleted',
            title: 'Registration deleted',
            message: "Registration record for {$email} was deleted.",
            actionUrl: route('admin.register.index'),
            meta: ['email' => $email],
        );

        $this->activityLogService->log(
            type: 'admin.registration.deleted',
            description: "Admin deleted registration of {$clientName} ({$email}) - {$companyTypeLabel}",
            performedBy: $request->user(),
            metadata: [
                'registration_id' => $registrationId,
                'email' => $email,
                'company_type' => $companyType,
                'company_type_label' => $companyTypeLabel,
            ],
        );

        if ($linkedUser && in_array($companyType, ['corp', 'sole_prop', 'opc'], true)) {
            $this->activityLogService->log(
                type: 'admin.user.company_type.removed',
                description: "Admin removed {$companyTypeLabel} from {$linkedUser->name} ({$linkedUser->email})",
                performedBy: $request->user(),
                metadata: [
                    'user_id' => $linkedUser->id,
                    'user_email' => $linkedUser->email,
                    'user_name' => $linkedUser->name,
                    'company_type' => $companyType,
                    'company_type_label' => $companyTypeLabel,
                    'registration_id' => $registrationId,
                ],
            );
        }

        return redirect()
            ->route('admin.register.index')
            ->with('success', 'Registration deleted successfully.');
    }

    public function show(RegistrationLink $registrationLink): Response
    {
        $registrationLink->load('uploads');
        $requiredTemplates = array_values($this->templateService->templatesFor($registrationLink->company_type));
        $missingTemplates = $this->missingTemplates($registrationLink);

        return Inertia::render('admin/registration/show', [
            'registration' => [
                'id' => $registrationLink->id,
                'email' => $registrationLink->email,
                'token' => $registrationLink->token,
                'company_type' => $registrationLink->company_type,
                'company_type_label' => $this->templateService->labelFor($registrationLink->company_type),
                'status' => $registrationLink->status,
                'created_at' => $registrationLink->created_at?->toDateTimeString(),
                'required_documents' => array_values(array_map(fn (array $template) => $template['name'], $requiredTemplates)),
                'missing_documents' => array_values(array_map(fn (array $template) => $template['name'], $missingTemplates)),
                'has_missing_documents' => count($missingTemplates) > 0,
                'follow_up_url' => route('admin.register.follow-up-missing-documents', $registrationLink->id),
                'uploads' => $registrationLink->uploads->map(fn (RegistrationUpload $upload) => [
                    'id' => $upload->id,
                    'original_name' => $upload->original_name,
                    'mime_type' => $upload->mime_type,
                    'size_bytes' => $upload->size_bytes,
                    'created_at' => $upload->created_at?->toDateTimeString(),
                    'view_url' => route('admin.register.uploads.view', [$registrationLink->id, $upload->id]).'?format=pdf&strict=1',
                    'view_pdf_url' => route('admin.register.uploads.view', [$registrationLink->id, $upload->id]).'?format=pdf&strict=1',
                    'download_url' => route('admin.register.uploads.download', [$registrationLink->id, $upload->id]),
                    'download_pdf_url' => route('admin.register.uploads.download', [$registrationLink->id, $upload->id]).'?format=pdf',
                    'delete_url' => route('admin.register.uploads.destroy', [$registrationLink->id, $upload->id]),
                    'can_convert_pdf' => in_array(strtolower(pathinfo($upload->original_name, PATHINFO_EXTENSION)), ['doc', 'docx'], true),
                ]),
            ],
        ]);
    }

    public function sendMissingDocumentsFollowUp(Request $request, RegistrationLink $registrationLink): RedirectResponse
    {
        $missingTemplates = $this->missingTemplates($registrationLink);

        if (count($missingTemplates) === 0) {
            return back()->with('success', 'No missing documents. Follow-up email was not sent.');
        }

        Mail::to($registrationLink->email)->send(new MissingDocumentsFollowUpMail(
            companyTypeLabel: $this->templateService->labelFor($registrationLink->company_type),
            uploadUrl: route('client.registration.show', $registrationLink->token),
            missingDocuments: array_values(array_map(fn (array $template) => $template['name'], $missingTemplates)),
            missingAttachments: $missingTemplates,
        ));

        $this->notificationService->notifyAdmins(
            category: 'registration_missing_documents_follow_up_sent',
            title: 'Missing documents follow-up sent',
            message: "A follow-up email was sent to {$registrationLink->email}.",
            actionUrl: route('admin.register.show', $registrationLink->id),
            meta: [
                'email' => $registrationLink->email,
                'registration_link_id' => $registrationLink->id,
                'missing_documents' => array_values(array_map(fn (array $template) => $template['name'], $missingTemplates)),
            ],
        );

        $this->activityLogService->log(
            type: 'admin.registration.missing_documents.follow_up_sent',
            description: "Admin sent missing documents follow-up to {$registrationLink->email}.",
            performedBy: $request->user(),
            metadata: [
                'registration_id' => $registrationLink->id,
                'email' => $registrationLink->email,
                'missing_documents' => array_values(array_map(fn (array $template) => $template['name'], $missingTemplates)),
            ],
        );

        return back()->with('success', 'Follow-up email sent successfully.');
    }

    public function updateStatus(UpdateRegistrationStatusRequest $request, RegistrationLink $registrationLink): RedirectResponse
    {
        $status = $request->string('status')->toString();

        $registrationLink->update([
            'status' => $status,
        ]);

        return back()->with('success', "Successfully set status to {$status}.");
    }

    public function downloadUpload(Request $request, RegistrationLink $registrationLink, RegistrationUpload $upload): BinaryFileResponse
    {
        abort_unless($upload->registration_link_id === $registrationLink->id, 404);

        $sourcePath = Storage::disk('public')->path($upload->storage_path);
        $extension = strtolower(pathinfo($upload->original_name, PATHINFO_EXTENSION));
        $wantsPdf = $request->query('format') === 'pdf';

        if ($wantsPdf && in_array($extension, ['doc', 'docx'], true)) {
            $pdf = $this->conversionService->convertToPdf($sourcePath, $upload->original_name);

            if ($pdf !== null) {
                return response()->download($pdf['path'], $pdf['name'])->deleteFileAfterSend(true);
            }
        }

        return response()->download($sourcePath, $upload->original_name);
    }

    public function viewUpload(Request $request, RegistrationLink $registrationLink, RegistrationUpload $upload): BinaryFileResponse|HttpResponse
    {
        abort_unless($upload->registration_link_id === $registrationLink->id, 404);

        $sourcePath = Storage::disk('public')->path($upload->storage_path);
        $format = $request->query('format', 'raw');
        if ($format !== 'pdf') {
            return response('Only PDF preview is supported for file viewing.', 422);
        }

        $pdf = $this->conversionService->convertToPdf($sourcePath, $upload->original_name);

        if ($pdf === null) {
            return response('PDF preview could not be generated for this file.', 422);
        }

        return response()->file($pdf['path'])->deleteFileAfterSend(true);
    }

    public function destroyUpload(RegistrationLink $registrationLink, RegistrationUpload $upload): RedirectResponse
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

    private function guessClientNameFromEmail(string $email): string
    {
        $localPart = explode('@', $email)[0] ?? $email;
        $normalized = trim(str_replace(['.', '_', '-'], ' ', $localPart));

        return $normalized !== '' ? ucwords($normalized) : $email;
    }

    /**
     * @return array<int, array{path: string, name: string}>
     */
    private function missingTemplates(RegistrationLink $registrationLink): array
    {
        $uploadedNames = $registrationLink->uploads
            ->map(fn (RegistrationUpload $upload) => $upload->original_name)
            ->all();

        return array_values(array_filter(
            $this->templateService->templatesFor($registrationLink->company_type),
            fn (array $template) => ! in_array($template['name'], $uploadedNames, true),
        ));
    }
}
