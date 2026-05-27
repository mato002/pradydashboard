<x-dashboard-layout :heading="__('Add product')" :subheading="__('Register a PradytecAI software system')">
    <x-admin.form-shell
        :title="__('New product')"
        :subtitle="__('Define the main software system (MFI, Property, CRM, SpareMe, etc.). Hosted domains are added separately under Hosted Projects.')"
        :badge="__('Products')"
        :back-href="route('products.index')"
        :back-label="__('Back to products')"
    >
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200/80 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-200">
                <p class="font-semibold">{{ __('Please correct the following:') }}</p>
                <ul class="mt-2 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('products.store') }}" class="max-w-4xl space-y-5">
            @csrf
            @include('admin.products._form', ['product' => $product])
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    {{ __('Create product') }}
                </button>
                <a href="{{ route('products.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
