<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\Contracts\ModuleExporter;
use App\Exports\ExportContext;

class CsvWriter
{
    /**
     * Stream a module's rows into an open file handle and return the row count.
     *
     * Rows are pulled with lazy chunking so large tables never fully
     * materialise in memory.
     *
     * @param  resource  $handle
     */
    public function write($handle, ModuleExporter $exporter, ExportContext $context): int
    {
        fputcsv($handle, $exporter->headers());

        $count = 0;

        foreach ($exporter->query($context)->lazy() as $model) {
            fputcsv($handle, $exporter->mapRow($model));
            $count++;
        }

        return $count;
    }

    /**
     * Write a module's CSV to a fresh temporary file and return its path and
     * row count.
     *
     * @return array{path: string, count: int}
     */
    public function writeToTempFile(ModuleExporter $exporter, ExportContext $context): array
    {
        $path = tempnam(sys_get_temp_dir(), 'lifeos_export_');
        $handle = fopen($path, 'w');

        try {
            $count = $this->write($handle, $exporter, $context);
        } finally {
            fclose($handle);
        }

        return ['path' => $path, 'count' => $count];
    }
}
