<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginal1e11b7899a52332f9ff31b371c8c12e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e11b7899a52332f9ff31b371c8c12e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.auth-card','data' => ['title' => __('Welcome back'),'subtitle' => __('Sign in to your infrastructure control center')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth.auth-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Welcome back')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Sign in to your infrastructure control center'))]); ?>
        
        <div class="mb-5 flex flex-wrap items-center justify-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/25 bg-emerald-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                <?php echo e(__('Secure login')); ?>

            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-indigo-700 dark:text-indigo-300">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <?php echo e(__('MFA ready')); ?>

            </span>
        </div>

        <?php if (isset($component)) { $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-session-status','data' => ['class' => 'mb-4 rounded-xl border border-emerald-200/80 bg-emerald-50/90 px-4 py-2.5 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200','status' => session('status')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth-session-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4 rounded-xl border border-emerald-200/80 bg-emerald-50/90 px-4 py-2.5 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('status'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $attributes = $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $component = $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>

        <form
            method="POST"
            action="<?php echo e(route('login')); ?>"
            class="space-y-4"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            <?php echo csrf_field(); ?>

            <?php if (isset($component)) { $__componentOriginaleea2d662cb19ffc163e6ecec4b363b4b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleea2d662cb19ffc163e6ecec4b363b4b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.auth-input','data' => ['label' => __('Email'),'name' => 'email','type' => 'email','value' => old('email'),'required' => true,'autofocus' => true,'autocomplete' => 'username','hint' => __('Use your work or organization email')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth.auth-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Email')),'name' => 'email','type' => 'email','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('email')),'required' => true,'autofocus' => true,'autocomplete' => 'username','hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Use your work or organization email'))]); ?>
                 <?php $__env->slot('icon', null, []); ?> 
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                 <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleea2d662cb19ffc163e6ecec4b363b4b)): ?>
<?php $attributes = $__attributesOriginaleea2d662cb19ffc163e6ecec4b363b4b; ?>
<?php unset($__attributesOriginaleea2d662cb19ffc163e6ecec4b363b4b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleea2d662cb19ffc163e6ecec4b363b4b)): ?>
<?php $component = $__componentOriginaleea2d662cb19ffc163e6ecec4b363b4b; ?>
<?php unset($__componentOriginaleea2d662cb19ffc163e6ecec4b363b4b); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginaleea2d662cb19ffc163e6ecec4b363b4b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleea2d662cb19ffc163e6ecec4b363b4b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.auth-input','data' => ['label' => __('Password'),'name' => 'password','type' => 'password','revealable' => true,'required' => true,'autocomplete' => 'current-password']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth.auth-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Password')),'name' => 'password','type' => 'password','revealable' => true,'required' => true,'autocomplete' => 'current-password']); ?>
                 <?php $__env->slot('icon', null, []); ?> 
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                 <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleea2d662cb19ffc163e6ecec4b363b4b)): ?>
<?php $attributes = $__attributesOriginaleea2d662cb19ffc163e6ecec4b363b4b; ?>
<?php unset($__attributesOriginaleea2d662cb19ffc163e6ecec4b363b4b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleea2d662cb19ffc163e6ecec4b363b4b)): ?>
<?php $component = $__componentOriginaleea2d662cb19ffc163e6ecec4b363b4b; ?>
<?php unset($__componentOriginaleea2d662cb19ffc163e6ecec4b363b4b); ?>
<?php endif; ?>

            <div class="flex flex-wrap items-center justify-between gap-2 pt-0.5">
                <label for="remember_me" class="inline-flex cursor-pointer select-none items-center gap-2">
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded border-slate-300 bg-white text-indigo-600 shadow-sm transition focus:ring-4 focus:ring-indigo-500/25 dark:border-white/15 dark:bg-slate-950 dark:text-indigo-400"
                    >
                    <span class="text-[13px] font-medium text-slate-600 dark:text-slate-300"><?php echo e(__('Remember me')); ?></span>
                </label>

                <?php if(Route::has('password.request')): ?>
                    <a
                        class="text-[13px] font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-cyan-400 dark:hover:text-cyan-300"
                        href="<?php echo e(route('password.request')); ?>"
                    >
                        <?php echo e(__('Forgot password?')); ?>

                    </a>
                <?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginal1d578dcda8ad58f27d77428ead0e5247 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1d578dcda8ad58f27d77428ead0e5247 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.auth-button','data' => ['loadingText' => __('Signing in…')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth.auth-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['loading-text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Signing in…'))]); ?>
                <?php echo e(__('Sign In')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1d578dcda8ad58f27d77428ead0e5247)): ?>
<?php $attributes = $__attributesOriginal1d578dcda8ad58f27d77428ead0e5247; ?>
<?php unset($__attributesOriginal1d578dcda8ad58f27d77428ead0e5247); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1d578dcda8ad58f27d77428ead0e5247)): ?>
<?php $component = $__componentOriginal1d578dcda8ad58f27d77428ead0e5247; ?>
<?php unset($__componentOriginal1d578dcda8ad58f27d77428ead0e5247); ?>
<?php endif; ?>

            <?php if(Route::has('register')): ?>
                <p class="text-center text-[13px] text-slate-500 dark:text-slate-400">
                    <?php echo e(__('New to the platform?')); ?>

                    <a
                        class="font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-cyan-400"
                        href="<?php echo e(route('register')); ?>"
                    >
                        <?php echo e(__('Create an account')); ?>

                    </a>
                </p>
            <?php endif; ?>
        </form>

        
        <div class="mt-6 space-y-4 border-t border-slate-200/80 pt-5 dark:border-white/[0.08]">
            <div class="flex items-start gap-2.5 rounded-xl border border-slate-200/70 bg-slate-50/80 px-3 py-2.5 dark:border-white/[0.08] dark:bg-white/[0.03]">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                        <?php echo e(__('Encrypted authentication')); ?>

                    </p>
                    <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                        <?php echo e(__('Protected by enterprise-grade security. Sessions are encrypted and monitored for anomalous access.')); ?>

                    </p>
                </div>
            </div>

            <p class="text-center text-[10px] font-medium uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">
                <?php echo e(__('Protected by enterprise-grade security')); ?>

            </p>

            <div class="flex flex-wrap items-center justify-center gap-2">
                <?php $__currentLoopData = [__('SOC 2 aligned'), __('TLS 1.3'), __('RBAC'), __('Audit logs')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="rounded-md border border-slate-200/80 bg-white px-2 py-1 text-[10px] font-semibold text-slate-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                        <?php echo e($badge); ?>

                    </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <p class="text-center text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                <svg class="mr-1 inline h-3.5 w-3.5 -mt-px text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <?php echo e(__('Active session protection enabled on this device')); ?>

            </p>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e11b7899a52332f9ff31b371c8c12e9)): ?>
<?php $attributes = $__attributesOriginal1e11b7899a52332f9ff31b371c8c12e9; ?>
<?php unset($__attributesOriginal1e11b7899a52332f9ff31b371c8c12e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e11b7899a52332f9ff31b371c8c12e9)): ?>
<?php $component = $__componentOriginal1e11b7899a52332f9ff31b371c8c12e9; ?>
<?php unset($__componentOriginal1e11b7899a52332f9ff31b371c8c12e9); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/auth/login.blade.php ENDPATH**/ ?>