<x-dashboard-layout :heading="__('Edit product')" :subheading="$product->name">
    <x-admin.form-shell
        :title="$product->name"
        :subtitle="__('Update product catalog entry.')"
        :badge="__('Products')"
        :back-href="route('products.show', $product)"
        :back-label="__('Back to product')"
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

        <form method="post" action="{{ route('products.update', $product) }}" class="max-w-4xl space-y-5">
            @csrf
            @method('PUT')
            @include('admin.products._form', ['product' => $product])
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Save changes') }}
                </button>
                <a href="{{ route('products.show', $product) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>

        <form method="post" action="{{ route('products.destroy', $product) }}" class="mt-4 max-w-4xl" onsubmit="return confirm(@js(__('Delete this product? Remove or reassign hosted projects and tenants first.')));">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-rose-200/80 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300">
                {{ __('Delete product') }}
            </button>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
