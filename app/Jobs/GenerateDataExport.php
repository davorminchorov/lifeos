<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exports\ExportContext;
use App\Exports\ExportRegistry;
use App\Models\DataExport;
use App\Notifications\DataExportReady;
use App\Scopes\TenantScope;
use App\Services\CsvWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class GenerateDataExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    /**
     * Number of days a finished export remains available for download.
     */
    private const RETENTION_DAYS = 7;

    public function __construct(public int $exportId) {}

    public function handle(ExportRegistry $registry, CsvWriter $writer): void
    {
        $export = DataExport::withoutGlobalScope(TenantScope::class)->find($this->exportId);

        if (! $export) {
            return;
        }

        $export->update(['status' => DataExport::STATUS_PROCESSING]);

        $context = new ExportContext($export->tenant_id, $export->user_id, $export->filters ?? []);
        $disk = Storage::disk($export->disk);
        $directory = 'exports/'.$export->tenant_id;
        $disk->makeDirectory($directory);

        /** @var array<int, array{path: string, entry: string}> $tempFiles */
        $tempFiles = [];
        $rowCounts = [];

        try {
            foreach ($export->modules as $key) {
                $exporter = $registry->get($key);
                $result = $writer->writeToTempFile($exporter, $context);

                $tempFiles[] = ['path' => $result['path'], 'entry' => $key.'.csv'];
                $rowCounts[$key] = $result['count'];
            }

            [$storedPath, $fileName] = count($tempFiles) === 1
                ? $this->storeSingleCsv($export, $disk, $directory, $tempFiles[0]['path'])
                : $this->storeZip($export, $disk, $directory, $tempFiles);

            $export->update([
                'status' => DataExport::STATUS_COMPLETED,
                'file_path' => $storedPath,
                'file_name' => $fileName,
                'row_counts' => $rowCounts,
                'error' => null,
                'completed_at' => now(),
                'expires_at' => now()->addDays(self::RETENTION_DAYS),
            ]);

            $export->user?->notify(new DataExportReady($export));
        } catch (Throwable $e) {
            $export->update([
                'status' => DataExport::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            foreach ($tempFiles as $file) {
                @unlink($file['path']);
            }
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function storeSingleCsv(DataExport $export, $disk, string $directory, string $tempPath): array
    {
        $fileName = $this->baseName($export).'.csv';
        $storedPath = $directory.'/'.uniqid('export_', true).'.csv';

        $disk->writeStream($storedPath, fopen($tempPath, 'r'));

        return [$storedPath, $fileName];
    }

    /**
     * @param  array<int, array{path: string, entry: string}>  $tempFiles
     * @return array{0: string, 1: string}
     */
    private function storeZip(DataExport $export, $disk, string $directory, array $tempFiles): array
    {
        $fileName = $this->baseName($export).'.zip';
        $storedPath = $directory.'/'.uniqid('export_', true).'.zip';
        $zipTemp = tempnam(sys_get_temp_dir(), 'lifeos_export_zip_');

        $zip = new ZipArchive;
        if ($zip->open($zipTemp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create export archive.');
        }

        foreach ($tempFiles as $file) {
            $zip->addFile($file['path'], $file['entry']);
        }

        $zip->close();

        try {
            $disk->writeStream($storedPath, fopen($zipTemp, 'r'));
        } finally {
            @unlink($zipTemp);
        }

        return [$storedPath, $fileName];
    }

    private function baseName(DataExport $export): string
    {
        $prefix = count($export->modules) === 1 ? $export->modules[0] : 'lifeos-export';

        return $prefix.'-'.now()->format('Y-m-d_His');
    }

    public function failed(Throwable $exception): void
    {
        $export = DataExport::withoutGlobalScope(TenantScope::class)->find($this->exportId);

        $export?->update([
            'status' => DataExport::STATUS_FAILED,
            'error' => $exception->getMessage(),
        ]);

        Log::error('GenerateDataExport failed', [
            'export_id' => $this->exportId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
