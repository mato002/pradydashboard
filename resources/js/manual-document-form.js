function blankLineItem() {
    return {
        _key: 'line-' + Date.now() + '-' + Math.random().toString(36).slice(2),
        description: '',
        quantity: 1,
        unit_price: 0,
        discount: 0,
        tax_rate: 0,
        item_type: 'custom',
    };
}

function mapLineItems(items) {
    if (!Array.isArray(items) || items.length === 0) {
        return [blankLineItem()];
    }

    return items.map((line, index) => ({
        _key: 'line-' + index + '-' + Date.now() + '-' + Math.random().toString(36).slice(2),
        description: line.description || '',
        quantity: parseFloat(line.quantity) || 1,
        unit_price: parseFloat(line.unit_price) || 0,
        discount: parseFloat(line.discount) || 0,
        tax_rate: parseFloat(line.tax_rate) || 0,
        item_type: line.item_type || 'custom',
    }));
}

export function registerManualDocumentForm(Alpine) {
    Alpine.data('manualDocumentForm', (config = {}) => {
        const profileUrlBase = (config.tenantProfileBase || '').replace(/\/$/, '');
        const i18n = config.i18n || {};

        return {
            documentType: config.documentType,
            currency: config.currency,
            previewCompany: config.previewCompany || {},
            paymentOptions: config.paymentOptions || {},
            numberPrefix: config.numberPrefix || 'INV',
            tenantId: String(config.oldTenantId || ''),
            subscriptionId: String(config.oldSubscriptionId || ''),
            clientName: config.clientName || '',
            clientEmail: config.clientEmail || '',
            clientPhone: config.clientPhone || '',
            clientAddress: config.clientAddress || '',
            issueDate: config.issueDate || '',
            dueDate: config.dueDate || '',
            paymentDate: config.paymentDate || '',
            notes: config.notes || '',
            linkedInvoiceId: config.linkedInvoiceId || '',
            receiptAmount: parseFloat(config.receiptAmount) || 0,
            receiptLineDesc: config.receiptLineDesc || '',
            amountPaid: parseFloat(config.amountPaid) || 0,
            previewOpen: false,
            previewZoom: 1,
            previewHtml: config.initialPreviewHtml || '',
            previewPaperSize: (config.initialPreviewPaperSize || 'A5').toUpperCase(),
            previewRefreshTimer: null,
            previewUrl: config.previewUrl || '',
            openInvoices: config.openInvoices || [],
            typeLabels: config.typeLabels || {},
            tenantProfile: null,
            profileLoading: false,
            subscriptions: [],
            lines: mapLineItems(config.lineItems),
            documentTypeBadge() {
                return this.typeLabels[this.documentType] || this.documentType;
            },
            documentPreviewTitle() {
                const titles = {
                    invoice: 'INVOICE',
                    proforma: 'PROFORMA INVOICE',
                    quotation: 'QUOTATION',
                    receipt: 'RECEIPT',
                };

                return titles[this.documentType] || 'DOCUMENT';
            },
            previewDocNumber() {
                return this.numberPrefix + '-DRAFT-0000';
            },
            previewIssueDate() {
                return this.issueDate || '—';
            },
            previewDueDate() {
                return this.dueDate || '';
            },
            previewPaymentDate() {
                return this.paymentDate || this.issueDate || '';
            },
            previewClientName() {
                if (this.tenantId && this.tenantProfile?.company_name) {
                    return this.tenantProfile.company_name;
                }

                const name = (this.clientName || '').trim();

                return name || i18n.clientName || 'Client name';
            },
            previewClientAttention() {
                if (this.tenantId && this.tenantProfile?.billing_contact_name) {
                    const contact = this.tenantProfile.billing_contact_name;
                    if (contact && contact !== this.previewClientName()) {
                        return contact;
                    }
                }

                return '';
            },
            previewClientEmail() {
                if (this.tenantId) {
                    return this.tenantProfile?.billing_email || '';
                }

                return (this.clientEmail || '').trim();
            },
            previewClientPhone() {
                if (this.tenantId) {
                    return this.tenantProfile?.billing_phone || '';
                }

                return (this.clientPhone || '').trim();
            },
            previewClientAddress() {
                if (this.tenantId) {
                    return this.tenantProfile?.billing_address || '';
                }

                return (this.clientAddress || '').trim();
            },
            receiptLineDescription() {
                return (this.receiptLineDesc || '').trim() || i18n.paymentReceived || 'Payment received';
            },
            formatQty(qty) {
                const value = parseFloat(qty) || 0;

                return value.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 4 });
            },
            formatAmount(amount) {
                const value = parseFloat(amount) || 0;

                return value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            lineSubtotal(line) {
                return Math.max(0, line.quantity * line.unit_price - line.discount);
            },
            lineTax(line) {
                return Math.round(this.lineSubtotal(line) * (line.tax_rate / 100) * 100) / 100;
            },
            lineTotal(line) {
                return Math.round((this.lineSubtotal(line) + this.lineTax(line)) * 100) / 100;
            },
            totals() {
                let subtotal = 0;
                let discount = 0;
                let tax = 0;

                this.lines.forEach((line) => {
                    const lineSubtotal = this.lineSubtotal(line);
                    subtotal += lineSubtotal;
                    discount += Math.max(0, parseFloat(line.discount) || 0);
                    tax += this.lineTax(line);
                });

                subtotal = Math.round(subtotal * 100) / 100;
                tax = Math.round(tax * 100) / 100;

                return {
                    subtotal,
                    discount: Math.round(discount * 100) / 100,
                    tax,
                    total: Math.round((subtotal + tax) * 100) / 100,
                };
            },
            formatMoney(amount) {
                const value = parseFloat(amount) || 0;

                return (this.currency || 'KES') + ' ' + value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            filteredOpenInvoices() {
                if (!this.tenantId) {
                    return [];
                }

                return this.openInvoices.filter((invoice) => String(invoice.tenant_id) === String(this.tenantId));
            },
            clearLinkedInvoiceIfInvalid() {
                if (!this.linkedInvoiceId) {
                    return;
                }

                const valid = this.filteredOpenInvoices().some((invoice) => String(invoice.id) === String(this.linkedInvoiceId));
                if (!valid) {
                    this.linkedInvoiceId = '';
                }
            },
            ensureDefaultLine() {
                if (this.lines.length === 0) {
                    this.lines.push(blankLineItem());
                }
            },
            previewPayload() {
                return {
                    document_type: this.documentType,
                    tenant_id: this.tenantId || null,
                    tenant_project_subscription_id: this.subscriptionId || null,
                    manual_client_name: this.clientName,
                    manual_client_email: this.clientEmail,
                    manual_client_phone: this.clientPhone,
                    manual_client_address: this.clientAddress,
                    issue_date: this.issueDate,
                    due_date: this.dueDate,
                    currency: this.currency,
                    notes: this.notes,
                    amount_paid: this.amountPaid,
                    linked_invoice_id: this.linkedInvoiceId || null,
                    amount_received: this.receiptAmount,
                    payment_date: this.paymentDate,
                    line_description: this.receiptLineDesc,
                    line_items: this.lines.map((line) => ({
                        description: line.description,
                        quantity: line.quantity,
                        unit_price: line.unit_price,
                        discount: line.discount,
                        tax_rate: line.tax_rate,
                        item_type: line.item_type,
                    })),
                };
            },
            schedulePreviewRefresh() {
                clearTimeout(this.previewRefreshTimer);
                this.previewRefreshTimer = setTimeout(() => this.refreshPreview(), 350);
            },
            async refreshPreview() {
                if (!this.previewUrl) {
                    return;
                }

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                try {
                    const response = await fetch(this.previewUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': token || '',
                        },
                        body: JSON.stringify(this.previewPayload()),
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    this.previewHtml = data.html || '';
                    this.previewPaperSize = (data.paper_size || 'A5').toUpperCase();
                } catch {
                    // Preview is best-effort while the form is edited.
                }
            },
            addLine() {
                this.lines.push(blankLineItem());
            },
            duplicateLine(index) {
                const source = this.lines[index];
                this.lines.splice(index + 1, 0, {
                    _key: 'line-' + Date.now() + '-' + Math.random().toString(36).slice(2),
                    description: source.description,
                    quantity: source.quantity,
                    unit_price: source.unit_price,
                    discount: source.discount,
                    tax_rate: source.tax_rate,
                    item_type: source.item_type,
                });
            },
            removeLine(index) {
                if (this.lines.length > 1) {
                    this.lines.splice(index, 1);
                }
            },
            onTenantChange() {
                this.linkedInvoiceId = '';
                this.loadTenant();
            },
            async loadTenant() {
                if (!this.tenantId) {
                    this.tenantProfile = null;
                    this.subscriptions = [];
                    this.subscriptionId = '';
                    this.clearLinkedInvoiceIfInvalid();

                    return;
                }

                this.profileLoading = true;

                try {
                    const response = await fetch(profileUrlBase + this.tenantId + '/billing-profile');
                    if (!response.ok) {
                        throw new Error('profile');
                    }

                    const data = await response.json();
                    this.tenantProfile = data;
                    this.clientEmail = data.billing_email || '';
                    this.clientPhone = data.billing_phone || '';
                    this.clientAddress = data.billing_address || '';
                    this.clientName = data.company_name || '';
                    this.subscriptions = data.subscriptions || [];

                    if (data.currency) {
                        this.currency = data.currency;
                    }

                    if (this.subscriptionId && !this.subscriptions.some((subscription) => String(subscription.id) === String(this.subscriptionId))) {
                        this.subscriptionId = '';
                    }
                } catch {
                    this.tenantProfile = null;
                } finally {
                    this.profileLoading = false;
                    this.clearLinkedInvoiceIfInvalid();
                }
            },
            init() {
                this.ensureDefaultLine();

                if (this.tenantId) {
                    this.loadTenant();
                } else {
                    this.clearLinkedInvoiceIfInvalid();
                }

                this.$watch(() => JSON.stringify(this.previewPayload()), () => this.schedulePreviewRefresh());
                this.refreshPreview();
            },
        };
    });
}
