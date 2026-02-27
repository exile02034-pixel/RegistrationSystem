<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\UserDashboardService;
use App\Services\UserFormSubmissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserDashboardService $dashboardService,
        private readonly UserFormSubmissionService $formSubmissionService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('user/Dashboard', [
            'stats' => $this->dashboardService->getStatsForUser($request->user()),
        ]);
    }

    public function aboutMe(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('user/Files', [
            'submissions' => $this->formSubmissionService->getSubmissionsForUser($user),
            'clientInfo' => [
                'name' => $user->name,
                'email' => $user->email,
                'company_types' => $this->formSubmissionService->getCompanyTypesForUser($user)->all(),
            ],
        ]);
    }
}
