<?php
    $s = $snapshot;
    $issuer = $issuer ?? ($s['issuer'] ?? []);
    $pay = $s['payment_options'] ?? [];
    $lineItems = $s['line_items'] ?? [];
    $statementRows = $s['statement_rows'] ?? ($s['statement']['rows'] ?? []);
    $docType = $s['document_type'] ?? ($s['document']['type'] ?? 'invoice');
    $docLabel = $s['document']['title'] ?? \App\Support\Billing\FinancialDocumentRegistry::title($docType);
    $displayNumber = $s['display_number'] ?? ($s['document']['display_number'] ?? \App\Support\Billing\DocumentNumberFormatter::display($s['invoice_number'] ?? ''));
    $clientName = $s['client']['name'] ?? ($s['tenant']['company_name'] ?? '—');
    $clientPrefix = $s['client']['salutation'] ?? 'Ms.';
    $currency = $s['currency'] ?? 'KES';
    $linkedInvoice = $s['conversion_links']['linked_invoice_number'] ?? null;
    $sections = $s['presentation']['enabled_sections'] ?? [];
    $isStatement = $docType === 'statement';
    $isQuotation = $docType === 'quotation';
    $isReceipt = $docType === 'receipt';
    $showPaidBalance = $sections['paid_balance'] ?? \App\Support\Billing\FinancialDocumentRegistry::showsPaidBalance($docType);
    $showPaymentOptions = $sections['payment_options'] ?? \App\Support\Billing\FinancialDocumentRegistry::showsPaymentOptions($docType);
    $showDueDate = in_array($docType, ['invoice', 'proforma'], true);
    $showValidity = $isQuotation;
    $lineItemCount = count($lineItems);
    if ($isStatement) {
        $minLedgerRows = $lineItemCount;
    } else {
        $minLedgerRows = max(5, $lineItemCount);
    }
    $fmtAmount = fn (float $n): string => number_format($n, 0).'/=';
    $fmtKsh = fn (float $n): string => 'Ksh '.number_format($n, 0);
    $subtotal = (float) ($s['totals']['subtotal'] ?? $s['subtotal'] ?? 0);
    $tax = (float) ($s['totals']['tax'] ?? $s['tax_amount'] ?? 0);
    $discount = (float) ($s['totals']['discount'] ?? $s['discount_amount'] ?? 0);
    $total = (float) ($s['totals']['total'] ?? $s['total'] ?? 0);
    $paid = (float) ($s['totals']['paid'] ?? $s['amount_paid'] ?? 0);
    $balance = (float) ($s['totals']['balance'] ?? $s['balance_due'] ?? 0);
    $tradingName = $issuer['trading_name'] ?? 'Prady Technologies';
    $legalName = $issuer['legal_name'] ?? $tradingName;
    $phone = trim((string) ($issuer['phone'] ?? ''));
    $email = trim((string) ($issuer['email'] ?? ''));
    $website = trim((string) ($issuer['website'] ?? ''));
    $address = trim((string) ($issuer['address'] ?? ''));
    $pin = trim((string) ($issuer['pin'] ?? ''));
    $tagline = trim((string) ($issuer['tagline'] ?? 'DOING I.T DIFFERENTLY'));
    $logoUrl = $issuer['logo_url'] ?? null;
    $issueDate = $s['issue_date'] ?? ($s['document']['issue_date'] ?? '—');
    $dueDate = $s['due_date'] ?? ($s['document']['due_date'] ?? null);
    $issueDateDisplay = $issueDate && $issueDate !== '—'
        ? \Illuminate\Support\Carbon::parse($issueDate)->format('d/m/Y')
        : '—';
?>
<div class="doc-a5">
    <table class="pc-doc-shell" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="pc-doc-body" valign="top">
                <?php echo $__env->make('billing.documents.prady-classic.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('billing.documents.prady-classic.partials.document-meta', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php if($isStatement && $statementRows !== []): ?>
                    <?php echo $__env->make('billing.documents.prady-classic.partials.statement-table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php else: ?>
                    <?php echo $__env->make('billing.documents.prady-classic.partials.ledger-body', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>

                <?php if($isQuotation): ?>
                    <?php echo $__env->make('billing.documents.prady-classic.partials.quotation-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="pc-doc-footer-slot" valign="bottom">
                <?php echo $__env->make('billing.documents.prady-classic.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </td>
        </tr>
    </table>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/billing/documents/prady-classic/layout-a5.blade.php ENDPATH**/ ?>