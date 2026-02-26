<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationLink;
use App\Models\RegistrationUpload;
use App\Models\User;
use App\Services\RegistrationTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
    ) {
    }

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

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'company_type' => in_array($companyType, ['corp', 'sole_prop', 'opc'], true) ? $companyType : '',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/users/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        return back()->with('success', 'User created successfully');
    }

    public function show(User $user): Response
    {
        abort_unless($user->role === 'user', 404);

        $links = RegistrationLink::query()
            ->with('uploads')
            ->where('email', $user->email)
            ->latest()
            ->get();

        $uploads = $links
            ->flatMap(function (RegistrationLink $link) {
                return $link->uploads->map(fn (RegistrationUpload $upload) => [
                    'id' => $upload->id,
                    'registration_link_id' => $link->id,
                    'company_type' => $link->company_type,
                    'company_type_label' => $this->templateService->labelFor($link->company_type),
                    'original_name' => $upload->original_name,
                    'mime_type' => $upload->mime_type,
                    'size_bytes' => $upload->size_bytes,
                    'created_at' => $upload->created_at?->toDateTimeString(),
                    'view_url' => route('admin.register.uploads.view', [$link->id, $upload->id]),
                    'download_url' => route('admin.register.uploads.download', [$link->id, $upload->id]),
                    'download_pdf_url' => route('admin.register.uploads.download', [$link->id, $upload->id]).'?format=pdf',
                    'can_convert_pdf' => in_array(strtolower(pathinfo($upload->original_name, PATHINFO_EXTENSION)), ['doc', 'docx'], true),
                ]);
            })
            ->values();

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
            'uploads' => $uploads,
        ]);
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->role !== 'user') {
            return back()->with('error', 'Only client users can be deleted.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully');
    }
}
