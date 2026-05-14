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
});

Alpine.start();
