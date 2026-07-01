<?php

use App\Domain\Billing\DocumentRenderer;
use App\Domain\Billing\PdfGenerator;
use App\Domain\Billing\SampleFinancialDocumentSnapshot;
use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$renderer = app(DocumentRenderer::class);
$pdf = app(PdfGenerator::class);
$dir = 'prady-classic-samples';
Storage::disk('local')->makeDirectory($dir);

$map = [
    'invoice' => fn () => SampleFinancialDocumentSnapshot::invoice(),
    'receipt' => fn () => SampleFinancialDocumentSnapshot::receipt(),
    'proforma' => fn () => SampleFinancialDocumentSnapshot::proforma(),
    'quotation' => fn () => SampleFinancialDocumentSnapshot::quotation(),
    'statement' => fn () => SampleFinancialDocumentSnapshot::statement(),
];

foreach ($map as $type => $snapshotFactory) {
    $template = DocumentTemplate::query()
        ->where('style', 'prady_classic_a5')
        ->where('type', $type)
        ->first();

    if (! $template) {
        fwrite(STDERR, "Missing template: {$type}\n");
        continue;
    }

    $html = $renderer->render($template, $snapshotFactory());
    $path = "{$dir}/prady-classic-{$type}.pdf";
    $pdf->store($html, $path, 'A5', 'portrait');
    echo "Generated: storage/app/{$path}\n";
}
