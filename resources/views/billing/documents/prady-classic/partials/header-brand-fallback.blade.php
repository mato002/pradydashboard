@php
    $taglineRaw = trim((string) ($issuer['tagline'] ?? 'DOING I.T DIFFERENTLY'));
    $taglineCore = preg_replace('/^[—–-]+\s*|\s*[—–-]+$/u', '', $taglineRaw) ?: 'DOING I.T DIFFERENTLY';
    $taglineDisplay = '— '.strtoupper($taglineCore).' —';
@endphp
<table class="pc-header-black pc-header-fallback" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="pc-header-brand-cell" align="center">
            <div class="pc-brand-title">
                <span class="pc-brand-prady">PRADY</span><span class="pc-brand-tech"> TECHNOLOGIES</span>
            </div>
            <table class="pc-tagline-table" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="pc-tagline-line">&nbsp;</td>
                    <td class="pc-tagline-text">{{ $taglineDisplay }}</td>
                    <td class="pc-tagline-line">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
