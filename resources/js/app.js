import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

function applyThemeClass(mode) {
    const root = document.documentElement;
    if (mode === 'dark') {
        root.classList.add('dark');
    } else if (mode === 'light') {
        root.classList.remove('dark');
    } else {
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            root.classList.add('dark');
        } else {
            root.classList.remove('dark');
        }
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.store('sidebar', {
        groups: {},

        init() {
            try {
                this.groups = JSON.parse(localStorage.getItem('prady-sidebar-groups') || '{}');
            } catch {
                this.groups = {};
            }
        },

        isGroupOpen(id, defaultOpen) {
            if (Object.prototype.hasOwnProperty.call(this.groups, id)) {
                return this.groups[id];
            }

            return defaultOpen;
        },

        toggleGroup(id, defaultOpen) {
            const next = !this.isGroupOpen(id, defaultOpen);
            this.groups = { ...this.groups, [id]: next };
            localStorage.setItem('prady-sidebar-groups', JSON.stringify(this.groups));
        },
    });

    Alpine.store('sidebar').init();

    Alpine.data('pradyShell', () => ({
        sidebarOpen: false,
        sidebarCollapsed: false,
        theme: localStorage.getItem('prady-theme') || 'light',
        dateMenuOpen: false,
        notifOpen: false,
        searchOpen: false,
        workspaceLoading: false,

        init() {
            applyThemeClass(this.theme === 'system' ? 'system' : this.theme);
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.theme === 'system') {
                    applyThemeClass('system');
                }
            });

            document.addEventListener('click', (event) => this.handleWorkspaceLinkClick(event));

            window.addEventListener('popstate', () => {
                if (window.history.state?.tenantTab !== undefined) {
                    return;
                }
                this.loadWorkspaceFromUrl(window.location.href, false);
            });
        },

        shouldHandleWorkspaceLink(link) {
            if (!link?.href || link.hasAttribute('data-prady-full-nav') || link.hasAttribute('data-tenant-full-nav')) {
                return false;
            }
            if (link.target === '_blank' || link.hasAttribute('download')) {
                return false;
            }
            if (link.closest('#tenant-workspace-root')) {
                return false;
            }

            const href = link.getAttribute('href') || '';
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
                return false;
            }

            let target;
            try {
                target = new URL(link.href, window.location.origin);
            } catch {
                return false;
            }

            if (target.origin !== window.location.origin) {
                return false;
            }

            const skipPaths = ['/logout', '/login', '/register', '/confirm-password'];
            if (skipPaths.some((p) => target.pathname === p || target.pathname.startsWith(p + '/'))) {
                return false;
            }

            const inSidebar = link.closest('aside');
            const inWorkspace = link.closest('#prady-workspace-content');

            return Boolean(inSidebar || inWorkspace);
        },

        handleWorkspaceLinkClick(event) {
            const link = event.target.closest('a[href]');
            if (!this.shouldHandleWorkspaceLink(link)) {
                return;
            }

            event.preventDefault();
            this.sidebarOpen = false;
            this.loadWorkspaceFromUrl(link.href);
        },

        updatePageChrome(workspaceEl, doc) {
            const heading = workspaceEl?.dataset?.pageHeading;
            const subheading = workspaceEl?.dataset?.pageSubheading;
            const documentTitle = workspaceEl?.dataset?.documentTitle;

            const headingEl = document.getElementById('prady-page-heading');
            if (headingEl && heading) {
                headingEl.textContent = heading;
            }

            const subheadingEl = document.getElementById('prady-page-subheading');
            if (subheadingEl && subheading) {
                subheadingEl.textContent = subheading;
            }

            if (documentTitle) {
                document.title = documentTitle;
            } else if (doc?.querySelector('title')?.textContent) {
                document.title = doc.querySelector('title').textContent;
            }

            this.updateSidebarActiveState(window.location.pathname);
        },

        updateSidebarActiveState(pathname) {
            const active =
                'bg-gradient-to-r from-indigo-500/20 to-violet-500/10 text-white shadow-inner shadow-indigo-500/10 ring-1 ring-inset ring-white/10';
            const idle = 'text-slate-400 hover:bg-white/5 hover:text-white';
            const shared = 'group flex items-center gap-3 rounded-xl px-3 py-2 transition';

            document.querySelectorAll('aside nav a[href]').forEach((anchor) => {
                let linkPath;
                try {
                    linkPath = new URL(anchor.href, window.location.origin).pathname;
                } catch {
                    return;
                }

                const isActive =
                    pathname === linkPath ||
                    (linkPath !== '/' && linkPath.length > 1 && pathname.startsWith(linkPath + '/'));

                anchor.className = `${shared} ${isActive ? active : idle}`;
            });
        },

        async loadWorkspaceFromUrl(url, pushState = true) {
            if (this.workspaceLoading) {
                return;
            }

            this.workspaceLoading = true;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const headers = {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Prady-Workspace': '1',
                };
                if (token) {
                    headers['X-CSRF-TOKEN'] = token;
                }

                const response = await fetch(url, {
                    headers,
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    window.location.href = url;
                    return;
                }

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const next = doc.getElementById('prady-workspace-content');
                const current = document.getElementById('prady-workspace-content');

                if (!next || !current) {
                    window.location.href = url;
                    return;
                }

                current.replaceWith(next);

                if (window.Alpine) {
                    window.Alpine.initTree(next);
                }

                this.updatePageChrome(next, doc);

                if (pushState) {
                    window.history.pushState({ pradyNav: true }, '', url);
                }

                window.scrollTo({ top: 0, behavior: 'auto' });
            } catch {
                window.location.href = url;
            } finally {
                this.workspaceLoading = false;
            }
        },
        setTheme(mode) {
            this.theme = mode;
            localStorage.setItem('prady-theme', mode);
            applyThemeClass(mode === 'system' ? 'system' : mode);
        },
        cycleTheme() {
            const order = ['light', 'dark', 'system'];
            const i = order.indexOf(this.theme);
            this.setTheme(order[(i + 1) % order.length]);
        },
    }));

    Alpine.data('countUp', (target, duration = 900) => ({
        display: 0,
        init() {
            const end = Number(target) || 0;
            const start = performance.now();
            const tick = (now) => {
                const t = Math.min(1, (now - start) / duration);
                const eased = 1 - Math.pow(1 - t, 3);
                this.display = Math.round(end * eased);
                if (t < 1) {
                    requestAnimationFrame(tick);
                } else {
                    this.display = end;
                }
            };
            requestAnimationFrame(tick);
        },
    }));

    Alpine.data('countUpDecimal', (target, duration = 1100) => ({
        display: '0.00',
        init() {
            const end = Number(target) || 0;
            const start = performance.now();
            const tick = (now) => {
                const t = Math.min(1, (now - start) / duration);
                const eased = 1 - Math.pow(1 - t, 3);
                const val = end * eased;
                this.display = val.toFixed(2);
                if (t < 1) {
                    requestAnimationFrame(tick);
                } else {
                    this.display = end.toFixed(2);
                }
            };
            requestAnimationFrame(tick);
        },
    }));

    Alpine.data('iamCenter', (users, roles, permissions) => ({
        users,
        roles,
        permissions,
        activeTab: 'users',
        searchQuery: '',
        filterStatus: '',
        filterRisk: '',
        selectedRole: roles[0]?.slug ?? null,

        get filteredUsers() {
            return this.users.filter((u) => {
                if (this.filterStatus && u.status !== this.filterStatus) {
                    return false;
                }
                if (this.filterRisk && u.risk !== this.filterRisk) {
                    return false;
                }
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    if (!u.name.toLowerCase().includes(q) && !u.email.toLowerCase().includes(q)) {
                        return false;
                    }
                }

                return true;
            });
        },
    }));

    Alpine.data('tenantWorkspace', (config) => ({
        baseUrl: config.baseUrl,
        activeTab: config.initialTab,
        tabs: config.tabs ?? [],
        loading: false,

        init() {
            this.syncTabFromLocation(false);

            this.$el.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');
                if (!link || link.hasAttribute('data-tenant-full-nav')) {
                    return;
                }

                let target;
                try {
                    target = new URL(link.href, window.location.origin);
                } catch {
                    return;
                }

                const base = new URL(this.baseUrl, window.location.origin);
                if (target.pathname !== base.pathname) {
                    return;
                }

                const tab = target.searchParams.get('tab') || 'overview';
                if (!this.tabs.includes(tab)) {
                    return;
                }

                event.preventDefault();
                this.navigateToUrl(link.href, tab);
            });

            window.addEventListener('popstate', () => {
                this.syncTabFromLocation(true);
            });
        },

        syncTabFromLocation(fetchPanel) {
            const url = new URL(window.location.href);
            const tab = url.searchParams.get('tab') || 'overview';
            if (!this.tabs.includes(tab)) {
                return;
            }
            this.activeTab = tab;
            if (fetchPanel) {
                this.navigateToUrl(url.toString(), tab, false);
            }
        },

        navigate(tab) {
            const url = new URL(this.baseUrl, window.location.origin);
            url.searchParams.set('tab', tab);
            this.navigateToUrl(url.toString(), tab);
        },

        async navigateToUrl(url, tab = null, pushState = true) {
            if (this.loading) {
                return;
            }

            const resolvedTab =
                tab ||
                new URL(url, window.location.origin).searchParams.get('tab') ||
                'overview';

            this.activeTab = resolvedTab;
            this.loading = true;

            const panel = document.getElementById('tenant-workspace-panel');
            const scrollY = window.scrollY;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const headers = {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Tenant-Workspace': '1',
                    'X-Prady-Workspace': '1',
                };
                if (token) {
                    headers['X-CSRF-TOKEN'] = token;
                }

                const response = await fetch(url, {
                    headers,
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    window.location.href = url;
                    return;
                }

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const next = doc.getElementById('tenant-workspace-panel');

                if (panel && next) {
                    panel.innerHTML = next.innerHTML;
                    panel.dataset.tenantTab = next.dataset.tenantTab || resolvedTab;
                    if (next.getAttribute('aria-label')) {
                        panel.setAttribute('aria-label', next.getAttribute('aria-label'));
                    }
                }

                if (pushState) {
                    window.history.pushState({ tenantTab: resolvedTab }, '', url);
                }

                window.scrollTo({ top: scrollY, behavior: 'instant' in window ? 'instant' : 'auto' });
            } catch {
                window.location.href = url;
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('tenantFormWizard', () => ({
        step: 1,
        maxStep: 4,

        get progress() {
            return (this.step / this.maxStep) * 100;
        },

        goTo(n) {
            this.step = Math.min(this.maxStep, Math.max(1, n));
        },

        next() {
            if (this.step < this.maxStep) {
                this.step += 1;
            }
        },

        prev() {
            if (this.step > 1) {
                this.step -= 1;
            }
        },
    }));

    Alpine.data('tenantControlCenter', (directory, tenantDetails, lifecycleStatuses = {}, quickStatuses = [], reopenTenantId = null) => ({
        directory,
        tenantDetails,
        lifecycleStatuses,
        quickStatuses,
        selectedTenant: directory[0] ?? null,
        drawerOpen: false,
        filterStatus: '',
        statusModalOpen: false,
        statusModalTenant: null,
        statusModalValue: 'active',

        init() {
            if (reopenTenantId === null || reopenTenantId === '') {
                return;
            }
            const tenant = this.directory.find((t) => String(t.id) === String(reopenTenantId));
            if (tenant) {
                this.openDrawer(tenant);
            }
        },

        get filteredDirectory() {
            return this.directory.filter((t) => {
                if (this.filterStatus && t.status !== this.filterStatus) {
                    return false;
                }

                return true;
            });
        },

        get detail() {
            if (!this.selectedTenant) {
                return null;
            }

            return this.tenantDetails[this.selectedTenant.id] ?? null;
        },

        openDrawer(tenant) {
            this.selectedTenant = tenant;
            this.drawerOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeDrawer() {
            this.drawerOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        openStatusModal(tenant) {
            this.statusModalTenant = tenant;
            this.statusModalValue = tenant.status ?? 'active';
            this.statusModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeStatusModal() {
            this.statusModalOpen = false;
            this.statusModalTenant = null;
            if (!this.drawerOpen) {
                document.body.classList.remove('overflow-hidden');
            }
        },
    }));

    Alpine.data('apiCredentialsCenter', (apiKeys, webhooks, tokenDetail, developer) => ({
        apiKeys,
        webhooks,
        tokenDetail,
        developer,
        selectedKey: apiKeys[0] ?? null,
        filterStatus: '',
        activeTab: 'keys',
        showToken: false,
        copied: false,
        snippetLang: 'curl',
        snippetCopied: false,

        get filteredKeys() {
            return this.apiKeys.filter((k) => {
                if (this.filterStatus && k.status !== this.filterStatus) {
                    return false;
                }

                return true;
            });
        },

        get snippetText() {
            return this.developer.snippets[this.snippetLang] ?? '';
        },

        selectKey(key) {
            this.selectedKey = key;
            this.showToken = false;
            this.copied = false;
        },

        async copyToken(key) {
            try {
                await navigator.clipboard.writeText(key.full_token);
                this.copied = true;
                setTimeout(() => {
                    this.copied = false;
                }, 2000);
            } catch (e) {
                /* clipboard unavailable */
            }
        },

        async copySnippet() {
            try {
                await navigator.clipboard.writeText(this.snippetText);
                this.snippetCopied = true;
                setTimeout(() => {
                    this.snippetCopied = false;
                }, 2000);
            } catch (e) {
                /* clipboard unavailable */
            }
        },
    }));

    Alpine.data('supportCenter', (tickets, incidents, conversations) => ({
        tickets,
        incidents,
        conversations,
        selectedTicket: tickets[0] ?? null,
        filterStatus: '',
        filterPriority: '',
        convTab: 'thread',

        get filteredTickets() {
            return this.tickets.filter((t) => {
                if (this.filterStatus && t.status !== this.filterStatus) {
                    return false;
                }
                if (this.filterPriority && t.priority !== this.filterPriority) {
                    return false;
                }

                return true;
            });
        },

        get activeMessages() {
            if (!this.selectedTicket) {
                return [];
            }

            const all = this.conversations[this.selectedTicket.id] ?? [];
            if (this.convTab === 'internal') {
                return all.filter((m) => m.type === 'internal' || m.type === 'system');
            }
            if (this.convTab === 'audit') {
                return all.filter((m) => m.type === 'system');
            }

            return all.filter((m) => m.type === 'customer' || m.type === 'agent');
        },

        selectTicket(ticket) {
            this.selectedTicket = ticket;
        },
    }));

    Alpine.data('accessGovernance', (detailMap, tenantList) => ({
        detailMap,
        tenantList,
        selectedId: null,
        showPolicyModal: false,

        get selected() {
            if (!this.selectedId) {
                return null;
            }

            return this.detailMap[this.selectedId] ?? null;
        },

        selectPolicy(tenantId) {
            this.selectedId = tenantId;
        },
    }));

    Alpine.data('observabilityCenter', (alerts, policies) => ({
        alerts,
        policies,
        alertFilter: 'all',
        refreshing: false,
        lastRefresh: new Date().toLocaleTimeString(),

        get filteredAlerts() {
            if (this.alertFilter === 'all') {
                return this.alerts;
            }

            return this.alerts.filter((a) => a.severity === this.alertFilter);
        },

        refresh() {
            this.refreshing = true;
            setTimeout(() => {
                this.refreshing = false;
                this.lastRefresh = new Date().toLocaleTimeString();
            }, 800);
        },
    }));

    Alpine.data('devopsReleaseCenter', (detailMap) => ({
        detailMap,
        selectedId: null,
        showDeployModal: false,

        get selected() {
            if (!this.selectedId) {
                return null;
            }

            return this.detailMap[this.selectedId] ?? null;
        },

        select(id) {
            this.selectedId = id;
        },
    }));

    Alpine.data('serverForm', (initialForm = {}, initialEnvironment = 'production', isEdit = false, hasStoredToken = false) => ({
        isEdit,
        hasStoredToken,
        provider: initialForm.provider || 'Hostinger',
        environment: initialEnvironment,
        activeTab: 'overview',
        testingConnection: false,
        connectionStatus: null,
        probeMessages: [],

        form: {
            name: '',
            hostname: '',
            provider: 'Hostinger',
            ip_address: '',
            cpu_cores: '',
            ram_gb: '',
            storage_gb: '',
            disk_usage_percent: '',
            status: 'unknown',
            telemetry_mode: 'whm',
            ssl_status: '',
            backup_status: '',
            monthly_cost: '',
            currency: 'KES',
            renewal_expires_at: '',
            ...initialForm,
        },

        init() {
            this.provider = this.form.provider || this.provider;
        },

        selectProvider(name) {
            this.provider = name;
            this.form.provider = name;
        },

        summaryValue(value) {
            const v = value === null || value === undefined ? '' : String(value).trim();
            return v !== '' ? v : 'Not configured';
        },

        telemetryLabel(mode) {
            const labels = {
                manual: 'Manual monitoring',
                basic: 'Basic checks',
                whm: 'WHM live metrics',
            };
            return labels[mode] || this.summaryValue(mode);
        },

        fieldValue(name) {
            const el = document.querySelector(`[name="${name}"]`);
            return el ? String(el.value || '').trim() : '';
        },

        hasWhmToken() {
            const token = this.fieldValue('meta[api_token]');
            if (token !== '' && token !== '********') {
                return true;
            }

            return this.hasStoredToken;
        },

        async testConnection() {
            this.testingConnection = true;
            this.connectionStatus = null;
            this.probeMessages = [];

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const body = new FormData();
                body.append('ip_address', this.form.ip_address || '');
                body.append('provider', this.form.provider || this.provider || '');
                body.append('name', this.form.name || '');
                body.append('whm_cpanel_reference', this.fieldValue('whm_cpanel_reference'));
                body.append('meta[hostname]', this.form.hostname || '');
                body.append('meta[api_token]', this.fieldValue('meta[api_token]'));
                body.append('meta[api_endpoint]', this.fieldValue('meta[api_endpoint]'));
                body.append('meta[cloud_instance_id]', this.fieldValue('meta[cloud_instance_id]'));

                const response = await fetch('/servers/probe', {
                    method: 'POST',
                    headers: token ? { 'X-CSRF-TOKEN': token, Accept: 'application/json' } : { Accept: 'application/json' },
                    body,
                });

                const data = await response.json();
                this.connectionStatus = data.ok ? 'ok' : 'fail';
                this.probeMessages = data.messages || [];

                if (data.status) {
                    this.form.status = data.status;
                }
                if (data.ssl_status) {
                    this.form.ssl_status = data.ssl_status;
                }
                if (data.backup_status) {
                    this.form.backup_status = data.backup_status;
                }
                if (data.disk_percent != null) {
                    this.form.disk_usage_percent = data.disk_percent;
                }
            } catch (e) {
                this.connectionStatus = 'fail';
                this.probeMessages = ['Probe request failed.'];
            }

            this.testingConnection = false;
        },

        get readinessChecklist() {
            const whm = this.form.telemetry_mode === 'whm';
            const items = [
                { label: 'Public IP provided', done: !!this.form.ip_address?.trim() },
                { label: 'Hostname provided', done: !!this.form.hostname?.trim() },
                { label: 'Telemetry mode selected', done: !!this.form.telemetry_mode },
            ];

            if (whm) {
                items.push(
                    { label: 'WHM endpoint provided', done: !!this.fieldValue('meta[api_endpoint]') },
                    { label: 'API token provided', done: this.hasWhmToken() },
                );
            }

            items.push(
                { label: 'Renewal date provided', done: !!this.form.renewal_expires_at?.trim() },
                { label: 'Monthly cost provided', done: !!String(this.form.monthly_cost ?? '').trim() },
            );

            return items;
        },
    }));

    Alpine.data('infraObservability', (detailMap, fleetList) => ({
        detailMap,
        fleetList,
        selectedId: fleetList[0]?.id ?? null,
        refreshing: false,
        lastRefresh: new Date().toLocaleTimeString(),

        get selected() {
            if (!this.selectedId) {
                return null;
            }

            return this.detailMap[this.selectedId] ?? null;
        },

        selectServer(id) {
            this.selectedId = id;
        },

        closePanel() {
            this.selectedId = null;
        },

        simulateRefresh() {
            this.refreshing = true;
            setTimeout(() => {
                this.refreshing = false;
                this.lastRefresh = new Date().toLocaleTimeString();
            }, 800);
        },
    }));

    Alpine.data('authShell', () => ({
        theme: typeof localStorage !== 'undefined' ? localStorage.getItem('prady-theme') || 'light' : 'light',
        applyTheme() {
            const root = document.documentElement;
            const dark =
                this.theme === 'dark' ||
                (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }
        },
        init() {
            this.applyTheme();
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.theme === 'system') {
                    this.applyTheme();
                }
            });
        },
        setTheme(mode) {
            this.theme = mode;
            localStorage.setItem('prady-theme', mode);
            this.applyTheme();
        },
        cycleTheme() {
            const order = ['light', 'dark', 'system'];
            const i = order.indexOf(this.theme);
            this.setTheme(order[(i + 1) % order.length]);
        },
    }));

    Alpine.data('liveMetric', (base, variance = 0, suffix = '', decimals = 0) => ({
        display: '',
        init() {
            const format = (n) => {
                const v = decimals > 0 ? n.toFixed(decimals) : Math.round(n).toLocaleString();
                return v + suffix;
            };
            const b = Number(base) || 0;
            const v = Number(variance) || 0;
            this.display = format(b);
            if (v <= 0) {
                return;
            }
            setInterval(() => {
                const delta = (Math.random() - 0.5) * 2 * v;
                const next = Math.max(0, b + delta);
                this.display = format(next);
            }, 3200 + Math.random() * 2000);
        },
    }));
});

Alpine.start();
