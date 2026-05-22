<style>
    .manual-doc-preview-panel {
        --preview-teal: #0f766e;
        contain: inline-size;
    }
    .manual-doc-preview-viewport {
        width: 100%;
        max-width: 100%;
    }
    .manual-doc-preview-scaler {
        transform-origin: top center;
        transition: transform 0.15s ease;
        max-width: 100%;
    }
    .manual-doc-preview-frame {
        position: relative;
        margin: 0 auto;
        width: 100%;
        max-width: 100%;
        background: #fff;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.12);
        border-radius: 4px;
        overflow: hidden;
        box-sizing: border-box;
    }
    .manual-doc-preview-frame.is-a5 {
        aspect-ratio: 148 / 210;
        min-height: 280px;
    }
    .manual-doc-preview-frame.is-thermal {
        max-width: 80mm;
        min-height: 120px;
        font-family: ui-monospace, monospace;
        font-size: 11px;
    }
    .manual-doc-preview-frame.is-generic {
        aspect-ratio: 148 / 200;
        min-height: 260px;
    }
    .manual-doc-preview-panel .preview-a5 {
        width: 100%;
        padding: 2mm 3mm 4mm;
        font-size: clamp(7px, 1.8vw, 9.5px);
    }
    @media (min-width: 1024px) {
        .manual-doc-preview-panel .preview-a5 {
            font-size: clamp(7px, 0.85vw, 9.5px);
        }
    }
    .preview-draft-watermark::before {
        content: 'DRAFT';
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(2.5rem, 12vw, 4.5rem);
        font-weight: 800;
        letter-spacing: 0.2em;
        color: #0f766e;
        opacity: 0.07;
        pointer-events: none;
        z-index: 2;
        transform: rotate(-28deg);
    }
    .preview-a5 {
        width: 100%;
        box-sizing: border-box;
        padding: 2mm 4mm 6mm;
        font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
        font-size: 9.5px;
        color: #111827;
        line-height: 1.35;
        position: relative;
        z-index: 1;
    }
    .preview-a5 .phdr { display: flex; gap: 10px; align-items: flex-start; border-bottom: 2px solid var(--preview-teal); padding-bottom: 8px; margin-bottom: 8px; }
    .preview-a5 .phdr-mark { width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg,#0d9488,#115e59); color: #fff; font-weight: 800; font-size: 18px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .preview-a5 .phdr-name { font-size: 14px; font-weight: 800; letter-spacing: -0.02em; color: #0f172a; }
    .preview-a5 .phdr-tag { font-size: 8.5px; color: #475569; margin-top: 1px; }
    .preview-a5 .phdr-meta { font-size: 8px; color: #64748b; margin-top: 4px; }
    .preview-a5 .pdoc-type { font-size: 11px; font-weight: 800; letter-spacing: .12em; color: var(--preview-teal); text-align: right; margin: 0 0 6px 0; }
    .preview-a5 .pgrid { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 8px; }
    .preview-a5 .pcol { flex: 1; min-width: 0; }
    .preview-a5 .pcol-num { flex: 0 0 42%; text-align: right; }
    .preview-a5 .plab { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; }
    .preview-a5 .pval { font-size: 10px; }
    .preview-a5 .pstrong { font-weight: 700; color: #0f172a; }
    .preview-a5 .psub { font-size: 8.5px; color: #475569; margin-top: 2px; white-space: pre-line; }
    .preview-a5 .pri-row { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 3px; }
    .preview-a5 .pri-row .plab { min-width: 52px; text-align: right; }
    .preview-a5 .mono { font-family: ui-monospace, monospace; }
    .preview-a5 .muted { color: #94a3b8; }
    .preview-a5 .ptable { width: 100%; border-collapse: collapse; margin: 6px 0 8px; font-size: 8.5px; }
    .preview-a5 .ptable th { background: #f0fdfa; color: #134e4a; text-align: left; padding: 4px 5px; border: 1px solid #ccfbf1; font-size: 7.5px; text-transform: uppercase; letter-spacing: .04em; }
    .preview-a5 .ptable td { padding: 4px 5px; border: 1px solid #e2e8f0; vertical-align: top; }
    .preview-a5 .ptable th.r, .preview-a5 .ptable td.r { text-align: right; }
    .preview-a5 .ptable th.c, .preview-a5 .ptable td.c { text-align: center; width: 22px; }
    .preview-a5 .ptable .empty-row td { color: #94a3b8; font-style: italic; }
    .preview-a5 .ptotals { margin-left: auto; width: 58%; margin-bottom: 8px; }
    .preview-a5 .ptot-row { display: flex; justify-content: space-between; padding: 3px 0; border-bottom: 1px solid #f1f5f9; font-size: 9px; }
    .preview-a5 .ptot-row.pgrand { border: none; margin-top: 4px; padding-top: 6px; border-top: 2px solid var(--preview-teal); font-weight: 800; font-size: 11px; color: #0f172a; }
    .preview-a5 .ppay { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; margin-bottom: 10px; }
    .preview-a5 .ppay-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 12px; margin-top: 6px; }
    .preview-a5 .pk { font-size: 7px; font-weight: 700; text-transform: uppercase; color: #64748b; display: block; }
    .preview-a5 .pv { font-size: 9px; font-weight: 600; }
    .preview-a5 .pfoot { font-size: 8px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 8px; white-space: pre-wrap; }
    .preview-a5 .pinst { margin-top: 6px; text-align: left; }
    .preview-a5 .pnotes { font-size: 8.5px; color: #475569; margin-top: 8px; padding: 6px 8px; background: #f8fafc; border-radius: 4px; white-space: pre-wrap; }
    .preview-generic {
        width: 100%;
        box-sizing: border-box;
        padding: 8mm 5mm;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 10px;
        color: #1e293b;
        position: relative;
        z-index: 1;
    }
    .preview-generic .g-hdr { border-bottom: 2px solid #334155; padding-bottom: 8px; margin-bottom: 10px; }
    .preview-generic .g-title { font-size: 13px; font-weight: 700; letter-spacing: 0.08em; text-align: right; color: #334155; }
    .preview-generic .g-table { width: 100%; border-collapse: collapse; margin: 8px 0; }
    .preview-generic .g-table th, .preview-generic .g-table td { border: 1px solid #cbd5e1; padding: 4px 6px; }
    .preview-generic .g-table th { background: #f1f5f9; font-size: 8px; text-transform: uppercase; }
    .preview-thermal { padding: 6mm 4mm; position: relative; z-index: 1; text-align: center; }
    .preview-thermal .t-lines { text-align: left; margin: 8px 0; }
    .preview-thermal .t-line { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 4px; }
    .preview-thermal .t-total { font-weight: 800; font-size: 13px; margin-top: 8px; padding-top: 6px; border-top: 1px dashed #94a3b8; }
</style>
