<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RegistrationUpload;
use App\Services\UserDashboardService;
use App\Services\UserFileService;
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
        return Inertia::render('user/Files', [
            ...$this->fileService->getFilesForUser($request->user(), $request->only(['sort', 'direction'])),
            'batchPrintBaseUrl' => route('user.uploads.print-batch'),
        ]);
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
