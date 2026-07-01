<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'html',
    'paperSize' => 'A4',
    'title' => null,
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
    'html',
    'paperSize' => 'A4',
    'title' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $srcdoc = str_replace(['&', '"'], ['&amp;', '&quot;'], (string) $html);
    $size = strtoupper((string) $paperSize);
    [$frameWidth, $frameMinHeight] = match ($size) {
        'A5' => ['148mm', '210mm'],
        default => ['210mm', '297mm'],
    };
    $frameTitle = $title ?? __('Preview');
?>

<div class="document-preview-frame mx-auto w-full max-w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-100 p-2 dark:border-slate-700">
    <iframe
        srcdoc="<?php echo $srcdoc; ?>"
        sandbox="allow-same-origin"
        class="mx-auto block w-full max-w-full border-0 bg-white shadow-md"
        style="width: 100%; max-width: 100%; min-height: <?php echo e($frameMinHeight); ?>; aspect-ratio: <?php echo e($size === 'A5' ? '148 / 210' : '210 / 297'); ?>;"
        title="<?php echo e($frameTitle); ?>"
    ></iframe>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/billing/document-preview-frame.blade.php ENDPATH**/ ?>