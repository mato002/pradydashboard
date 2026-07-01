<?php
    $actionLabels = [
        'view' => __('View'),
        'create' => __('Create'),
        'update' => __('Update'),
        'delete' => __('Delete'),
        'deploy' => __('Deploy'),
        'rollback' => __('Rollback'),
        'export' => __('Export'),
        'manage_billing' => __('Billing'),
        'manage_users' => __('Users'),
        'manage_servers' => __('Servers'),
    ];
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Identity & Access'),'subheading' => __('Enterprise IAM control center')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Identity & Access')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Enterprise IAM control center'))]); ?>
    <div
        x-data="iamCenter(<?php echo \Illuminate\Support\Js::from($users)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($roles)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($permissions)->toHtml() ?>)"
        class="space-y-6"
    >
        
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400"><?php echo e(__('Security & governance')); ?></p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Identity & Access Management')); ?></h2>
                <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('Users, roles, permissions, sessions, API tokens, authentication policies, and audit visibility.')); ?></p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-500/20 dark:text-amber-200">
                    <?php echo e(__('Threat')); ?>: <?php echo e(ucfirst($securityIntel['threat_level'])); ?>

                </span>
                <a href="<?php echo e(route('users-roles.users.create')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-indigo-500/25 hover:brightness-110">
                    <?php echo e(__('Add user')); ?>

                </a>
                <a href="<?php echo e(route('users-roles.roles.create')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white/80 px-4 py-2 text-xs font-semibold backdrop-blur dark:border-slate-700 dark:bg-slate-900/80">
                    <?php echo e(__('Create role')); ?>

                </a>
            </div>
        </div>

        
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-8">
            <?php $__currentLoopData = [
                'total_users' => ['icon' => 'users', 'title' => __('Total users')],
                'active_sessions' => ['icon' => 'sessions', 'title' => __('Active sessions')],
                'super_admins' => ['icon' => 'admin', 'title' => __('Super admins')],
                'suspended' => ['icon' => 'suspend', 'title' => __('Suspended')],
                'pending_invites' => ['icon' => 'invite', 'title' => __('Pending invites')],
                'api_tokens' => ['icon' => 'token', 'title' => __('API tokens')],
                'mfa_enabled' => ['icon' => 'mfa', 'title' => __('MFA enabled')],
                'failed_logins' => ['icon' => 'fail', 'title' => __('Failed logins')],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => $meta['title'],'value' => $kpis[$key]['value'],'trend' => $kpis[$key]['trend'],'sublabel' => $kpis[$key]['sublabel'],'points' => $kpis[$key]['points'],'tone' => $kpis[$key]['tone'],'animate' => is_numeric($kpis[$key]['value'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($meta['title']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis[$key]['value']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis[$key]['trend']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis[$key]['sublabel']),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis[$key]['points']),'tone' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis[$key]['tone']),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(is_numeric($kpis[$key]['value']))]); ?>
                     <?php $__env->slot('icon', null, []); ?> 
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" /></svg>
                     <?php $__env->endSlot(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $attributes = $__attributesOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__attributesOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $component = $__componentOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__componentOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="grid gap-5 xl:grid-cols-12">
            <div class="space-y-5 xl:col-span-9">
                
                <div class="overflow-x-auto prady-scrollbar">
                    <div class="flex min-w-max gap-1 rounded-xl border border-slate-200/80 bg-slate-50/80 p-1 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
                        <?php $__currentLoopData = [
                            'users' => __('Users'),
                            'roles' => __('Roles'),
                            'permissions' => __('Permissions'),
                            'teams' => __('Teams'),
                            'sessions' => __('Sessions'),
                            'tokens' => __('API Tokens'),
                            'audit' => __('Audit'),
                            'auth' => __('Auth policies'),
                            'alerts' => __('Alerts'),
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="button" @click="activeTab = '<?php echo e($tab); ?>'" :class="activeTab === '<?php echo e($tab); ?>' ? 'bg-white text-indigo-600 shadow dark:bg-slate-800 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400'" class="rounded-lg px-3 py-2 text-[11px] font-semibold whitespace-nowrap transition"><?php echo e($label); ?></button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <div x-show="activeTab === 'users'" class="space-y-4">
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-card backdrop-blur dark:border-slate-800/80 dark:bg-slate-900/70">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Platform users')); ?></h3>
                                <p class="text-xs text-slate-500" x-text="filteredUsers.length + ' <?php echo e(__('users')); ?>'"></p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <input type="search" x-model="searchQuery" placeholder="<?php echo e(__('Search name or email…')); ?>" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-3 pr-3 text-xs dark:border-slate-700 dark:bg-slate-800" />
                                <select x-model="filterStatus" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
                                    <option value=""><?php echo e(__('All statuses')); ?></option>
                                    <option value="active"><?php echo e(__('Active')); ?></option>
                                    <option value="invited"><?php echo e(__('Invited')); ?></option>
                                    <option value="suspended"><?php echo e(__('Suspended')); ?></option>
                                </select>
                                <select x-model="filterRisk" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
                                    <option value=""><?php echo e(__('All risk')); ?></option>
                                    <option value="high"><?php echo e(__('High risk')); ?></option>
                                    <option value="medium"><?php echo e(__('Medium')); ?></option>
                                    <option value="low"><?php echo e(__('Low')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="prady-scrollbar overflow-x-auto">
                            <table class="prady-table w-full min-w-[1100px]">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('User')); ?></th>
                                        <th><?php echo e(__('Department')); ?></th>
                                        <th><?php echo e(__('Roles')); ?></th>
                                        <th><?php echo e(__('Access')); ?></th>
                                        <th><?php echo e(__('Status')); ?></th>
                                        <th><?php echo e(__('Last activity')); ?></th>
                                        <th><?php echo e(__('IP')); ?></th>
                                        <th><?php echo e(__('MFA')); ?></th>
                                        <th><?php echo e(__('Sessions')); ?></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                    <template x-for="user in filteredUsers" :key="user.id">
                                        <tr class="transition hover:bg-indigo-50/40 dark:hover:bg-indigo-500/5">
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="relative">
                                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white" x-text="user.initials"></span>
                                                        <span x-show="user.online" class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900"></span>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-slate-900 dark:text-white" x-text="user.name"></p>
                                                        <p class="text-[11px] text-slate-500" x-text="user.email"></p>
                                                    </div>
                                                    <span x-show="user.risk === 'high'" class="rounded bg-rose-500/15 px-1.5 py-0.5 text-[9px] font-bold uppercase text-rose-600"><?php echo e(__('Risk')); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-sm text-slate-600 dark:text-slate-400" x-text="user.department"></td>
                                            <td>
                                                <template x-for="role in user.roles" :key="role">
                                                    <span class="mr-1 inline-flex rounded-md bg-indigo-500/10 px-2 py-0.5 text-[10px] font-semibold text-indigo-700 dark:text-indigo-300" x-text="role"></span>
                                                </template>
                                            </td>
                                            <td class="text-xs font-medium capitalize text-slate-600" x-text="user.access_level"></td>
                                            <td>
                                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase ring-1 ring-inset"
                                                    :class="{
                                                        'bg-emerald-500/12 text-emerald-700 ring-emerald-500/20': user.status === 'active',
                                                        'bg-amber-500/12 text-amber-800 ring-amber-500/20': user.status === 'invited',
                                                        'bg-rose-500/12 text-rose-700 ring-rose-500/20': user.status === 'suspended',
                                                    }" x-text="user.status"></span>
                                            </td>
                                            <td class="text-xs text-slate-500" x-text="user.last_activity"></td>
                                            <td class="font-mono text-[11px] text-slate-500" x-text="user.last_ip"></td>
                                            <td>
                                                <span x-show="user.mfa" class="text-emerald-600"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?></span>
                                                <span x-show="!user.mfa" class="text-rose-500"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'xmark']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'xmark']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?></span>
                                            </td>
                                            <td class="tabular-nums text-sm font-medium" x-text="user.sessions"></td>
                                            <td class="text-right">
                                                <a :href="'<?php echo e(url('users-roles/users')); ?>/' + user.id" @click.stop class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('View')); ?></a>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                
                <div x-show="activeTab === 'roles'" x-cloak class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-card backdrop-blur transition hover:shadow-card-hover dark:border-slate-800/80 dark:bg-slate-900/70">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-semibold text-slate-900 dark:text-white"><?php echo e($role['name']); ?></h4>
                                    <p class="mt-1 text-xs text-slate-500"><?php echo e($role['description']); ?></p>
                                </div>
                                <span class="rounded-full bg-violet-500/10 px-2 py-0.5 text-[10px] font-bold text-violet-700 dark:text-violet-300">L<?php echo e($role['level']); ?></span>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2 text-center text-xs">
                                <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800"><p class="font-bold text-slate-900 dark:text-white"><?php echo e($role['users']); ?></p><p class="text-slate-500"><?php echo e(__('Users')); ?></p></div>
                                <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800"><p class="font-bold text-indigo-600"><?php echo e($role['permissions']); ?></p><p class="text-slate-500"><?php echo e(__('Permissions')); ?></p></div>
                            </div>
                            <?php if($role['inherits']): ?>
                                <p class="mt-2 text-[10px] text-slate-500"><?php echo e(__('Inherits')); ?>: <span class="font-semibold"><?php echo e($role['inherits']); ?></span></p>
                            <?php endif; ?>
                            <div class="mt-3 flex gap-2">
                                <a href="<?php echo e(route('users-roles.roles.show', $role['slug'])); ?>" class="flex-1 rounded-lg border border-slate-200 py-1.5 text-center text-[11px] font-semibold dark:border-slate-700"><?php echo e(__('View')); ?></a>
                                <a href="<?php echo e(route('users-roles.roles.edit', $role['slug'])); ?>" class="flex-1 rounded-lg bg-indigo-600 py-1.5 text-center text-[11px] font-semibold text-white"><?php echo e(__('Edit')); ?></a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div x-show="activeTab === 'permissions'" x-cloak class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-card backdrop-blur dark:border-slate-800/80 dark:bg-slate-900/70">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Permissions matrix')); ?></h3>
                        <p class="text-xs text-slate-500"><?php echo e(__('Role: Super Admin — inherited grants shown')); ?></p>
                    </div>
                    <div class="prady-scrollbar overflow-x-auto p-4">
                        <table class="w-full min-w-[800px] text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700">
                                    <th class="py-2 text-left font-semibold text-slate-600"><?php echo e(__('Module')); ?></th>
                                    <?php $__currentLoopData = $permissions['actions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="px-1 py-2 text-center font-semibold text-slate-500"><?php echo e($actionLabels[$action] ?? $action); ?></th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $permissions['matrix']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="border-b border-slate-100 dark:border-slate-800/80">
                                        <td class="py-2 font-medium text-slate-800 dark:text-slate-200"><?php echo e($row['module']); ?></td>
                                        <?php $__currentLoopData = $permissions['actions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="px-1 py-2 text-center">
                                                <?php if($row['grants'][$action] ?? false): ?>
                                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-emerald-500/15 text-emerald-600"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'check','class' => 'text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'text-xs']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?></span>
                                                <?php else: ?>
                                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-slate-300 dark:bg-slate-800">—</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div x-show="activeTab === 'teams'" x-cloak class="space-y-3">
                    <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/70">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <h4 class="font-semibold text-slate-900 dark:text-white"><?php echo e($team['name']); ?></h4>
                                    <p class="text-xs text-slate-500"><?php echo e(__('Lead')); ?>: <?php echo e($team['lead']); ?> · <?php echo e($team['members']); ?> <?php echo e(__('members')); ?></p>
                                </div>
                                <span class="rounded-full bg-indigo-500/10 px-2.5 py-0.5 text-[10px] font-bold text-indigo-700 dark:text-indigo-300"><?php echo e($team['permissions']); ?></span>
                            </div>
                            <?php if(count($team['children']) > 0): ?>
                                <div class="mt-3 flex flex-wrap gap-1">
                                    <?php $__currentLoopData = $team['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] dark:bg-slate-800"><?php echo e($child); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div x-show="activeTab === 'sessions'" x-cloak class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-card dark:border-slate-800/80 dark:bg-slate-900/70">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Active sessions')); ?></h3>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white"><?php echo e($session['user']); ?></p>
                                    <p class="text-xs text-slate-500"><?php echo e($session['device']); ?> · <?php echo e($session['browser']); ?> · <?php echo e($session['os']); ?></p>
                                    <p class="mt-0.5 font-mono text-[11px] text-slate-400"><?php echo e($session['ip']); ?> — <?php echo e($session['location']); ?></p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-slate-500"><?php echo e($session['started']); ?></span>
                                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $session['status'] === 'active' ? 'success' : 'neutral']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($session['status'] === 'active' ? 'success' : 'neutral')]); ?><?php echo e(ucfirst($session['status'])); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                                    <button type="button" class="rounded-lg border border-rose-200 px-2 py-1 text-[10px] font-semibold text-rose-600 dark:border-rose-500/30"><?php echo e(__('Terminate')); ?></button>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <div x-show="activeTab === 'tokens'" x-cloak class="space-y-3">
                    <?php $__currentLoopData = $apiTokens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $token): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/70">
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white"><?php echo e($token['name']); ?></p>
                                <p class="text-xs text-slate-500"><?php echo e($token['owner']); ?> · <?php echo e($token['scopes']); ?></p>
                                <p class="mt-1 text-[11px] text-slate-400"><?php echo e(__('Last used')); ?>: <?php echo e($token['last_used']); ?> · <?php echo e($token['requests']); ?> <?php echo e(__('requests')); ?></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $token['status'] === 'active' ? 'success' : 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($token['status'] === 'active' ? 'success' : 'danger')]); ?><?php echo e(ucfirst($token['status'])); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                                <span class="text-xs text-slate-500"><?php echo e($token['expires']); ?></span>
                                <button type="button" class="text-xs font-semibold text-indigo-600"><?php echo e(__('Rotate')); ?></button>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div x-show="activeTab === 'audit'" x-cloak class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-card dark:border-slate-800/80 dark:bg-slate-900/70">
                    <div class="flex items-center justify-between border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Audit timeline')); ?></h3>
                        <button type="button" class="text-xs font-semibold text-indigo-600"><?php echo e(__('Export report')); ?></button>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        <?php $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex gap-4 px-4 py-3">
                                <span class="w-12 shrink-0 font-mono text-xs text-slate-400"><?php echo e($log['time']); ?></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white"><?php echo e($log['action']); ?></p>
                                    <p class="text-xs text-slate-500"><?php echo e($log['actor']); ?> → <?php echo e($log['target']); ?></p>
                                </div>
                                <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $log['severity'] === 'danger' ? 'danger' : ($log['severity'] === 'warning' ? 'warning' : 'info')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($log['severity'] === 'danger' ? 'danger' : ($log['severity'] === 'warning' ? 'warning' : 'info'))]); ?><?php echo e($log['type']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>

                
                <div x-show="activeTab === 'auth'" x-cloak class="grid gap-4 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/70">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Authentication policies')); ?></h3>
                        <ul class="mt-3 space-y-2">
                            <?php $__currentLoopData = $authPolicies['policies']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $policy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-center justify-between rounded-xl border border-slate-200/80 px-3 py-2 dark:border-slate-700">
                                    <div>
                                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200"><?php echo e($policy['name']); ?></p>
                                        <p class="text-[11px] text-slate-500"><?php echo e($policy['scope']); ?></p>
                                    </div>
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['h-2 w-2 rounded-full', 'bg-emerald-500' => $policy['enabled'], 'bg-slate-300 dark:bg-slate-600' => ! $policy['enabled']]); ?>"></span>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                    <div class="overflow-hidden rounded-2xl border border-rose-200/60 bg-rose-50/30 p-4 dark:border-rose-500/20 dark:bg-rose-950/20">
                        <h3 class="text-sm font-semibold text-rose-900 dark:text-rose-200"><?php echo e(__('Suspicious activity')); ?></h3>
                        <ul class="mt-3 space-y-2">
                            <?php $__currentLoopData = $authPolicies['suspicious']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="rounded-xl bg-white/80 px-3 py-2 text-xs dark:bg-slate-900/60">
                                    <p class="font-semibold text-slate-900 dark:text-white"><?php echo e($item['user']); ?></p>
                                    <p class="text-slate-500"><?php echo e($item['reason']); ?> — <?php echo e($item['ip']); ?></p>
                                    <p class="text-[10px] text-slate-400"><?php echo e($item['time']); ?></p>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>

                
                <div x-show="activeTab === 'alerts'" x-cloak class="space-y-2">
                    <?php $__currentLoopData = $securityAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'rounded-2xl border px-4 py-3',
                            'border-rose-200/80 bg-rose-50/50 dark:border-rose-500/20 dark:bg-rose-950/30' => $alert['type'] === 'danger',
                            'border-amber-200/80 bg-amber-50/40 dark:border-amber-500/20 dark:bg-amber-950/20' => $alert['type'] === 'warning',
                            'border-sky-200/80 bg-sky-50/40 dark:border-sky-500/20 dark:bg-sky-950/20' => $alert['type'] === 'info',
                        ]); ?>">
                            <div class="flex justify-between gap-2">
                                <p class="font-semibold text-slate-900 dark:text-white"><?php echo e($alert['title']); ?></p>
                                <span class="text-[10px] text-slate-400"><?php echo e($alert['time']); ?></span>
                            </div>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400"><?php echo e($alert['body']); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <div class="space-y-4 xl:col-span-3">
                <div class="sticky top-4 space-y-4">
                    <div class="overflow-hidden rounded-2xl border border-indigo-200/60 bg-gradient-to-br from-indigo-50/90 to-violet-50/50 p-4 shadow-lg backdrop-blur-xl dark:border-indigo-500/20 dark:from-indigo-950/50 dark:to-violet-950/30">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400"><?php echo e(__('Security intelligence')); ?></p>
                        <div class="mt-3 space-y-3">
                            <div>
                                <p class="text-xs text-slate-500"><?php echo e(__('Threat level')); ?></p>
                                <p class="text-2xl font-bold text-amber-600"><?php echo e($securityIntel['threat_label']); ?></p>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="rounded-xl bg-white/60 p-2 dark:bg-slate-900/50">
                                    <p class="text-lg font-bold text-emerald-600"><?php echo e($securityIntel['login_success_rate']); ?>%</p>
                                    <p class="text-[10px] text-slate-500"><?php echo e(__('Login success')); ?></p>
                                </div>
                                <div class="rounded-xl bg-white/60 p-2 dark:bg-slate-900/50">
                                    <p class="text-lg font-bold text-indigo-600"><?php echo e($securityIntel['mfa_adoption']); ?>%</p>
                                    <p class="text-[10px] text-slate-500"><?php echo e(__('MFA adoption')); ?></p>
                                </div>
                            </div>
                            <div class="rounded-xl bg-white/60 p-3 dark:bg-slate-900/50">
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-500"><?php echo e(__('Security score')); ?></span>
                                    <span class="font-bold text-slate-900 dark:text-white"><?php echo e($securityIntel['security_score']); ?>/100</span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-indigo-500" style="width: <?php echo e($securityIntel['security_score']); ?>%"></div>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500"><?php echo e(__('Open incidents')); ?></span>
                                <span class="font-bold text-rose-600"><?php echo e($securityIntel['open_incidents']); ?></span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500"><?php echo e(__('Access requests')); ?></span>
                                <span class="font-bold text-amber-600"><?php echo e($securityIntel['pending_access_requests']); ?> <?php echo e(__('pending')); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 p-4 shadow-card backdrop-blur dark:border-slate-800/80 dark:bg-slate-900/60">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Quick actions')); ?></h4>
                        <div class="mt-3 grid gap-2">
                            <button type="button" class="w-full rounded-xl border border-slate-200/80 py-2 text-left px-3 text-xs font-semibold hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"><?php echo e(__('Create role')); ?></button>
                            <button type="button" class="w-full rounded-xl border border-slate-200/80 py-2 text-left px-3 text-xs font-semibold hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"><?php echo e(__('Generate API token')); ?></button>
                            <button type="button" class="w-full rounded-xl border border-slate-200/80 py-2 text-left px-3 text-xs font-semibold hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"><?php echo e(__('Force logout all')); ?></button>
                            <button type="button" class="w-full rounded-xl bg-indigo-600 py-2 text-xs font-semibold text-white hover:brightness-110"><?php echo e(__('Run security scan')); ?></button>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 p-4 backdrop-blur dark:border-slate-800/80 dark:bg-slate-900/60">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Advanced governance')); ?></h4>
                        <ul class="mt-2 space-y-1.5 text-[11px] text-slate-600 dark:text-slate-400">
                            <li>· <?php echo e(__('Access approval workflows')); ?></li>
                            <li>· <?php echo e(__('Temporary access grants')); ?></li>
                            <li>· <?php echo e(__('Break-glass accounts')); ?></li>
                            <li>· <?php echo e(__('Delegated administration')); ?></li>
                            <li>· <?php echo e(__('Role expiration dates')); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal895f6ef515592ffd4805667c75b9d7a7)): ?>
<?php $attributes = $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7; ?>
<?php unset($__attributesOriginal895f6ef515592ffd4805667c75b9d7a7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal895f6ef515592ffd4805667c75b9d7a7)): ?>
<?php $component = $__componentOriginal895f6ef515592ffd4805667c75b9d7a7; ?>
<?php unset($__componentOriginal895f6ef515592ffd4805667c75b9d7a7); ?>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/users-roles/index.blade.php ENDPATH**/ ?>