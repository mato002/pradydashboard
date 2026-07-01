<?php

namespace App\Domain\Billing;

use Illuminate\Support\Facades\Storage;

class PdfGenerator
{
    public function isAvailable(): bool
    {
        return class_exists(\Dompdf\Dompdf::class);
    }

    public function store(string $html, string $relativePath, string $paper = 'A4', string $orientation = 'portrait'): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        Storage::disk('local')->put($relativePath, $dompdf->output());

        return $relativePath;
    }

    public function pageCount(string $html, string $paper = 'A4', string $orientation = 'portrait'): int
    {
        if (! $this->isAvailable()) {
            return 0;
        }

        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        return (int) $dompdf->getCanvas()->get_page_count();
    }
}
