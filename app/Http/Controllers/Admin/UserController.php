<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly AdminUserService $adminUserService,
    ) {}

    public function index(): Response
    {
        $search = trim((string) request('search', ''));
        $sort = (string) request('sort', 'created_at');
        $direction = (string) request('direction', 'desc');
        $companyType = request('company_type');

        return Inertia::render('admin/users/index', $this->adminUserService->indexPageProps(
            search: $search,
            sort: $sort,
            direction: $direction,
            companyType: is_string($companyType) ? $companyType : null,
        ));
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $email = trim((string) $request->query('email', ''));

        if ($email !== '') {
            if (! $this->adminUserService->canCreateFromRegistration($email)) {
                return redirect()
                    ->route('admin.register.index')
                    ->with('error', 'User creation is allowed only for completed registrations.');
            }
        }

        return Inertia::render('admin/users/create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->adminUserService->createUser($request->validated(), $request->user());

        return back()->with('success', 'User created successfully');
    }

    public function show(User $user): Response
    {
        return Inertia::render('admin/users/show', $this->adminUserService->showPageProps($user));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if (! $this->adminUserService->deleteUser($user, $request->user())) {
            return back()->with('error', 'Only client users can be deleted.');
        }

        return back()->with('success', 'User deleted successfully');
    }
}
