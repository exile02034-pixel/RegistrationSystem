<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationUpload;
use App\Services\Admin\AdminFileService;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
    public function __construct(
        private readonly AdminFileService $fileService,
    ) {}

    public function viewAsPdf(RegistrationUpload $upload): BinaryFileResponse|HttpResponse
    {
        return $this->fileService->streamAsPdf($upload);
    }

    public function downloadDocx(RegistrationUpload $upload): BinaryFileResponse
    {
        return $this->fileService->downloadDocx($upload);
    }

    public function downloadPdf(RegistrationUpload $upload): BinaryFileResponse
    {
        return $this->fileService->downloadPdf($upload);
    }
}
