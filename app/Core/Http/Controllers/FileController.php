<?php

namespace App\Core\Http\Controllers;

use App\Core\Files\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends ApiController
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Upload a file and attach it to an entity.
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB max
            'entity_id' => 'required|string',
            'entity_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $entityId = $request->input('entity_id');
        $entityType = $request->input('entity_type');

        $fileAttachment = $this->fileService->storeFile($file, $entityId, $entityType);

        return response()->json([
            'id' => $fileAttachment->id,
            'url' => $fileAttachment->url,
            'download_url' => $fileAttachment->download_url,
            'name' => $fileAttachment->original_name,
            'mime_type' => $fileAttachment->mime_type,
            'size' => $fileAttachment->size,
        ], 201);
    }

    /**
     * Get files attached to an entity.
     */
    public function getFiles(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity_id' => 'required|string',
            'entity_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $entityId = $request->input('entity_id');
        $entityType = $request->input('entity_type');

        $files = $this->fileService->getFilesForEntity($entityId, $entityType);

        $fileData = $files->map(function ($file) {
            return [
                'id' => $file->id,
                'url' => $file->url,
                'download_url' => $file->download_url,
                'name' => $file->original_name,
                'mime_type' => $file->mime_type,
                'size' => $file->size,
                'created_at' => $file->created_at,
            ];
        });

        return response()->json(['data' => $fileData]);
    }

    /**
     * Display a file (for inline viewing).
     */
    public function show(string $id): Response
    {
        $file = $this->fileService->getFileById($id);

        if (!$file) {
            abort(404, 'File not found');
        }

        $content = $this->fileService->getFileContents($file);

        return response($content)
            ->header('Content-Type', $file->mime_type)
            ->header('Content-Disposition', 'inline; filename="' . $file->original_name . '"');
    }

    /**
     * Download a file.
     */
    public function download(string $id): Response
    {
        $file = $this->fileService->getFileById($id);

        if (!$file) {
            abort(404, 'File not found');
        }

        $content = $this->fileService->getFileContents($file);

        return response($content)
            ->header('Content-Type', $file->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $file->original_name . '"');
    }

    /**
     * Delete a file.
     */
    public function delete(string $id): JsonResponse
    {
        $file = $this->fileService->getFileById($id);

        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $deleted = $this->fileService->deleteFile($file);

        if ($deleted) {
            return response()->json(['message' => 'File deleted successfully']);
        }

        return response()->json(['error' => 'Failed to delete file'], 500);
    }
}
