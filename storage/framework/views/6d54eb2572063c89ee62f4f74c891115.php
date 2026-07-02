<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'dialCode' => '254',
    'local' => '',
    'selectClass' => 'mt-1 block w-full rounded-xl border-slate-200/80 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100',
    'countryIso' => 'KE',
    'syncCountryField' => 'country',
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
    'dialCode' => '254',
    'local' => '',
    'selectClass' => 'mt-1 block w-full rounded-xl border-slate-200/80 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100',
    'countryIso' => 'KE',
    'syncCountryField' => 'country',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Support\Phone\EastAfricaPhone;

    $countries = EastAfricaPhone::countries();
    $dialCode = old('phone_dial_code', $dialCode);
    $local = old('phone_local', $local);
    $limitsJson = collect($countries)->mapWithKeys(fn ($c) => [
        $c['dial'] => ['min' => $c['local_min'], 'max' => $c['local_max'], 'iso' => $c['iso']],
    ])->toJson();
?>

<div
    <?php echo e($attributes->merge(['class' => 'space-y-1'])); ?>

    x-data="{
        dial: <?php echo \Illuminate\Support\Js::from($dialCode)->toHtml() ?>,
        local: <?php echo \Illuminate\Support\Js::from($local)->toHtml() ?>,
        limits: <?php echo e($limitsJson); ?>,
        syncCountryField: <?php echo \Illuminate\Support\Js::from($syncCountryField)->toHtml() ?>,
        currentLimit() {
            return this.limits[this.dial] ?? { min: 9, max: 9, iso: 'KE' };
        },
        onDialChange() {
            const countryField = document.getElementById(this.syncCountryField);
            const iso = this.currentLimit().iso;
            if (countryField && iso) {
                countryField.value = iso;
            }
            this.trimLocal();
        },
        onCountryChange(event) {
            const iso = (event?.target?.value || '').toUpperCase();
            const match = Object.values(this.limits).find(l => l.iso === iso);
            if (match) {
                const dial = Object.keys(this.limits).find(d => this.limits[d].iso === iso);
                if (dial) {
                    this.dial = dial;
                }
            }
            this.trimLocal();
        },
        trimLocal() {
            const max = this.currentLimit().max;
            this.local = String(this.local).replace(/\D/g, '').replace(/^0+/, '').slice(0, max);
        },
        hint() {
            const l = this.currentLimit();
            if (l.min === l.max) {
                return <?php echo \Illuminate\Support\Js::from(__('Enter :digits digits without the leading 0.'))->toHtml() ?>.replace(':digits', String(l.min));
            }
            return <?php echo \Illuminate\Support\Js::from(__('Enter :min–:max digits without the leading 0.'))->toHtml() ?>
                .replace(':min', String(l.min))
                .replace(':max', String(l.max));
        }
    }"
    x-init="
        trimLocal();
        const countryField = document.getElementById(syncCountryField);
        if (countryField) {
            countryField.addEventListener('change', onCountryChange);
            countryField.addEventListener('input', onCountryChange);
        }
    "
>
    <div class="flex gap-2">
        <div class="w-[11.5rem] shrink-0">
            <label for="phone_dial_code" class="sr-only"><?php echo e(__('Country code')); ?></label>
            <select
                id="phone_dial_code"
                name="phone_dial_code"
                x-model="dial"
                @change="onDialChange()"
                class="<?php echo e($selectClass); ?>"
            >
                <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($country['dial']); ?>" <?php if($dialCode === $country['dial']): echo 'selected'; endif; ?>>
                        +<?php echo e($country['dial']); ?> <?php echo e($country['name']); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="min-w-0 flex-1">
            <label for="phone_local" class="sr-only"><?php echo e(__('Phone number')); ?></label>
            <input
                id="phone_local"
                name="phone_local"
                type="tel"
                inputmode="numeric"
                autocomplete="tel-national"
                placeholder="<?php echo e(__('e.g. 712345678')); ?>"
                x-model="local"
                @input="trimLocal()"
                :maxlength="currentLimit().max"
                class="<?php echo e($selectClass); ?>"
            />
        </div>
    </div>
    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="hint()"></p>
    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['class' => 'mt-1','messages' => $errors->get('phone_local')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-1','messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('phone_local'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['class' => 'mt-1','messages' => $errors->get('phone_dial_code')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-1','messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('phone_dial_code'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['class' => 'mt-1','messages' => $errors->get('phone')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-1','messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('phone'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/phone-input.blade.php ENDPATH**/ ?>