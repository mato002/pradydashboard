<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'heading' => null,
    'subheading' => null,
    'documentTitle' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'heading' => null,
    'subheading' => null,
    'documentTitle' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Support\Admin\PradyWorkspaceRequest;
?>

<?php if(PradyWorkspaceRequest::isPartial(request())): ?>
    <?php if (isset($component)) { $__componentOriginal33ec30da9d8dc117b4458e65beaddbf8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal33ec30da9d8dc117b4458e65beaddbf8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.prady-workspace-content','data' => ['heading' => $heading,'subheading' => $subheading,'documentTitle' => $documentTitle]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('prady-workspace-content'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($heading),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subheading),'document-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documentTitle)]); ?>
        <?php echo e($slot); ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal33ec30da9d8dc117b4458e65beaddbf8)): ?>
<?php $attributes = $__attributesOriginal33ec30da9d8dc117b4458e65beaddbf8; ?>
<?php unset($__attributesOriginal33ec30da9d8dc117b4458e65beaddbf8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal33ec30da9d8dc117b4458e65beaddbf8)): ?>
<?php $component = $__componentOriginal33ec30da9d8dc117b4458e65beaddbf8; ?>
<?php unset($__componentOriginal33ec30da9d8dc117b4458e65beaddbf8); ?>
<?php endif; ?>
<?php else: ?>
    <?php if (isset($component)) { $__componentOriginalc6abd3bdd3aec1cad6a194b833439448 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc6abd3bdd3aec1cad6a194b833439448 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.prady-shell','data' => ['heading' => $heading,'subheading' => $subheading,'documentTitle' => $documentTitle]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('prady-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($heading),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subheading),'document-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documentTitle)]); ?>
        <?php if (isset($component)) { $__componentOriginal33ec30da9d8dc117b4458e65beaddbf8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal33ec30da9d8dc117b4458e65beaddbf8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.prady-workspace-content','data' => ['heading' => $heading,'subheading' => $subheading,'documentTitle' => $documentTitle]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('prady-workspace-content'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($heading),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subheading),'document-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documentTitle)]); ?>
            <?php echo e($slot); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal33ec30da9d8dc117b4458e65beaddbf8)): ?>
<?php $attributes = $__attributesOriginal33ec30da9d8dc117b4458e65beaddbf8; ?>
<?php unset($__attributesOriginal33ec30da9d8dc117b4458e65beaddbf8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal33ec30da9d8dc117b4458e65beaddbf8)): ?>
<?php $component = $__componentOriginal33ec30da9d8dc117b4458e65beaddbf8; ?>
<?php unset($__componentOriginal33ec30da9d8dc117b4458e65beaddbf8); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc6abd3bdd3aec1cad6a194b833439448)): ?>
<?php $attributes = $__attributesOriginalc6abd3bdd3aec1cad6a194b833439448; ?>
<?php unset($__attributesOriginalc6abd3bdd3aec1cad6a194b833439448); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc6abd3bdd3aec1cad6a194b833439448)): ?>
<?php $component = $__componentOriginalc6abd3bdd3aec1cad6a194b833439448; ?>
<?php unset($__componentOriginalc6abd3bdd3aec1cad6a194b833439448); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/dashboard-layout.blade.php ENDPATH**/ ?>