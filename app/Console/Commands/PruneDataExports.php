<?php

namespace App\Console\Commands;

use App\Models\DataExport;
use App\Scopes\TenantScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneDataExports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired data export files and records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expired = DataExport::withoutGlobalScope(TenantScope::class)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $deleted = 0;

        foreach ($expired as $export) {
            if ($export->file_path) {
                Storage::disk($export->disk)->delete($export->file_path);
            }

            $export->delete();
            $deleted++;
        }

        $this->info("Pruned {$deleted} expired data export(s).");

        return self::SUCCESS;
    }
}
