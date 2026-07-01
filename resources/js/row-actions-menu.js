let rowActionsMenuCounter = 0;

export function registerRowActionsMenu(Alpine) {
    if (! Alpine.store('rowActionsMenu')) {
        Alpine.store('rowActionsMenu', {
            activeId: null,

            open(id) {
                this.activeId = id;
            },

            close(id = null) {
                if (id === null || this.activeId === id) {
                    this.activeId = null;
                }
            },

            isActive(id) {
                return this.activeId === id;
            },
        });
    }

    Alpine.data('rowActionsMenu', () => ({
        menuId: null,
        panelStyle: {},

        init() {
            rowActionsMenuCounter += 1;
            this.menuId = `row-actions-${rowActionsMenuCounter}`;

            this._onScroll = () => {
                if (this.isOpen()) {
                    this.close();
                }
            };

            window.addEventListener('scroll', this._onScroll, true);
        },

        destroy() {
            window.removeEventListener('scroll', this._onScroll, true);
            this.close();
        },

        isOpen() {
            return Alpine.store('rowActionsMenu').isActive(this.menuId);
        },

        toggle() {
            if (this.isOpen()) {
                this.close();

                return;
            }

            Alpine.store('rowActionsMenu').open(this.menuId);
            this.positionPanel();
        },

        close() {
            Alpine.store('rowActionsMenu').close(this.menuId);
        },

        positionPanel() {
            const trigger = this.$refs.trigger;

            if (! trigger) {
                return;
            }

            const rect = trigger.getBoundingClientRect();
            const panelWidth = 176;
            let left = rect.right - panelWidth;
            left = Math.max(8, Math.min(left, window.innerWidth - panelWidth - 8));

            this.panelStyle = {
                position: 'fixed',
                top: `${rect.bottom + 4}px`,
                left: `${left}px`,
                minWidth: '11rem',
                zIndex: 9999,
            };
        },
    }));
}
