<?php

namespace App\Services\Admin;

use App\Models\ActivityLog;
use App\Models\RegistrationLink;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use App\Services\RegistrationTemplateService;
use App\Services\UserFormSubmissionService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminUserService
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly NotificationService $notificationService,
        private readonly ActivityLogService $activityLogService,
        private readonly UserFormSubmissionService $formSubmissionService,
    ) {}

    public function indexPageProps(string $search, string $sort, string $direction, ?string $companyType): array
    {
        $users = $this->paginatedUsers($search, $sort, $direction, $companyType);

        return [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'company_type' => in_array($companyType, ['corp', 'sole_prop', 'opc'], true) ? $companyType : '',
            ],
        ];
    }

    public function canCreateFromRegistration(string $email): bool
    {
        return RegistrationLink::query()
            ->where('email', $email)
            ->where('status', 'completed')
            ->exists();
    }

    public function createUser(array $validated, ?User $performedBy): User
    {
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make((string) $validated['password']),
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
            performedBy: $performedBy,
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
                performedBy: $performedBy,
                metadata: [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                    'company_type' => $type,
                    'company_type_label' => $this->templateService->labelFor($type),
                ],
            );
        }

        return $user;
    }

    public function showPageProps(User $user): array
    {
        if ($user->role !== 'user') {
            throw new NotFoundHttpException;
        }

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

        return [
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
        ];
    }

    public function deleteUser(User $user, ?User $performedBy): bool
    {
        if ($user->role !== 'user') {
            return false;
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
                performedBy: $performedBy,
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
            performedBy: $performedBy,
            metadata: [
                'deleted_user_id' => $deletedId,
                'deleted_user_email' => $deletedEmail,
                'deleted_user_name' => $deletedName,
            ],
        );

        return true;
    }

    private function paginatedUsers(string $search, string $sort, string $direction, ?string $companyType): LengthAwarePaginator
    {
        $normalizedSort = $sort === 'created_at' ? 'created_at' : 'created_at';
        $normalizedDirection = $direction === 'asc' ? 'asc' : 'desc';
        $normalizedCompanyType = in_array($companyType, ['corp', 'sole_prop', 'opc'], true) ? $companyType : null;

        $users = User::query()
            ->where('role', 'user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($normalizedCompanyType !== null, function ($query) use ($normalizedCompanyType) {
                $query->whereExists(function ($subQuery) use ($normalizedCompanyType) {
                    $subQuery->selectRaw('1')
                        ->from('registration_links')
                        ->whereColumn('registration_links.email', 'users.email')
                        ->where('registration_links.status', 'completed')
                        ->where('registration_links.company_type', $normalizedCompanyType);
                });
            })
            ->orderBy($normalizedSort, $normalizedDirection)
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

        return $users;
    }
}
