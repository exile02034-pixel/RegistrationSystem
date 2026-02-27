<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RegistrationLink;
use App\Models\RegistrationUpload;
use App\Services\NotificationService;
use App\Services\RegistrationTemplateService;
use App\Services\RegistrationWorkflowService;
use App\Services\UserDashboardService;
use App\Services\UserFileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserDashboardService $dashboardService,
        private readonly UserFileService $fileService,
        private readonly RegistrationTemplateService $templateService,
        private readonly RegistrationWorkflowService $workflowService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('user/Dashboard', [
            'stats' => $this->dashboardService->getStatsForUser($request->user()),
        ]);
    }

    public function files(Request $request): Response
    {
        $user = $request->user();
        $companyTypes = RegistrationLink::query()
            ->where('email', $user->email)
            ->pluck('company_type')
            ->filter(fn ($type) => in_array($type, ['corp', 'sole_prop', 'opc'], true))
            ->unique()
            ->values()
            ->map(fn (string $type) => [
                'value' => $type,
                'label' => $this->templateService->labelFor($type),
            ])
            ->values();

        $uploadTargets = RegistrationLink::query()
            ->where('email', $user->email)
            ->latest()
            ->get()
            ->map(fn (RegistrationLink $link) => [
                'id' => $link->id,
                'company_type_label' => $this->templateService->labelFor($link->company_type),
                'status' => $link->status,
            ])
            ->values();

        return Inertia::render('user/Files', [
            ...$this->fileService->getFilesForUser($user, $request->only(['sort', 'direction'])),
            'batchPrintBaseUrl' => route('user.uploads.print-batch'),
            'uploadTargets' => $uploadTargets,
            'clientInfo' => [
                'name' => $user->name,
                'email' => $user->email,
                'company_types' => $companyTypes,
            ],
        ]);
    }

    public function storeUploads(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'registration_link_id' => ['required', 'integer'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:10240', 'mimes:doc,docx,pdf,jpg,jpeg,png'],
        ]);

        $registrationLink = RegistrationLink::query()
            ->where('id', (int) $validated['registration_link_id'])
            ->where('email', $user->email)
            ->firstOrFail();

        $files = $request->file('files') ?? [];

        $this->workflowService->storeClientUploads($registrationLink, $files, $user);

        $this->notificationService->notifyAdmins(
            category: 'client_files_submitted',
            title: 'Client files submitted',
            message: "{$registrationLink->email} submitted ".count($files).' file(s).',
            actionUrl: route('admin.register.show', $registrationLink->id),
            meta: [
                'email' => $registrationLink->email,
                'registration_link_id' => $registrationLink->id,
                'files_count' => count($files),
            ],
        );

        return back()->with('success', 'Files uploaded successfully.');
    }

    public function downloadUpload(Request $request, RegistrationUpload $upload): BinaryFileResponse
    {
        return $this->fileService->downloadUploadForUser(
            $request->user(),
            $upload,
            $request->query('format') === 'pdf',
        );
    }

    public function viewUpload(Request $request, RegistrationUpload $upload): BinaryFileResponse
    {
        return $this->fileService->viewUploadForUser(
            $request->user(),
            $upload,
            (string) $request->query('format', 'raw'),
            (bool) $request->boolean('strict'),
        );
    }

    public function viewSignedRawUpload(RegistrationUpload $upload): BinaryFileResponse
    {
        return $this->fileService->viewSignedRawUpload($upload);
    }

    public function printUpload(Request $request, RegistrationUpload $upload): \Illuminate\Contracts\View\View
    {
        return view('user/print-upload', $this->fileService->getPrintUploadViewData($request->user(), $upload));
    }

    public function printBatch(Request $request): \Illuminate\Contracts\View\View
    {
        $idCsv = trim((string) $request->query('ids', ''));
        $ids = $idCsv !== '' ? array_values(array_filter(array_map('intval', explode(',', $idCsv)))) : [];

        return view('user/print-upload', $this->fileService->getBatchPrintViewData($request->user(), $ids));
    }

    public function printBatchFile(Request $request, string $token): SymfonyResponse
    {
        $path = $this->fileService->getBatchPrintFilePath($request->user(), $token);
        return response()->file($path)->deleteFileAfterSend(true);
    }
}
