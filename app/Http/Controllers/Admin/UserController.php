<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\ActivityLog;
use App\Models\RegistrationLink;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use App\Services\RegistrationTemplateService;
use App\Services\UserFormSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly NotificationService $notificationService,
        private readonly ActivityLogService $activityLogService,
        private readonly UserFormSubmissionService $formSubmissionService,
    ) {}

    public function index(): Response
    {
        $search = trim((string) request('search', ''));
        $sort = request('sort') === 'created_at' ? 'created_at' : 'created_at';
        $direction = request('direction') === 'asc' ? 'asc' : 'desc';
        $companyType = request('company_type');

        $users = User::query()
            ->where('role', 'user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($companyType, ['corp', 'sole_prop', 'opc'], true), function ($query) use ($companyType) {
                $query->whereExists(function ($subQuery) use ($companyType) {
                    $subQuery->selectRaw('1')
                        ->from('registration_links')
                        ->whereColumn('registration_links.email', 'users.email')
                        ->where('registration_links.status', 'completed')
                        ->where('registration_links.company_type', $companyType);
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        $linkStatsByEmail = RegistrationLink::query()
            ->with('formSubmission')
            ->whereIn('email', $users->getCollection()->pluck('email')->filter()->values())
            ->latest()
            ->get()
            ->groupBy('email');

        $users->setCollection(
            $users->getCollection()->map(function (User $user) use ($linkStatsByEmail) {
                $emailLinks = $linkStatsByEmail->get($user->email, collect());
                $submittedFormsCount = $emailLinks->filter(fn (RegistrationLink $link) => $link->formSubmission !== null)->count();
                $assignedTypeValues = $emailLinks
                    ->where('status', 'completed')
                    ->pluck('company_type')
                    ->filter(fn ($type) => in_array($type, ['corp', 'sole_prop', 'opc'], true))
                    ->unique()
                    ->take(3)
                    ->values()
                    ->all();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at?->toDateTimeString(),
                    'company_types' => array_map(
                        fn (string $type) => [
                            'value' => $type,
                            'label' => $this->templateService->labelFor($type),
                        ],
                        $assignedTypeValues
                    ),
                    'company_type_values' => $assignedTypeValues,
                    'submissions_count' => $submittedFormsCount,
                    'show_url' => route('admin.user.show', $user->id),
                ];
            })
        );

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'eligibleClients' => $this->eligibleClients(),
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'company_type' => in_array($companyType, ['corp', 'sole_prop', 'opc'], true) ? $companyType : '',
            ],
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $email = trim((string) $request->query('email', ''));

        if ($email !== '') {
            $canCreateFromRegistration = RegistrationLink::query()
                ->where('email', $email)
                ->where('status', 'completed')
                ->exists();

            if (! $canCreateFromRegistration) {
                return redirect()
                    ->route('admin.register.index')
                    ->with('error', 'User creation is allowed only for completed registrations.');
            }
        }

        return Inertia::render('admin/users/create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        $this->notificationService->notifyAdmins(
            category: 'user_created',
            title: 'User created',
            message: "User {$user->name} ({$user->email}) was created.",
            actionUrl: route('admin.user.show', $user->id),
            meta: [
                'user_id' => $user->id,
                'email' => $user->email,
            ],
        );

        $this->activityLogService->log(
            type: 'admin.user.created',
            description: "Admin created user {$user->name} ({$user->email})",
            performedBy: $request->user(),
            metadata: [
                'user_id' => $user->id,
                'email' => $user->email,
            ],
        );

        $assignedTypeValues = RegistrationLink::query()
            ->where('email', $user->email)
            ->where('status', 'completed')
            ->pluck('company_type')
            ->filter(fn ($type) => in_array($type, ['corp', 'sole_prop', 'opc'], true))
            ->unique()
            ->take(3)
            ->values();

        foreach ($assignedTypeValues as $type) {
            $this->activityLogService->log(
                type: 'admin.user.company_type.assigned',
                description: "Admin assigned {$this->templateService->labelFor($type)} to {$user->name} ({$user->email})",
                performedBy: $request->user(),
                metadata: [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                    'company_type' => $type,
                    'company_type_label' => $this->templateService->labelFor($type),
                ],
            );
        }

        return back()->with('success', 'User created successfully');
    }

    public function show(User $user): Response
    {
        abort_unless($user->role === 'user', 404);

        $links = RegistrationLink::query()
            ->where('email', $user->email)
            ->latest()
            ->get();

        $assignedTypes = $links
            ->where('status', 'completed')
            ->pluck('company_type')
            ->filter(fn ($type) => in_array($type, ['corp', 'sole_prop', 'opc'], true))
            ->unique()
            ->take(3)
            ->values();

        return Inertia::render('admin/users/show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->email_verified_at ? 'Verified' : 'Pending Verification',
                'created_at' => $user->created_at?->toDateTimeString(),
                'company_types' => $assignedTypes
                    ->map(fn (string $type) => [
                        'value' => $type,
                        'label' => $this->templateService->labelFor($type),
                    ])->values(),
            ],
            'submissions' => $this->formSubmissionService->getSubmissionsByEmail($user->email),
            'activities' => ActivityLog::query()
                ->where(function ($query) use ($user) {
                    $query
                        ->where('performed_by', $user->id)
                        ->orWhere('performed_by_email', $user->email);
                })
                ->whereIn('type', ['user.files.submitted', 'client.registration.submitted', 'form.section.updated'])
                ->latest()
                ->limit(20)
                ->get()
                ->map(function (ActivityLog $log) {
                    $metadata = is_array($log->metadata) ? $log->metadata : [];

                    return [
                        'id' => $log->id,
                        'type' => $log->type,
                        'description' => $log->description,
                        'created_at' => $log->created_at?->toDateTimeString(),
                        'files_count' => isset($metadata['files_count']) ? (int) $metadata['files_count'] : null,
                        'filenames' => isset($metadata['filenames']) && is_array($metadata['filenames']) ? array_values($metadata['filenames']) : [],
                        'section_label' => isset($metadata['section_label']) ? (string) $metadata['section_label'] : null,
                        'updated_fields' => isset($metadata['updated_fields']) && is_array($metadata['updated_fields']) ? array_values($metadata['updated_fields']) : [],
                    ];
                })
                ->values(),
        ]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== 'user') {
            return back()->with('error', 'Only client users can be deleted.');
        }

        $assignedTypeValues = RegistrationLink::query()
            ->where('email', $user->email)
            ->where('status', 'completed')
            ->pluck('company_type')
            ->filter(fn ($type) => in_array($type, ['corp', 'sole_prop', 'opc'], true))
            ->unique()
            ->take(3)
            ->values();

        $deletedName = $user->name;
        $deletedEmail = $user->email;
        $deletedId = $user->id;
        $user->delete();

        $this->notificationService->notifyAdmins(
            category: 'user_deleted',
            title: 'User deleted',
            message: "User {$deletedName} ({$deletedEmail}) was deleted.",
            actionUrl: route('admin.user.index'),
            meta: ['email' => $deletedEmail],
        );

        foreach ($assignedTypeValues as $type) {
            $this->activityLogService->log(
                type: 'admin.user.company_type.removed',
                description: "Admin removed {$this->templateService->labelFor($type)} from {$deletedName} ({$deletedEmail})",
                performedBy: $request->user(),
                metadata: [
                    'deleted_user_id' => $deletedId,
                    'deleted_user_email' => $deletedEmail,
                    'deleted_user_name' => $deletedName,
                    'company_type' => $type,
                    'company_type_label' => $this->templateService->labelFor($type),
                ],
            );
        }

        $this->activityLogService->log(
            type: 'admin.user.deleted',
            description: "Admin deleted user {$deletedName} ({$deletedEmail})",
            performedBy: $request->user(),
            metadata: [
                'deleted_user_id' => $deletedId,
                'deleted_user_email' => $deletedEmail,
                'deleted_user_name' => $deletedName,
            ],
        );

        return back()->with('success', 'User deleted successfully');
    }

    /**
     * @return array<int, array{email: string, company_types: array<int, string>}>
     */
    private function eligibleClients(): array
    {
        return RegistrationLink::query()
            ->where('status', 'completed')
            ->whereNotIn('email', User::query()->where('role', 'user')->select('email'))
            ->get(['email', 'company_type'])
            ->groupBy('email')
            ->map(function ($links, string $email): array {
                $companyTypes = collect($links)
                    ->pluck('company_type')
                    ->filter(fn ($type) => in_array($type, ['corp', 'sole_prop', 'opc'], true))
                    ->unique()
                    ->values()
                    ->map(fn (string $type) => $this->templateService->labelFor($type))
                    ->all();

                return [
                    'email' => $email,
                    'company_types' => $companyTypes,
                ];
            })
            ->sortBy('email')
            ->values()
            ->all();
    }
}
