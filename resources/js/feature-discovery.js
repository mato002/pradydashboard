const RECENT_STORAGE_KEY = 'prady.discovery.recent';
const FAVORITES_STORAGE_KEY = 'prady.discovery.favorites';
const RECENT_LIMIT = 8;
const SEARCH_RESULT_LIMIT = 24;
const SEARCH_DEBOUNCE_MS = 200;

function discoveryConfig() {
    return window.__pradyFeatureDiscovery ?? {};
}

function discoveryCatalog() {
    const catalog = discoveryConfig().catalog;

    return Array.isArray(catalog) ? catalog : [];
}

function discoveryCatalogHasSearchText() {
    return discoveryCatalog().some((entry) => String(entry?.search_text ?? '').trim() !== '');
}

function discoveryNormalizeSearchUrl(url) {
    if (!url) {
        return '';
    }

    try {
        const parsed = new URL(url, window.location.origin);

        return `${parsed.pathname}${parsed.search}`;
    } catch {
        return url;
    }
}

function discoverySearchUrl() {
    const configured =
        discoveryConfig().searchUrl
        || document.querySelector('[data-feature-discovery-url]')?.dataset?.featureDiscoveryUrl
        || '';

    return discoveryNormalizeSearchUrl(configured);
}

function discoveryEntryId(entry) {
    if (typeof entry === 'string') {
        return entry;
    }

    return entry?.id || '';
}

function discoveryNormalizeStoredEntries(entries) {
    if (!Array.isArray(entries)) {
        return [];
    }

    return entries.filter((entry) => entry && entry.id && entry.url);
}

function discoveryReadStored(key) {
    try {
        return discoveryNormalizeStoredEntries(JSON.parse(localStorage.getItem(key) || '[]'));
    } catch {
        return [];
    }
}

function discoveryWriteStored(key, entries) {
    try {
        localStorage.setItem(key, JSON.stringify(entries));
    } catch {
        // Storage may be unavailable in private browsing.
    }
}

function discoveryTokenize(value) {
    const normalized = String(value ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, ' ')
        .trim();

    if (!normalized) {
        return [];
    }

    return normalized.split(/\s+/).filter(Boolean);
}

function discoveryTokenMatches(token, haystack) {
    if (!token || !haystack) {
        return false;
    }

    if (haystack.includes(token)) {
        return true;
    }

    if (token.endsWith('s') && token.length > 3) {
        const singular = token.slice(0, -1);

        if (singular && haystack.includes(singular)) {
            return true;
        }
    }

    if (!token.endsWith('s') && haystack.includes(`${token}s`)) {
        return true;
    }

    return false;
}

function discoveryPresentResult(entry) {
    return {
        id: String(entry?.id ?? ''),
        label: String(entry?.label ?? ''),
        description: entry?.description ?? '',
        path: String(entry?.path ?? ''),
        url: String(entry?.url ?? ''),
        category: entry?.category ?? 'features',
    };
}

function discoveryPresentResults(entries) {
    if (!Array.isArray(entries)) {
        return [];
    }

    return entries
        .filter((entry) => entry && entry.id && entry.url)
        .map(discoveryPresentResult);
}

function discoveryGroupResults(results) {
    const buckets = {
        features: [],
        financials: [],
        settings: [],
        operations: [],
    };

    for (const item of results) {
        const key = buckets[item.category] ? item.category : 'features';
        buckets[key].push(item);
    }

    const labels = {
        features: 'Features',
        financials: 'Financials',
        settings: 'Settings',
        operations: 'Operations',
    };

    return Object.entries(buckets)
        .filter(([, items]) => items.length > 0)
        .map(([key, items]) => ({
            key,
            label: labels[key] ?? key,
            items,
        }));
}

function discoveryLocalSearch(query, limit = SEARCH_RESULT_LIMIT) {
    const tokens = discoveryTokenize(query);

    if (tokens.length === 0) {
        return [];
    }

    return discoveryCatalog()
        .filter((entry) => {
            const haystack = String(entry.search_text ?? '');

            return tokens.every((token) => discoveryTokenMatches(token, haystack));
        })
        .slice(0, limit)
        .map(discoveryPresentResult);
}

