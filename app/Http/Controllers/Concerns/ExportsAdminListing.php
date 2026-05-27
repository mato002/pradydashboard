<?php

namespace App\Http\Controllers\Concerns;

use App\Support\Admin\CsvExporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExportsAdminListing
{
    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<scalar|null>>  $rows
     */
    protected function exportCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return CsvExporter::download($filename, $headers, $rows);
    }
}
