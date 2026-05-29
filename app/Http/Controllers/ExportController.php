<?php

namespace App\Http\Controllers;

use App\Exports\ExportRegistry;
use App\Http\Requests\StoreDataExportRequest;
use App\Jobs\GenerateDataExport;
use App\Models\DataExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(protected ExportRegistry $registry) {}

    /**
     * Show the data export page with module options and recent exports.
     */
    public function index(): Response
    {
        return Inertia::render('Settings/DataExport', [
            'modules' => $this->registry->toOptions(),
            'exports' => DataExport::where('user_id', auth()->id())
                ->latest()
                ->take(20)
                ->get()
                ->map(fn (DataExport $export): array => $this->presentExport($export)),
        ]);
    }

    /**
     * Queue a new export for the selected modules.
     */
    public function store(StoreDataExportRequest $request): RedirectResponse
    {
        $export = DataExport::create([
            'user_id' => auth()->id(),
            'modules' => $request->validated('modules'),
            'filters' => $request->filters(),
            'status' => DataExport::STATUS_PENDING,
            'disk' => 'local',
        ]);

        GenerateDataExport::dispatch($export->id);

        return back()->with('success', 'Your export is being prepared. We will notify you when it is ready.');
    }

    /**
     * Return the current status of an export (used for polling).
     */
    public function show(DataExport $export): JsonResponse
    {
        $this->authorize('view', $export);

        return response()->json($this->presentExport($export));
    }

    /**
     * Stream the finished export file as a download.
     */
    public function download(DataExport $export): StreamedResponse
    {
        $this->authorize('view', $export);

        abort_unless($export->isDownloadable(), 404);

        $disk = Storage::disk($export->disk);

        abort_unless($disk->exists($export->file_path), 404);

        return $disk->download($export->file_path, $export->file_name);
    }

    /**
     * Delete an export and its stored file.
     */
    public function destroy(DataExport $export): RedirectResponse
    {
        $this->authorize('delete', $export);

        if ($export->file_path) {
            Storage::disk($export->disk)->delete($export->file_path);
        }

        $export->delete();

        return back()->with('success', 'Export deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function presentExport(DataExport $export): array
    {
        return [
            'id' => $export->id,
            'modules' => $export->modules,
            'filters' => $export->filters,
            'status' => $export->status,
            'file_name' => $export->file_name,
            'row_counts' => $export->row_counts,
            'error' => $export->error,
            'downloadable' => $export->isDownloadable(),
            'expires_at' => $export->expires_at?->toIso8601String(),
            'created_at' => $export->created_at?->toIso8601String(),
        ];
    }
}