async function discoveryFetchRemoteResults(query, signal = null) {
    const searchUrl = discoverySearchUrl();
    const trimmed = String(query ?? '').trim();

    if (!trimmed) {
        return { results: [], error: null };
    }

    if (!searchUrl) {
        return { results: [], error: 'missing_url' };
    }

    const params = new URLSearchParams({ q: trimmed });
    const fetchOptions = {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    };

    if (signal) {
        fetchOptions.signal = signal;
    }

    const response = await fetch(`${searchUrl}?${params.toString()}`, fetchOptions);

    if (!response.ok) {
        return { results: [], error: `http_${response.status}` };
    }

    let payload;

    try {
        payload = await response.json();
    } catch {
        return { results: [], error: 'invalid_json' };
    }

    return {
        results: discoveryPresentResults(payload?.results),
        error: null,
    };
}

async function discoveryFetchResults(query, signal = null) {
    const trimmed = String(query ?? '').trim();

    if (!trimmed) {
        return { results: [], error: null };
    }

    const remote = await discoveryFetchRemoteResults(query, signal);

    if (remote.error) {
        return remote;
    }

    if (remote.results.length > 0) {
        return remote;
    }

    if (discoveryCatalog().length > 0 && discoveryCatalogHasSearchText()) {
        const localResults = discoveryLocalSearch(query);

        if (localResults.length > 0) {
            return { results: localResults, error: null };
        }
    }

    return remote;
}

