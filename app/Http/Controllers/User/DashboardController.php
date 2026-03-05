<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\UserPageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserPageService $userPageService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('user/Dashboard', $this->userPageService->dashboardPageProps($request->user()));
    }

    public function aboutMe(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('user/Files', $this->userPageService->filesPageProps($user));
    }
}
