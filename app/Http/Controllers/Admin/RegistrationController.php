<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendRegistrationLinkRequest;
use App\Models\RegistrationLink;
use App\Models\RegistrationUpload;
use App\Services\DocumentConversionService;
use App\Services\NotificationService;
use App\Services\RegistrationTemplateService;
use App\Services\RegistrationWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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
    ) {
    }

    public function index(): Response
    {
        $search = trim((string) request('search', ''));
        $sort = request('sort') === 'created_at' ? 'created_at' : 'created_at';
        $direction = request('direction') === 'asc' ? 'asc' : 'desc';
        $companyType = request('company_type');

        $links = RegistrationLink::query()
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

        $links->setCollection($links->getCollection()->map(fn (RegistrationLink $link) => [
                'id' => $link->id,
                'email' => $link->email,
                'company_type' => $link->company_type,
                'company_type_label' => $this->templateService->labelFor($link->company_type),
                'status' => $link->status,
                'token' => $link->token,
                'uploads_count' => $link->uploads_count,
                'created_at' => $link->created_at?->toDateTimeString(),
                'client_url' => route('client.registration.show', $link->token),
                'show_url' => route('admin.register.show', $link->id),
            ]));

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

    public function destroy(RegistrationLink $registrationLink): RedirectResponse
    {
        $email = $registrationLink->email;
        $registrationLink->delete();

        $this->notificationService->notifyAdmins(
            category: 'registration_deleted',
            title: 'Registration deleted',
            message: "Registration record for {$email} was deleted.",
            actionUrl: route('admin.register.index'),
            meta: ['email' => $email],
        );

        return redirect()
            ->route('admin.register.index')
            ->with('success', 'Registration deleted successfully.');
    }

    public function show(RegistrationLink $registrationLink): Response
    {
        $registrationLink->load('uploads');

        return Inertia::render('admin/registration/show', [
            'registration' => [
                'id' => $registrationLink->id,
                'email' => $registrationLink->email,
                'token' => $registrationLink->token,
                'company_type_label' => $this->templateService->labelFor($registrationLink->company_type),
                'status' => $registrationLink->status,
                'created_at' => $registrationLink->created_at?->toDateTimeString(),
                'uploads' => $registrationLink->uploads->map(fn (RegistrationUpload $upload) => [
                    'id' => $upload->id,
                    'original_name' => $upload->original_name,
                    'mime_type' => $upload->mime_type,
                    'size_bytes' => $upload->size_bytes,
                    'created_at' => $upload->created_at?->toDateTimeString(),
                    'download_url' => route('admin.register.uploads.download', [$registrationLink->id, $upload->id]),
                    'download_pdf_url' => route('admin.register.uploads.download', [$registrationLink->id, $upload->id]).'?format=pdf',
                    'delete_url' => route('admin.register.uploads.destroy', [$registrationLink->id, $upload->id]),
                    'can_convert_pdf' => in_array(strtolower(pathinfo($upload->original_name, PATHINFO_EXTENSION)), ['doc', 'docx'], true),
                ]),
            ],
        ]);
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

    public function viewUpload(Request $request, RegistrationLink $registrationLink, RegistrationUpload $upload): BinaryFileResponse
    {
        abort_unless($upload->registration_link_id === $registrationLink->id, 404);

        $sourcePath = Storage::disk('public')->path($upload->storage_path);
        $extension = strtolower(pathinfo($upload->original_name, PATHINFO_EXTENSION));
        $format = $request->query('format', 'raw');
        $strict = (bool) $request->boolean('strict');

        if ($format === 'pdf' && in_array($extension, ['doc', 'docx'], true)) {
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
}
