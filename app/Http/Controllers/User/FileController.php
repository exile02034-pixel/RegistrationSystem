<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RegistrationUpload;
use App\Services\UserFileService;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
    public function __construct(
        private readonly UserFileService $fileService,
    ) {}

    public function viewAsPdf(Request $request, RegistrationUpload $upload): BinaryFileResponse|HttpResponse
    {
        return $this->fileService->streamAsPdfForUser($request->user(), $upload);
    }

    public function downloadDocx(Request $request, RegistrationUpload $upload): BinaryFileResponse
    {
        return $this->fileService->downloadDocxForUser($request->user(), $upload);
    }

    public function downloadPdf(Request $request, RegistrationUpload $upload): BinaryFileResponse
    {
        return $this->fileService->downloadPdfForUser($request->user(), $upload);
    }
}
