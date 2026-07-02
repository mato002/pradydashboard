@props([
    'dialCode' => '254',
    'local' => '',
    'selectClass' => 'mt-1 block w-full rounded-xl border-slate-200/80 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100',
    'countryIso' => 'KE',
    'syncCountryField' => 'country',
])

@php
    use App\Support\Phone\EastAfricaPhone;

    $countries = EastAfricaPhone::countries();
    $dialCode = old('phone_dial_code', $dialCode);
    $local = old('phone_local', $local);
    $limitsJson = collect($countries)->mapWithKeys(fn ($c) => [
        $c['dial'] => ['min' => $c['local_min'], 'max' => $c['local_max'], 'iso' => $c['iso']],
    ])->toJson();
@endphp

<div
    {{ $attributes->merge(['class' => 'space-y-1']) }}
    x-data="{
        dial: @js($dialCode),
        local: @js($local),
        limits: {{ $limitsJson }},
        syncCountryField: @js($syncCountryField),
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
                return @js(__('Enter :digits digits without the leading 0.')).replace(':digits', String(l.min));
            }
            return @js(__('Enter :min–:max digits without the leading 0.'))
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
            <label for="phone_dial_code" class="sr-only">{{ __('Country code') }}</label>
            <select
                id="phone_dial_code"
                name="phone_dial_code"
                x-model="dial"
                @change="onDialChange()"
                class="{{ $selectClass }}"
            >
                @foreach ($countries as $country)
                    <option value="{{ $country['dial'] }}" @selected($dialCode === $country['dial'])>
                        +{{ $country['dial'] }} {{ $country['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-0 flex-1">
            <label for="phone_local" class="sr-only">{{ __('Phone number') }}</label>
            <input
                id="phone_local"
                name="phone_local"
                type="tel"
                inputmode="numeric"
                autocomplete="tel-national"
                placeholder="{{ __('e.g. 712345678') }}"
                x-model="local"
                @input="trimLocal()"
                :maxlength="currentLimit().max"
                class="{{ $selectClass }}"
            />
        </div>
    </div>
    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="hint()"></p>
    <x-input-error class="mt-1" :messages="$errors->get('phone_local')" />
    <x-input-error class="mt-1" :messages="$errors->get('phone_dial_code')" />
    <x-input-error class="mt-1" :messages="$errors->get('phone')" />
</div>
