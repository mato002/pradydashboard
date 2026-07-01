<style>
    .manual-doc-preview-panel {
        --preview-frame-bg: #f1f5f9;
    }

    .manual-doc-preview-viewport {
        background: var(--preview-frame-bg);
    }

    .manual-doc-preview-scaler {
        width: 100%;
        max-width: 100%;
    }

    .manual-doc-preview-frame {
        margin: 0 auto;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        border-radius: 0.5rem;
        border: 1px solid #cbd5e1;
        background: #fff;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    }

    .manual-doc-preview-frame.is-a5 {
        max-width: 148mm;
    }

    .manual-doc-preview-frame.is-a4 {
        max-width: 210mm;
    }

    .manual-doc-preview-iframe {
        display: block;
        width: 100%;
        min-height: 210mm;
        border: 0;
        background: #fff;
    }

    .manual-doc-preview-frame.is-a5 .manual-doc-preview-iframe {
        aspect-ratio: 148 / 210;
        min-height: 0;
    }

    .manual-doc-preview-frame.is-a4 .manual-doc-preview-iframe {
        aspect-ratio: 210 / 297;
        min-height: 0;
    }
</style>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/invoices/partials/manual-document-preview-styles.blade.php ENDPATH**/ ?>