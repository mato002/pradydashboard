@php
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100 dark:placeholder:text-slate-500';
    $selectClass = $inputClass;
    $textareaClass = $inputClass.' resize-y min-h-[88px]';
@endphp

<div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <div class="space-y-6 p-6 sm:p-8">
        <x-admin.form-section :title="__('Product identity')" :description="__('Name and slug used in the license API (product_key).')">
            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <x-input-label for="name" :value="__('Product name')" :required="true" />
                    <x-text-input id="name" name="name" type="text" :class="$inputClass" :value="old('name', $product->name)" placeholder="{{ __('e.g. Prady MFI System') }}" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="slug" :value="__('Slug (license API product_key)')" />
                    <x-text-input id="slug" name="slug" type="text" :class="$inputClass.' font-mono'" :value="old('slug', $product->slug)" placeholder="{{ __('e.g. mfi') }}" />
                    <p class="mt-1.5 text-xs text-slate-500">{{ __('Lowercase. Auto-generated from name if left empty.') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                </div>
                <div>
                    <x-input-label for="category" :value="__('Category')" />
                    <x-text-input id="category" name="category" type="text" :class="$inputClass" :value="old('category', $product->category)" placeholder="{{ __('e.g. saas, fintech') }}" />
                    <x-input-error class="mt-2" :messages="$errors->get('category')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" class="{{ $textareaClass }}" placeholder="{{ __('Short description of this software product…') }}">{{ old('description', $product->description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>
            </div>
        </x-admin.form-section>

        <x-admin.form-section :title="__('Defaults')" :description="__('Default billing and license behavior for new tenants on this product.')">
            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <x-input-label for="status" :value="__('Status')" :required="true" />
                    <select id="status" name="status" class="{{ $selectClass }}" required>
                        @foreach (['active', 'suspended', 'archived'] as $st)
                            <option value="{{ $st }}" @selected(old('status', $product->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('status')" />
                </div>
                <div>
                    <x-input-label for="default_billing_model" :value="__('Billing model')" :required="true" />
                    <select id="default_billing_model" name="default_billing_model" class="{{ $selectClass }}" required>
                        @foreach (['subscription', 'per_seat', 'usage', 'enterprise'] as $m)
                            <option value="{{ $m }}" @selected(old('default_billing_model', $product->default_billing_model ?? 'subscription') === $m)>{{ str_replace('_', ' ', ucfirst($m)) }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('default_billing_model')" />
                </div>
                <div>
                    <x-input-label for="default_license_mode" :value="__('License mode')" :required="true" />
                    <select id="default_license_mode" name="default_license_mode" class="{{ $selectClass }}" required>
                        @foreach (['module', 'full', 'feature_flags'] as $m)
                            <option value="{{ $m }}" @selected(old('default_license_mode', $product->default_license_mode ?? 'module') === $m)>{{ str_replace('_', ' ', ucfirst($m)) }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('default_license_mode')" />
                </div>
            </div>
        </x-admin.form-section>
    </div>
</div>
