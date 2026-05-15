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
    Alpine.data('pradyShell', () => ({
        sidebarOpen: false,
        sidebarCollapsed: false,
        theme: localStorage.getItem('prady-theme') || 'light',
        dateMenuOpen: false,
        notifOpen: false,
        searchOpen: false,
        init() {
            applyThemeClass(this.theme === 'system' ? 'system' : this.theme);
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.theme === 'system') {
                    applyThemeClass('system');
                }
            });
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

    Alpine.data('tenantControlCenter', (directory, tenantDetails) => ({
        directory,
        tenantDetails,
        selectedTenant: directory[0] ?? null,
        drawerOpen: false,
        filterStatus: '',

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

    Alpine.data('infrastructureProvisioning', (fleetStats = {}) => ({
        fleet: fleetStats,
        provider: '',
        environment: 'production',
        testingConnection: false,
        connectionStatus: null,
        hostnameStatus: null,
        draftSaved: false,
        showToken: false,
        activeSection: 'identity',

        form: {
            name: '',
            hostname: '',
            provider: '',
            ip_address: '',
            private_ip: '',
            region: '',
            cpu_cores: '',
            ram_gb: '',
            storage_gb: '',
            disk_usage_percent: '',
            status: 'unknown',
            ssl_status: '',
            backup_status: '',
            monthly_cost: '',
            currency: 'KES',
            monthly_revenue: '',
            renewal_expires_at: '',
        },

        init() {
            const draft = localStorage.getItem('prady-server-provision-draft');
            if (draft) {
                try {
                    const parsed = JSON.parse(draft);
                    Object.assign(this.form, parsed.form ?? {});
                    this.provider = parsed.provider ?? '';
                    this.environment = parsed.environment ?? 'production';
                } catch (e) {
                    /* ignore corrupt draft */
                }
            }
            this.$watch('form', () => this.updateScores(), { deep: true });
            this.$watch('provider', () => this.updateScores());
            this.$watch('environment', () => this.updateScores());
            this.updateScores();
        },

        selectProvider(name) {
            this.provider = name;
            this.form.provider = name;
        },

        verifyHostname() {
            const h = (this.form.hostname || '').trim();
            if (!h) {
                this.hostnameStatus = null;
                return;
            }
            const valid = /^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/i.test(h);
            this.hostnameStatus = valid ? 'valid' : 'invalid';
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
                body.append('whm_cpanel_reference', document.getElementById('whm_cpanel_reference')?.value || '');
                body.append('meta[hostname]', this.form.hostname || '');
                body.append('meta[api_token]', document.querySelector('[name="meta[api_token]"]')?.value || '');
                body.append('meta[api_endpoint]', document.querySelector('[name="meta[api_endpoint]"]')?.value || '');
                body.append('meta[cloud_instance_id]', document.querySelector('[name="meta[cloud_instance_id]"]')?.value || '');

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
                this.probeMessages = [__('Probe request failed.')];
            }

            this.testingConnection = false;
            this.updateScores();
        },

        probeMessages: [],

        saveDraft() {
            localStorage.setItem(
                'prady-server-provision-draft',
                JSON.stringify({
                    form: this.form,
                    provider: this.provider,
                    environment: this.environment,
                }),
            );
            this.draftSaved = true;
            setTimeout(() => {
                this.draftSaved = false;
            }, 2500);
        },

        scores: {
            health: 0,
            readiness: 0,
            security: 0,
            connectivity: 0,
        },

        updateScores() {
            let health = 20;
            let readiness = 15;
            let security = 25;
            let connectivity = 10;

            if (this.form.name?.trim()) {
                health += 15;
                readiness += 12;
            }
            if (this.form.hostname?.trim() && this.hostnameStatus === 'valid') {
                health += 10;
                readiness += 8;
            }
            if (this.provider) {
                readiness += 10;
            }
            if (this.form.cpu_cores) {
                health += 10;
            }
            if (this.form.ram_gb) {
                health += 8;
            }
            if (this.form.ip_address?.trim()) {
                connectivity += 35;
                readiness += 15;
            }
            if (this.form.ssl_status?.trim()) {
                security += 25;
            }
            if (this.form.backup_status?.trim()) {
                security += 20;
                health += 12;
            }
            if (this.form.monthly_cost) {
                readiness += 8;
            }

            this.scores = {
                health: Math.min(100, health),
                readiness: Math.min(100, readiness),
                security: Math.min(100, security),
                connectivity: Math.min(100, connectivity),
            };
        },

        get estimatedCost() {
            const c = parseFloat(this.form.monthly_cost) || 0;
            return c.toFixed(2);
        },

        get checklist() {
            return [
                { label: 'Server identity configured', done: !!this.form.name?.trim() },
                { label: 'Network endpoint defined', done: !!this.form.ip_address?.trim() },
                { label: 'Capacity profile set', done: !!this.form.cpu_cores && !!this.form.ram_gb },
                { label: 'SSL posture documented', done: !!this.form.ssl_status?.trim() },
                { label: 'Backup policy recorded', done: !!this.form.backup_status?.trim() },
                { label: 'Billing baseline captured', done: !!this.form.monthly_cost },
            ];
        },

        get checklistComplete() {
            return this.checklist.filter((c) => c.done).length;
        },

        scrollToSection(id) {
            this.activeSection = id;
            document.getElementById('infra-section-' + id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
