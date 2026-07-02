<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <script>
            (function () {
                try {
                    var t = localStorage.getItem('prady-theme') || 'light';
                    var dark = t === 'dark' || (t === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    document.documentElement.classList.toggle('dark', dark);
                } catch (e) {}
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:500,600,700&display=swap" rel="stylesheet" />

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="h-full font-sans antialiased">
        <?php if (isset($component)) { $__componentOriginalbb976ff2216c5909421148b1eb07d1bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb976ff2216c5909421148b1eb07d1bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.auth-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth.auth-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <?php echo e($slot); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb976ff2216c5909421148b1eb07d1bc)): ?>
<?php $attributes = $__attributesOriginalbb976ff2216c5909421148b1eb07d1bc; ?>
<?php unset($__attributesOriginalbb976ff2216c5909421148b1eb07d1bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb976ff2216c5909421148b1eb07d1bc)): ?>
<?php $component = $__componentOriginalbb976ff2216c5909421148b1eb07d1bc; ?>
<?php unset($__componentOriginalbb976ff2216c5909421148b1eb07d1bc); ?>
<?php endif; ?>
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/layouts/guest.blade.php ENDPATH**/ ?>