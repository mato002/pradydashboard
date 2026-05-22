<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class PradyClassicA5TemplateSeeder extends Seeder
{
    /**
     * Prady Classic A5 — default proforma layout; optional A5 invoice variant.
     */
    public function run(): void
    {
        $css = <<<'CSS'
@page { size: A5 portrait; margin: 8mm; }
body { margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.preview-a5 {
  width: 148mm;
  min-height: 210mm;
  margin: 0 auto;
  box-sizing: border-box;
  padding: 2mm 0;
  font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
  font-size: 9.5px;
  color: #111827;
  line-height: 1.35;
}
.phdr { display: flex; gap: 10px; align-items: flex-start; border-bottom: 2px solid #0f766e; padding-bottom: 8px; margin-bottom: 8px; }
.phdr-mark { width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg,#0d9488,#115e59); color: #fff; font-weight: 800; font-size: 18px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.phdr-logo img { max-height: 36px; max-width: 120px; object-fit: contain; }
.phdr-name { font-size: 14px; font-weight: 800; letter-spacing: -0.02em; color: #0f172a; }
.phdr-tag { font-size: 8.5px; color: #475569; margin-top: 1px; }
.phdr-meta { font-size: 8px; color: #64748b; margin-top: 4px; }
.pdoc-type { font-size: 11px; font-weight: 800; letter-spacing: .12em; color: #0f766e; text-align: right; margin: 0 0 6px 0; }
.pgrid { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 8px; }
.pcol { flex: 1; min-width: 0; }
.pcol-num { flex: 0 0 42%; text-align: right; }
.plab { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; }
.pval { font-size: 10px; }
.pstrong { font-weight: 700; color: #0f172a; }
.psub { font-size: 8.5px; color: #475569; margin-top: 2px; }
.pri-row { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 3px; }
.pri-row .plab { min-width: 52px; text-align: right; }
.mono { font-family: ui-monospace, monospace; }
.muted { color: #94a3b8; }
.ptable { width: 100%; border-collapse: collapse; margin: 6px 0 8px; font-size: 8.5px; }
.ptable th { background: #f0fdfa; color: #134e4a; text-align: left; padding: 4px 5px; border: 1px solid #ccfbf1; font-size: 7.5px; text-transform: uppercase; letter-spacing: .04em; }
.ptable td { padding: 4px 5px; border: 1px solid #e2e8f0; vertical-align: top; }
.ptable th.r, .ptable td.r { text-align: right; }
.ptable th.c, .ptable td.c { text-align: center; width: 22px; }
.ptotals { margin-left: auto; width: 58%; margin-bottom: 8px; }
.ptot-row { display: flex; justify-content: space-between; padding: 3px 0; border-bottom: 1px solid #f1f5f9; font-size: 9px; }
.ptot-row.pgrand { border: none; margin-top: 4px; padding-top: 6px; border-top: 2px solid #0f766e; font-weight: 800; font-size: 11px; color: #0f172a; }
.ppay { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; margin-bottom: 10px; }
.ppay-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 12px; margin-top: 6px; }
.ppk { font-size: 7px; font-weight: 700; text-transform: uppercase; color: #64748b; display: block; }
.pk { font-size: 7px; font-weight: 700; text-transform: uppercase; color: #64748b; display: block; }
.pv { font-size: 9px; font-weight: 600; }
.pfoot { font-size: 8px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 8px; }
.pinst { margin-top: 6px; text-align: left; white-space: pre-wrap; }
CSS;

        $branding = [
            'display_name' => 'Prady Technologies',
            'tagline' => __('Innovation through technology'),
            'primary_color' => '#0f766e',
            'accent_color' => '#0f172a',
            'footer_text' => __('Thank you for choosing Prady Technologies.'),
            'logo_url' => null,
        ];

        DocumentTemplate::query()->where('type', 'proforma')->update(['is_default' => false]);

        DocumentTemplate::query()->updateOrCreate(
            [
                'type' => 'proforma',
                'style' => 'prady_classic_a5',
            ],
            [
                'name' => 'Prady Classic A5 Proforma',
                'blade_view' => 'billing.documents.prady-classic-a5',
                'paper_size' => 'A5',
                'orientation' => 'portrait',
                'active' => true,
                'is_default' => true,
                'css' => $css,
                'branding' => $branding,
            ],
        );

        DocumentTemplate::query()->updateOrCreate(
            [
                'type' => 'invoice',
                'style' => 'prady_classic_a5',
            ],
            [
                'name' => 'Prady Classic A5 Invoice',
                'blade_view' => 'billing.documents.prady-classic-a5',
                'paper_size' => 'A5',
                'orientation' => 'portrait',
                'active' => true,
                'is_default' => false,
                'css' => $css,
                'branding' => $branding,
            ],
        );
    }
}