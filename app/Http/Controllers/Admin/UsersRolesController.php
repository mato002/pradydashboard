<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsersRolesController extends Controller
{
    public function index(): View
    {
        $dbUsers = User::query()->orderBy('name')->get();

        $users = $this->buildUsers($dbUsers);
        $roles = $this->buildRoles();
        $permissions = $this->buildPermissionsMatrix();
        $teams = $this->buildTeams();
        $sessions = $this->buildSessions();
        $apiTokens = $this->buildApiTokens();
        $auditLogs = $this->buildAuditLogs();
        $authPolicies = $this->buildAuthPolicies();
        $securityAlerts = $this->buildSecurityAlerts();
        $securityIntel = $this->buildSecurityIntel($users);

        $kpis = [
            'total_users' => [
                'value' => count($users),
                'trend' => '+4',
                'sublabel' => __('Staff &amp; admins'),
                'tone' => 'indigo',
                'points' => $this->spark('users'),
            ],
            'active_sessions' => [
                'value' => collect($sessions)->where('status', 'active')->count(),
                'trend' => __('Live'),
                'sublabel' => __('Online now'),
                'tone' => 'emerald',
                'points' => $this->spark('sessions'),
            ],
            'super_admins' => [
                'value' => collect($users)->filter(fn ($u) => in_array('Super Admin', $u['roles'], true))->count(),
                'trend' => '0',
                'sublabel' => __('Break-glass').': <span class="font-semibold text-amber-600">1</span>',
                'tone' => 'violet',
                'points' => $this->spark('admins'),
            ],
            'suspended' => [
                'value' => collect($users)->where('status', 'suspended')->count(),
                'trend' => '-1',
                'sublabel' => __('Review required'),
                'tone' => 'rose',
                'points' => $this->spark('suspended'),
            ],
            'pending_invites' => [
                'value' => collect($users)->where('status', 'invited')->count(),
                'trend' => '+2',
                'sublabel' => __('Awaiting acceptance'),
                'tone' => 'amber',
                'points' => $this->spark('invites'),
            ],
            'api_tokens' => [
                'value' => collect($apiTokens)->where('status', 'active')->count(),
                'trend' => '+1',
                'sublabel' => __('Rotations due').': 2',
                'tone' => 'sky',
                'points' => $this->spark('tokens'),
            ],
            'mfa_enabled' => [
                'value' => collect($users)->where('mfa', true)->count(),
                'trend' => '+12%',
                'sublabel' => __('Adoption').': <span class="font-semibold text-emerald-600">'.(count($users) > 0 ? round(collect($users)->where('mfa', true)->count() / count($users) * 100) : 0).'%</span>',
                'tone' => 'emerald',
                'points' => $this->spark('mfa'),
            ],
            'failed_logins' => [
                'value' => 23,
                'trend' => '-18%',
                'sublabel' => __('Last 24h'),
                'tone' => 'rose',
                'points' => $this->spark('failed'),
            ],
        ];

        return view('admin.users-roles.index', compact(
            'kpis',
            'users',
            'roles',
            'permissions',
            'teams',
            'sessions',
            'apiTokens',
            'auditLogs',
            'authPolicies',
            'securityAlerts',
            'securityIntel',
        ));
    }

    public function createUser(): View
    {
        return view('admin.users-roles.users.create', [
            'user' => new User,
            'roles' => $this->buildRoles(),
            'departments' => $this->departmentOptions(),
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'department' => ['nullable', 'string', 'max:100'],
            'primary_role' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,invited,suspended'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => $data['status'] === 'active' ? now() : null,
        ]);

        return redirect()
            ->route('users-roles.users.show', $user->id)
            ->with('status', __('User provisioned successfully.'));
    }

    public function showUser(string $userRef): View
    {
        ['user' => $user, 'profile' => $profile] = $this->resolveUser($userRef);
        $sessions = $this->buildSessions();
        $auditLogs = array_slice($this->buildAuditLogs(), 0, 4);

        return view('admin.users-roles.users.show', compact('user', 'profile', 'userRef', 'sessions', 'auditLogs'));
    }

    public function editUser(string $userRef): View
    {
        ['user' => $user, 'profile' => $profile] = $this->resolveUser($userRef);

        return view('admin.users-roles.users.edit', [
            'user' => $user,
            'profile' => $profile,
            'userRef' => $userRef,
            'roles' => $this->buildRoles(),
            'departments' => $this->departmentOptions(),
            'isDemo' => ! $user instanceof User || ! $user->exists,
        ]);
    }

    public function updateUser(Request $request, string $userRef): RedirectResponse
    {
        ['user' => $user, 'profile' => $profile] = $this->resolveUser($userRef);

        if (! $user instanceof User || ! $user->exists) {
            return redirect()
                ->route('users-roles.index')
                ->with('status', __('Demo users cannot be saved. Create a real account to persist changes.'));
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'department' => ['nullable', 'string', 'max:100'],
            'primary_role' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,invited,suspended'],
        ]);

        $updates = [
            'name' => $data['name'],
            'email' => $data['email'],
            'email_verified_at' => $data['status'] === 'active' ? ($user->email_verified_at ?? now()) : null,
        ];

        if (! empty($data['password'])) {
            $updates['password'] = $data['password'];
        }

        $user->update($updates);

        return redirect()
            ->route('users-roles.users.show', $user->id)
            ->with('status', __('User updated.'));
    }

    public function destroyUser(string $userRef): RedirectResponse
    {
        ['user' => $user] = $this->resolveUser($userRef);

        if ($user instanceof User && $user->exists) {
            if ($user->id === auth()->id()) {
                return redirect()->back()->with('status', __('You cannot delete your own account.'));
            }

            $user->delete();

            return redirect()->route('users-roles.index')->with('status', __('User removed.'));
        }

        return redirect()->route('users-roles.index')->with('status', __('Demo user removed from view only.'));
    }

    public function createRole(): View
    {
        return view('admin.users-roles.roles.create', [
            'role' => $this->blankRole(),
            'roles' => $this->buildRoles(),
        ]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'level' => ['required', 'integer', 'min:1', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'inherits' => ['nullable', 'string', 'max:100'],
        ]);

        return redirect()
            ->route('users-roles.roles.show', $data['slug'])
            ->with('status', __('Role :name created.', ['name' => $data['name']]))
            ->with('role_draft', $data);
    }

    public function showRole(string $slug): View
    {
        $role = $this->resolveRole($slug);
        $permissions = $this->buildPermissionsMatrix();

        return view('admin.users-roles.roles.show', compact('role', 'slug', 'permissions'));
    }

    public function editRole(string $slug): View
    {
        $role = $this->resolveRole($slug);

        return view('admin.users-roles.roles.edit', [
            'role' => $role,
            'slug' => $slug,
            'roles' => $this->buildRoles(),
        ]);
    }

    public function updateRole(Request $request, string $slug): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'level' => ['required', 'integer', 'min:1', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'inherits' => ['nullable', 'string', 'max:100'],
        ]);

        return redirect()
            ->route('users-roles.roles.show', $slug)
            ->with('status', __('Role updated.'));
    }

    /**
     * @return array{user: User, profile: array<string, mixed>}
     */
    private function resolveUser(string $userRef): array
    {
        if (ctype_digit($userRef)) {
            $user = User::query()->findOrFail($userRef);
            $users = $this->buildUsers(collect([$user]));
            $profile = $users[0] ?? $this->demoUsers()[0];

            return ['user' => $user, 'profile' => $profile];
        }

        $demo = collect($this->demoUsers())->firstWhere('id', $userRef);

        if (! $demo) {
            abort(404);
        }

        return ['user' => new User(['name' => $demo['name'], 'email' => $demo['email']]), 'profile' => $demo];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveRole(string $slug): array
    {
        $draft = session('role_draft');
        if (is_array($draft) && ($draft['slug'] ?? '') === $slug) {
            return array_merge($this->blankRole(), $draft, ['users' => 0, 'permissions' => 12]);
        }

        $found = collect($this->buildRoles())->firstWhere('slug', $slug);

        if (! $found) {
            abort(404);
        }

        return $found;
    }

    /**
     * @return array<string, mixed>
     */
    private function blankRole(): array
    {
        return [
            'slug' => '',
            'name' => '',
            'users' => 0,
            'permissions' => 0,
            'level' => 50,
            'inherits' => null,
            'description' => '',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function departmentOptions(): array
    {
        return ['Platform Ops', 'Infrastructure', 'Support', 'Finance', 'Security', 'Engineering', 'Compliance', 'Contractor'];
    }

    /**
     * @param  Collection<int, User>  $dbUsers
     * @return array<int, array<string, mixed>>
     */
    private function buildUsers(Collection $dbUsers): array
    {
        $demo = $this->demoUsers();

        if ($dbUsers->isEmpty()) {
            return $demo;
        }

        $rolePool = ['Super Admin', 'Infrastructure Admin', 'Support Agent', 'Billing Manager', 'Read-only Auditor', 'DevOps Engineer'];
        $depts = ['Platform Ops', 'Infrastructure', 'Support', 'Finance', 'Security', 'Engineering'];

        $mapped = $dbUsers->map(function (User $user, int $i) use ($demo, $rolePool, $depts) {
            $fallback = $demo[$i % count($demo)];
            $initials = collect(explode(' ', $user->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'initials' => $initials ?: 'U',
                'department' => $depts[$i % count($depts)],
                'roles' => [$rolePool[$i % count($rolePool)]],
                'access_level' => $i === 0 ? 'full' : ($i < 3 ? 'elevated' : 'standard'),
                'status' => $user->email_verified_at ? 'active' : 'invited',
                'last_activity' => $user->updated_at?->diffForHumans() ?? __('Never'),
                'last_ip' => $fallback['last_ip'],
                'mfa' => $i % 3 !== 0,
                'sessions' => $i % 4 === 0 ? 2 : 1,
                'online' => $i < 3,
                'risk' => $fallback['risk'],
                'location' => $fallback['location'],
            ];
        })->all();

        if (count($mapped) < 8) {
            $mapped = array_merge($mapped, array_slice($demo, count($mapped), 8 - count($mapped)));
        }

        return $mapped;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function demoUsers(): array
    {
        $rows = [
            ['name' => 'Sarah Kamau', 'email' => 'sarah.k@pradytecai.com', 'dept' => 'Platform Ops', 'roles' => ['Super Admin'], 'level' => 'full', 'status' => 'active', 'mfa' => true, 'sessions' => 2, 'online' => true, 'risk' => 'low', 'ip' => '197.237.12.44', 'loc' => 'Nairobi, KE'],
            ['name' => 'James Ochieng', 'email' => 'james.o@pradytecai.com', 'dept' => 'Infrastructure', 'roles' => ['Infrastructure Admin', 'DevOps Engineer'], 'level' => 'elevated', 'status' => 'active', 'mfa' => true, 'sessions' => 1, 'online' => true, 'risk' => 'low', 'ip' => '10.0.4.22', 'loc' => 'Nairobi, KE'],
            ['name' => 'Amina Wanjiku', 'email' => 'amina.w@pradytecai.com', 'dept' => 'Support', 'roles' => ['Support Agent'], 'level' => 'standard', 'status' => 'active', 'mfa' => true, 'sessions' => 1, 'online' => false, 'risk' => 'low', 'ip' => '52.74.128.91', 'loc' => 'Mombasa, KE'],
            ['name' => 'David Mwangi', 'email' => 'david.m@pradytecai.com', 'dept' => 'Engineering', 'roles' => ['DevOps Engineer'], 'level' => 'elevated', 'status' => 'active', 'mfa' => false, 'sessions' => 1, 'online' => true, 'risk' => 'medium', 'ip' => '185.220.101.42', 'loc' => 'Frankfurt, DE'],
            ['name' => 'Grace Njeri', 'email' => 'grace.n@pradytecai.com', 'dept' => 'Finance', 'roles' => ['Billing Manager'], 'level' => 'standard', 'status' => 'active', 'mfa' => true, 'sessions' => 1, 'online' => false, 'risk' => 'low', 'ip' => '197.237.88.12', 'loc' => 'Nairobi, KE'],
            ['name' => 'Peter Odhiambo', 'email' => 'peter.o@pradytecai.com', 'dept' => 'Security', 'roles' => ['Security Officer'], 'level' => 'elevated', 'status' => 'active', 'mfa' => true, 'sessions' => 2, 'online' => true, 'risk' => 'low', 'ip' => '10.0.8.5', 'loc' => 'Nairobi, KE'],
            ['name' => 'Linda Akinyi', 'email' => 'linda.a@pradytecai.com', 'dept' => 'Support', 'roles' => ['Tenant Manager'], 'level' => 'standard', 'status' => 'invited', 'mfa' => false, 'sessions' => 0, 'online' => false, 'risk' => 'low', 'ip' => '—', 'loc' => '—'],
            ['name' => 'Tom Hassan', 'email' => 'tom.h@pradytecai.com', 'dept' => 'Platform Ops', 'roles' => ['Monitoring Analyst'], 'level' => 'standard', 'status' => 'active', 'mfa' => true, 'sessions' => 1, 'online' => false, 'risk' => 'low', 'ip' => '203.0.113.18', 'loc' => 'Kampala, UG'],
            ['name' => 'Eva Kiptoo', 'email' => 'eva.k@pradytecai.com', 'dept' => 'Compliance', 'roles' => ['Read-only Auditor'], 'level' => 'read', 'status' => 'active', 'mfa' => true, 'sessions' => 1, 'online' => false, 'risk' => 'low', 'ip' => '197.237.55.90', 'loc' => 'Nairobi, KE'],
            ['name' => 'Marcus Chen', 'email' => 'marcus.c@external.com', 'dept' => 'Contractor', 'roles' => ['Read-only Auditor'], 'level' => 'read', 'status' => 'suspended', 'mfa' => false, 'sessions' => 0, 'online' => false, 'risk' => 'high', 'ip' => '45.33.32.108', 'loc' => 'Unknown'],
        ];

        return collect($rows)->map(function (array $row, int $i) {
            $parts = explode(' ', $row['name']);

            return [
                'id' => 'usr_'.($i + 1),
                'name' => $row['name'],
                'email' => $row['email'],
                'initials' => strtoupper(substr($parts[0], 0, 1).substr($parts[1] ?? '', 0, 1)),
                'department' => $row['dept'],
                'roles' => $row['roles'],
                'access_level' => $row['level'],
                'status' => $row['status'],
                'last_activity' => ['2m ago', '15m ago', '1h ago', '3h ago', 'Yesterday', '5m ago', '—', '20m ago', '2d ago', '30d ago'][$i % 10],
                'last_ip' => $row['ip'],
                'mfa' => $row['mfa'],
                'sessions' => $row['sessions'],
                'online' => $row['online'],
                'risk' => $row['risk'],
                'location' => $row['loc'],
            ];
        })->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRoles(): array
    {
        return [
            ['slug' => 'super_admin', 'name' => 'Super Admin', 'users' => 2, 'permissions' => 48, 'level' => 100, 'inherits' => null, 'description' => __('Full platform control including IAM and billing.')],
            ['slug' => 'infra_admin', 'name' => 'Infrastructure Admin', 'users' => 3, 'permissions' => 32, 'level' => 85, 'inherits' => 'devops', 'description' => __('Servers, deployments, backups, SSL.')],
            ['slug' => 'security', 'name' => 'Security Officer', 'users' => 2, 'permissions' => 28, 'level' => 80, 'inherits' => null, 'description' => __('IAM, audit logs, access policies.')],
            ['slug' => 'billing', 'name' => 'Billing Manager', 'users' => 2, 'permissions' => 18, 'level' => 60, 'inherits' => null, 'description' => __('Invoices, payments, subscriptions.')],
            ['slug' => 'support', 'name' => 'Support Agent', 'users' => 4, 'permissions' => 14, 'level' => 45, 'inherits' => null, 'description' => __('Tickets, tenant read, limited actions.')],
            ['slug' => 'devops', 'name' => 'DevOps Engineer', 'users' => 3, 'permissions' => 24, 'level' => 70, 'inherits' => null, 'description' => __('Deploy, rollback, monitor infrastructure.')],
            ['slug' => 'monitoring', 'name' => 'Monitoring Analyst', 'users' => 2, 'permissions' => 12, 'level' => 40, 'inherits' => null, 'description' => __('Read-only monitoring and alerts.')],
            ['slug' => 'tenant_mgr', 'name' => 'Tenant Manager', 'users' => 3, 'permissions' => 20, 'level' => 55, 'inherits' => null, 'description' => __('Tenant CRUD, modules, access controls.')],
            ['slug' => 'auditor', 'name' => 'Read-only Auditor', 'users' => 2, 'permissions' => 8, 'level' => 15, 'inherits' => null, 'description' => __('View all modules, export audit data.')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPermissionsMatrix(): array
    {
        $modules = ['Tenants', 'Servers', 'Projects', 'Billing', 'Users & IAM', 'Deployments', 'Monitoring', 'Support', 'API', 'Settings'];
        $actions = ['view', 'create', 'update', 'delete', 'deploy', 'rollback', 'export', 'manage_billing', 'manage_users', 'manage_servers'];

        $matrix = [];
        foreach ($modules as $mod) {
            $row = ['module' => $mod, 'grants' => []];
            foreach ($actions as $action) {
                $hash = crc32($mod.$action);
                $row['grants'][$action] = ($hash % 3) !== 0;
            }
            if ($mod === 'Users & IAM') {
                $row['grants']['manage_users'] = true;
            }
            if (in_array($mod, ['Servers', 'Deployments'], true)) {
                $row['grants']['deploy'] = true;
                $row['grants']['manage_servers'] = $mod === 'Servers';
            }
            $matrix[] = $row;
        }

        return ['modules' => $modules, 'actions' => $actions, 'matrix' => $matrix];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTeams(): array
    {
        return [
            ['name' => 'Platform Operations', 'lead' => 'Sarah Kamau', 'members' => 8, 'permissions' => 'elevated', 'children' => ['Infrastructure', 'Support L1']],
            ['name' => 'Infrastructure', 'lead' => 'James Ochieng', 'members' => 5, 'permissions' => 'infra', 'children' => ['DevOps', 'NOC']],
            ['name' => 'Finance & Billing', 'lead' => 'Grace Njeri', 'members' => 3, 'permissions' => 'billing', 'children' => []],
            ['name' => 'Security & Compliance', 'lead' => 'Peter Odhiambo', 'members' => 4, 'permissions' => 'security', 'children' => ['Audit']],
            ['name' => 'Customer Support', 'lead' => 'Amina Wanjiku', 'members' => 6, 'permissions' => 'support', 'children' => []],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildSessions(): array
    {
        return [
            ['user' => 'Sarah Kamau', 'device' => 'MacBook Pro', 'browser' => 'Chrome 124', 'os' => 'macOS 14', 'ip' => '197.237.12.44', 'location' => 'Nairobi, KE', 'started' => '2h 14m', 'status' => 'active'],
            ['user' => 'James Ochieng', 'device' => 'Windows Workstation', 'browser' => 'Edge 124', 'os' => 'Windows 11', 'ip' => '10.0.4.22', 'location' => 'Nairobi, KE', 'started' => '45m', 'status' => 'active'],
            ['user' => 'David Mwangi', 'device' => 'Linux Server', 'browser' => 'Firefox 125', 'os' => 'Ubuntu 22.04', 'ip' => '185.220.101.42', 'location' => 'Frankfurt, DE', 'started' => '12m', 'status' => 'active'],
            ['user' => 'Peter Odhiambo', 'device' => 'iPhone 15', 'browser' => 'Safari Mobile', 'os' => 'iOS 17', 'ip' => '197.237.88.5', 'location' => 'Nairobi, KE', 'started' => '3h 02m', 'status' => 'active'],
            ['user' => 'Amina Wanjiku', 'device' => 'iPad', 'browser' => 'Safari', 'os' => 'iPadOS 17', 'ip' => '52.74.128.91', 'location' => 'Mombasa, KE', 'started' => '1d ago', 'status' => 'idle'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildApiTokens(): array
    {
        return [
            ['name' => 'CI/CD Pipeline', 'owner' => 'James Ochieng', 'scopes' => 'deploy, servers:read', 'last_used' => '5m ago', 'requests' => '12.4K', 'status' => 'active', 'expires' => 'Dec 2026'],
            ['name' => 'Monitoring Agent', 'owner' => 'Tom Hassan', 'scopes' => 'monitoring:read', 'last_used' => '1m ago', 'requests' => '89K', 'status' => 'active', 'expires' => 'Never'],
            ['name' => 'Legacy Integration', 'owner' => 'David Mwangi', 'scopes' => 'api:full', 'last_used' => '30d ago', 'requests' => '0', 'status' => 'revoked', 'expires' => 'Expired'],
            ['name' => 'Audit Export', 'owner' => 'Eva Kiptoo', 'scopes' => 'audit:export', 'last_used' => '2h ago', 'requests' => '142', 'status' => 'active', 'expires' => 'Jun 2026'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildAuditLogs(): array
    {
        return [
            ['time' => '14:32', 'actor' => 'Sarah Kamau', 'action' => __('Role assigned'), 'target' => 'Linda Akinyi → Tenant Manager', 'type' => 'iam', 'severity' => 'info'],
            ['time' => '14:15', 'actor' => 'System', 'action' => __('Failed login'), 'target' => 'marcus.c@external.com — 5 attempts', 'type' => 'security', 'severity' => 'danger'],
            ['time' => '13:48', 'actor' => 'James Ochieng', 'action' => __('Deployment approved'), 'target' => 'MFI Core v2.4.1', 'type' => 'deploy', 'severity' => 'info'],
            ['time' => '12:20', 'actor' => 'Grace Njeri', 'action' => __('Invoice generated'), 'target' => 'Savanna Retail — INV-2401', 'type' => 'billing', 'severity' => 'info'],
            ['time' => '11:05', 'actor' => 'Peter Odhiambo', 'action' => __('MFA policy updated'), 'target' => __('Enforce for all admins'), 'type' => 'policy', 'severity' => 'warning'],
            ['time' => '09:30', 'actor' => 'David Mwangi', 'action' => __('API token created'), 'target' => 'CI/CD Pipeline', 'type' => 'api', 'severity' => 'info'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAuthPolicies(): array
    {
        return [
            'mfa_required' => true,
            'mfa_admins_only' => false,
            'sso_enabled' => true,
            'sso_provider' => 'Microsoft Entra ID',
            'password_min_length' => 12,
            'password_expiry_days' => 90,
            'session_timeout_min' => 60,
            'ip_allowlist_enabled' => true,
            'geo_restrict' => false,
            'anomaly_detection' => true,
            'policies' => [
                ['name' => __('Enforce MFA'), 'enabled' => true, 'scope' => __('All users')],
                ['name' => __('SSO — Microsoft Entra'), 'enabled' => true, 'scope' => __('Organization')],
                ['name' => __('Password complexity'), 'enabled' => true, 'scope' => __('12+ chars, symbols')],
                ['name' => __('IP allowlist'), 'enabled' => true, 'scope' => __('3 ranges')],
                ['name' => __('Session timeout'), 'enabled' => true, 'scope' => __('60 minutes idle')],
                ['name' => __('Geo restriction'), 'enabled' => false, 'scope' => __('Africa + EU')],
                ['name' => __('Login anomaly detection'), 'enabled' => true, 'scope' => __('ML-based')],
            ],
            'suspicious' => [
                ['ip' => '185.220.101.42', 'user' => 'David Mwangi', 'reason' => __('Unusual location'), 'time' => '12m ago'],
                ['ip' => '45.33.32.108', 'user' => 'Marcus Chen', 'reason' => __('Brute force (5 fails)'), 'time' => '2h ago'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildSecurityAlerts(): array
    {
        return [
            ['type' => 'danger', 'title' => __('Suspicious login cluster'), 'body' => __('47 failed attempts from 185.220.x.x in 1 hour'), 'time' => __('14:15')],
            ['type' => 'warning', 'title' => __('Privilege escalation'), 'body' => __('Tenant Manager role assigned to invited user'), 'time' => __('14:32')],
            ['type' => 'warning', 'title' => __('MFA not enabled'), 'body' => __('David Mwangi — elevated access without MFA'), 'time' => __('Today')],
            ['type' => 'info', 'title' => __('API token abuse'), 'body' => __('Legacy Integration — 0 requests, consider revoke'), 'time' => __('30d')],
            ['type' => 'info', 'title' => __('Inactive account'), 'body' => __('Marcus Chen suspended — contractor offboarded'), 'time' => __('1w')],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $users
     * @return array<string, mixed>
     */
    private function buildSecurityIntel(array $users): array
    {
        $mfaCount = collect($users)->where('mfa', true)->count();
        $total = max(count($users), 1);

        return [
            'threat_level' => 'elevated',
            'threat_label' => __('Elevated'),
            'login_success_rate' => 97.8,
            'mfa_adoption' => round(($mfaCount / $total) * 100, 1),
            'security_score' => 86,
            'open_incidents' => 2,
            'pending_access_requests' => 3,
        ];
    }

    /**
     * @return array<int, float>
     */
    private function spark(string $seed): array
    {
        $h = crc32($seed);
        $pts = [];
        for ($i = 0; $i < 8; $i++) {
            $pts[] = 32 + (($h >> ($i * 3)) & 0x3F) % 48;
        }

        return $pts;
    }
}