export function createFeatureDiscoveryMixin() {
    return {
        paletteOpen: false,
        paletteQuery: '',
        paletteHighlightIndex: 0,
        paletteSearchResults: [],
        paletteSections: [],
        paletteLoading: false,
        paletteSearchSettled: false,
        paletteSearchError: null,
        paletteSearchTimer: null,
        paletteSearchAbort: null,
        paletteSearchGeneration: 0,
        discoveryFavorites: discoveryReadStored(FAVORITES_STORAGE_KEY),
        discoveryRecent: discoveryReadStored(RECENT_STORAGE_KEY),

        initFeatureDiscovery() {
            this.$watch('discoveryFavorites', (value) => {
                discoveryWriteStored(FAVORITES_STORAGE_KEY, value);
            });

            this.$watch('discoveryRecent', (value) => {
                discoveryWriteStored(RECENT_STORAGE_KEY, value);
            });
        },

        clearPaletteSearchResults() {
            this.paletteSearchResults = [];
            this.paletteSections = [];
        },

        applyPaletteSearchResults(results, error, query, generation, signal) {
            if (generation !== this.paletteSearchGeneration) {
                return;
            }

            if (signal?.aborted) {
                return;
            }

            if (!this.searchQueryStillMatches(query)) {
                return;
            }

            this.paletteSearchResults = [...results];
            this.paletteSections = discoveryGroupResults(results);
            this.paletteSearchError = error;
            this.paletteLoading = false;
            this.paletteSearchSettled = true;
            this.paletteSearchAbort = null;
        },

        cancelPaletteSearch() {
            clearTimeout(this.paletteSearchTimer);
            this.paletteSearchTimer = null;

            if (this.paletteSearchAbort) {
                this.paletteSearchAbort.abort();
                this.paletteSearchAbort = null;
            }
        },

        onPaletteQueryInput(event) {
            this.paletteQuery = String(event?.target?.value ?? '');
            this.paletteHighlightIndex = 0;
            this.schedulePaletteSearch();
        },

        paletteQueryIsActive() {
            return String(this.paletteQuery ?? '').trim() !== '';
        },

        schedulePaletteSearch() {
            clearTimeout(this.paletteSearchTimer);

            if (!this.paletteQueryIsActive()) {
                this.cancelPaletteSearch();
                this.clearPaletteSearchResults();
                this.paletteLoading = false;
                this.paletteSearchSettled = false;
                this.paletteSearchError = null;

                return;
            }

            this.paletteSearchTimer = setTimeout(() => this.runPaletteSearch(), SEARCH_DEBOUNCE_MS);
        },

        searchQueryStillMatches(query) {
            return String(this.paletteQuery ?? '').trim() === String(query ?? '').trim();
        },

        async runPaletteSearch() {
            const query = String(this.paletteQuery ?? '').trim();

            if (!query) {
                this.clearPaletteSearchResults();
                this.paletteLoading = false;
                this.paletteSearchSettled = false;
                this.paletteSearchError = null;

                return;
            }

            if (this.paletteSearchAbort) {
                this.paletteSearchAbort.abort();
            }

            this.paletteSearchAbort = new AbortController();
            const { signal } = this.paletteSearchAbort;
            const generation = ++this.paletteSearchGeneration;

            this.paletteLoading = true;
            this.paletteSearchSettled = false;
            this.paletteSearchError = null;
            this.paletteHighlightIndex = 0;

            try {
                const { results, error } = await discoveryFetchResults(query, signal);

                this.applyPaletteSearchResults(results, error, query, generation, signal);
            } catch (error) {
                if (error?.name === 'AbortError' || signal.aborted) {
                    return;
                }

                if (generation !== this.paletteSearchGeneration) {
                    return;
                }

                if (!this.searchQueryStillMatches(query)) {
                    return;
                }

                this.clearPaletteSearchResults();
                this.paletteSearchError = 'network';
                this.paletteLoading = false;
                this.paletteSearchSettled = true;
                this.paletteSearchAbort = null;
            }
        },

        get recentItems() {
            return this.discoveryRecent;
        },

        get favoriteDiscoveryItems() {
            return this.discoveryFavorites;
        },

        get paletteFlatResults() {
            return this.paletteSearchResults;
        },

        get paletteSelectableItems() {
            if (this.paletteQueryIsActive()) {
                return this.paletteSections.flatMap((section) => section.items);
            }

            return [...this.recentItems, ...this.favoriteDiscoveryItems];
        },

        paletteSectionOffset(sectionKey, index) {
            let offset = this.paletteQueryIsActive() ? 0 : this.recentItems.length;

            for (const section of this.paletteSections) {
                if (section.key === sectionKey) {
                    return offset + index;
                }

                offset += section.items.length;
            }

            if (!this.paletteQueryIsActive()) {
                return this.recentItems.length + index;
            }

            return index;
        },

        openPalette() {
            this.cancelPaletteSearch();
            this.paletteOpen = true;
            this.paletteQuery = '';
            this.paletteHighlightIndex = 0;
            this.paletteSearchError = null;
            this.clearPaletteSearchResults();
            this.paletteLoading = false;
            this.paletteSearchSettled = false;
            this.paletteSearchGeneration += 1;
            this.sidebarOpen = false;

            this.$nextTick(() => {
                this.$refs.paletteInput?.focus();
            });
        },

        closePalette() {
            this.cancelPaletteSearch();
            this.paletteOpen = false;
            this.paletteQuery = '';
            this.paletteHighlightIndex = 0;
            this.clearPaletteSearchResults();
            this.paletteLoading = false;
            this.paletteSearchSettled = false;
            this.paletteSearchError = null;
            this.paletteSearchGeneration += 1;
        },

        movePaletteSelection(step) {
            const total = this.paletteSelectableItems.length;

            if (total === 0) {
                return;
            }

            this.paletteHighlightIndex = (this.paletteHighlightIndex + step + total) % total;
        },

        openPaletteSelection() {
            const item = this.paletteSelectableItems[this.paletteHighlightIndex];

            if (item) {
                this.navigatePaletteItem(item);
            }
        },

        navigatePaletteItem(item) {
            if (!item?.url) {
                return;
            }

            this.recordRecent(item);
            this.closePalette();
            this.loadWorkspaceFromUrl(item.url);
        },

        openPaletteItemNewTab(item) {
            if (!item?.url) {
                return;
            }

            this.recordRecent(item);
            window.open(item.url, '_blank', 'noopener,noreferrer');
        },

        async copyPaletteItemLink(item) {
            if (!item?.url) {
                return;
            }

            try {
                await navigator.clipboard.writeText(new URL(item.url, window.location.origin).href);
            } catch {
                // Clipboard may be unavailable.
            }
        },

        isDiscoveryFavorite(id) {
            return this.discoveryFavorites.some((entry) => discoveryEntryId(entry) === id);
        },

        toggleDiscoveryFavorite(idOrItem) {
            const id = discoveryEntryId(idOrItem);
            const item = typeof idOrItem === 'object' && idOrItem !== null
                ? idOrItem
                : this.paletteSearchResults.find((entry) => entry.id === id);

            if (!id || !item) {
                return;
            }

            if (this.isDiscoveryFavorite(id)) {
                this.discoveryFavorites = this.discoveryFavorites.filter((entry) => discoveryEntryId(entry) !== id);

                return;
            }

            this.discoveryFavorites = [item, ...this.discoveryFavorites.filter((entry) => discoveryEntryId(entry) !== id)].slice(0, 12);
        },

        recordRecent(item) {
            if (!item?.id || !item?.url) {
                return;
            }

            this.discoveryRecent = [
                item,
                ...this.discoveryRecent.filter((entry) => discoveryEntryId(entry) !== item.id),
            ].slice(0, RECENT_LIMIT);
        },
    };
}
