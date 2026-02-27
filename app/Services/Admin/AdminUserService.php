<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\AdminUserResource;
use App\Models\RegistrationLink;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\RegistrationTemplateService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class AdminUserService
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly NotificationService $notificationService,
        private readonly AdminFileService $fileService,
    ) {
    }

    public function listUsers(array $input): array
    {
        $search = trim((string) ($input['search'] ?? ''));
        $sort = 'created_at';
        $direction = ($input['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $companyType = $input['company_type'] ?? null;
        $allowedType = in_array($companyType, ['corp', 'sole_prop', 'opc'], true) ? $companyType : '';

        $users = User::query()
            ->where('role', 'user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($allowedType !== '', function ($query) use ($allowedType) {
                $query->whereExists(function ($subQuery) use ($allowedType) {
                    $subQuery->selectRaw('1')
                        ->from('registration_links')
                        ->whereColumn('registration_links.email', 'users.email')
                        ->where('registration_links.status', 'completed')
                        ->where('registration_links.company_type', $allowedType);
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        $linkStatsByEmail = RegistrationLink::query()
            ->withCount('uploads')
            ->whereIn('email', $users->getCollection()->pluck('email')->filter()->values())
            ->latest()
            ->get()
            ->groupBy('email');

        $users->setCollection(
            $users->getCollection()->map(function (User $user) use ($linkStatsByEmail) {
                $emailLinks = $linkStatsByEmail->get($user->email, collect());
                $totalUploads = $emailLinks->sum('uploads_count');
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
                    'uploads_count' => $totalUploads,
                    'show_url' => route('admin.user.show', $user->id),
                ];
            })
        );

        $this->transformPaginator($users, AdminUserResource::class);

        return [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'company_type' => $allowedType,
            ],
        ];
    }

    public function createUser(array $validated): User
    {
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

        return $user;
    }

    public function userDetail(User $user): array
    {
        abort_unless($user->role === 'user', 404);

        $links = RegistrationLink::query()
            ->with('uploads')
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

        $userData = AdminUserResource::make([
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
        ])->resolve();

        return [
            'user' => $userData,
            'uploads' => $this->fileService->mapUploadsForUserLinks($links),
        ];
    }

    public function deleteUser(User $user): string
    {
        abort_unless($user->role === 'user', 422, 'Only client users can be deleted.');

        $deletedName = $user->name;
        $deletedEmail = $user->email;
        $user->delete();

        $this->notificationService->notifyAdmins(
            category: 'user_deleted',
            title: 'User deleted',
            message: "User {$deletedName} ({$deletedEmail}) was deleted.",
            actionUrl: route('admin.user.index'),
            meta: ['email' => $deletedEmail],
        );

        return 'User deleted successfully';
    }

    private function transformPaginator(LengthAwarePaginator $paginator, string $resourceClass): void
    {
        $paginator->setCollection($resourceClass::collection($paginator->getCollection())->resolve());
    }
}
