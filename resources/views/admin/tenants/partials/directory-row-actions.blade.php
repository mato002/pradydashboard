{{-- Used inside Alpine x-for="tenant in filteredDirectory" --}}
<div
    class="relative inline-flex justify-end"
    x-data="{ menuOpen: false }"
    @click.stop
    @keydown.escape.window="menuOpen = false"
>
    <button
        type="button"
        @click="menuOpen = !menuOpen"
        class="inline-flex items-center justify-center rounded-lg p-1.5 text-slate-500 ring-1 ring-transparent transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
        :class="menuOpen ? 'bg-slate-100 text-slate-700 ring-slate-200/80 dark:bg-slate-800 dark:text-slate-200' : ''"
        :aria-expanded="menuOpen"
        aria-haspopup="menu"
    >
        <span class="sr-only">{{ __('Actions for') }} <span x-text="tenant.company"></span></span>
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <circle cx="12" cy="5" r="1.5" />
            <circle cx="12" cy="12" r="1.5" />
            <circle cx="12" cy="19" r="1.5" />
        </svg>
    </button>

    <div
        x-show="menuOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="menuOpen = false"
        class="absolute right-0 z-50 mt-1 max-h-[min(24rem,70vh)] w-56 overflow-y-auto rounded-xl border border-slate-200/90 bg-white py-1 shadow-lg ring-1 ring-black/5 dark:border-slate-700 dark:bg-slate-900 dark:ring-white/10"
        role="menu"
    >
        <p class="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Workspace') }}</p>
        <button
            type="button"
            role="menuitem"
            @click="menuOpen = false; openDrawer(tenant)"
            class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
        >
            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            {{ __('Quick preview') }}
        </button>
        <a
            :href="tenant.show_url"
            role="menuitem"
            @click="menuOpen = false"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
            {{ __('Open command center') }}
        </a>

        <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
        <p class="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Operations') }}</p>
        <a :href="tenant.billing_url" role="menuitem" @click="menuOpen = false" class="flex px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Billing') }}</a>
        <a :href="tenant.licensing_url" role="menuitem" @click="menuOpen = false" class="flex px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Licensing') }}</a>
        <a :href="tenant.infrastructure_url" role="menuitem" @click="menuOpen = false" class="flex px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Infrastructure') }}</a>
        <a :href="tenant.modules_url" role="menuitem" @click="menuOpen = false" class="flex px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Modules') }}</a>
        <a :href="tenant.integrations_url" role="menuitem" @click="menuOpen = false" class="flex px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Integrations') }}</a>
        <a :href="tenant.support_url" role="menuitem" @click="menuOpen = false" class="flex px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Support') }}</a>
        <a :href="tenant.documents_url" role="menuitem" @click="menuOpen = false" class="flex px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Documents') }}</a>
        <a :href="tenant.projects_url" role="menuitem" @click="menuOpen = false" class="flex px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Projects') }}</a>
        <a :href="tenant.monitoring_url" role="menuitem" @click="menuOpen = false" class="flex px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Monitoring') }}</a>

        <template x-if="tenant.can_update">
            <div>
                <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
                <p class="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Account') }}</p>
                <a :href="tenant.edit_url" role="menuitem" @click="menuOpen = false" class="flex px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Edit tenant') }}</a>
                <button
                    type="button"
                    role="menuitem"
                    @click="menuOpen = false; openStatusModal(tenant)"
                    class="flex w-full px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    {{ __('Change status…') }}
                </button>

                <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
                <p class="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Set status') }}</p>
                <template x-for="item in quickStatuses" :key="item.value">
                    <form
                        x-show="tenant.status !== item.value"
                        :action="tenant.status_url"
                        method="post"
                        class="block"
                        @submit="menuOpen = false"
                    >
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="status" :value="item.value">
                        <button type="submit" role="menuitem" class="flex w-full px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800" x-text="item.label"></button>
                    </form>
                </template>
            </div>
        </template>

        <template x-if="tenant.can_delete">
            <div>
                <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
                <form
                    :action="tenant.destroy_url"
                    method="post"
                    class="block"
                    onsubmit="return confirm(@json(__('Delete this tenant? This cannot be undone.')))"
                    @submit="menuOpen = false"
                >
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" role="menuitem" class="flex w-full px-3 py-2 text-left text-sm font-medium text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">
                        {{ __('Delete tenant') }}
                    </button>
                </form>
            </div>
        </template>
    </div>
</div>
