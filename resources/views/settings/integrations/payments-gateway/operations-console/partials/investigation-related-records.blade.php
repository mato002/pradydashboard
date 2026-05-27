@if (! empty($relatedLinks) || ! empty($relatedRecords))
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Related records') }}</h3>

        @if (! empty($relatedRecords))
            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/40">
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">{{ __('Type') }}</th>
                            <th class="px-3 py-2">{{ __('UUID') }}</th>
                            <th class="px-3 py-2">{{ __('Status') }}</th>
                            <th class="px-3 py-2">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($relatedRecords as $record)
                            @php
                                $recordType = (string) ($record['type'] ?? '');
                                $recordUuid = (string) ($record['uuid'] ?? '');
                                $recordUrl = collect($relatedLinks ?? [])->first(fn ($link) => str_contains($link['url'] ?? '', $recordUuid))['url'] ?? null;
                            @endphp
                            <tr>
                                <td class="px-3 py-2 text-xs">{{ str_replace('_', ' ', ucfirst($recordType)) }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $recordUuid !== '' ? substr($recordUuid, 0, 8).'…' : '—' }}</td>
                                <td class="px-3 py-2 text-xs">{{ ucfirst((string) ($record['status'] ?? '—')) }}</td>
                                <td class="px-3 py-2 text-xs">
                                    @if (filled($recordUrl))
                                        <a href="{{ $recordUrl }}" class="font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Open') }}</a>
                                    @else
                                        <span class="text-slate-400">{{ __('No dashboard link') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if (! empty($relatedLinks))
            <div class="mt-4 flex flex-wrap gap-3">
                @foreach ($relatedLinks as $link)
                    <a href="{{ $link['url'] }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $link['label'] }}</a>
                @endforeach
            </div>
        @endif
    </div>
@endif
