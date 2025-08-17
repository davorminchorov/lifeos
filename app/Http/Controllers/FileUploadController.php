<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    /**
     * Allowed file types for different categories
     */
    const ALLOWED_TYPES = [
        'documents' => ['pdf', 'doc', 'docx', 'txt'],
        'receipts' => ['pdf', 'jpg', 'jpeg', 'png', 'gif'],
        'contracts' => ['pdf', 'doc', 'docx'],
        'warranties' => ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx'],
    ];

    /**
     * Maximum file size in KB
     */
    const MAX_FILE_SIZE = 10240; // 10MB

    /**
     * Upload a file to the specified category disk
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request, string $category)
    {
        // Validate category
        if (! array_key_exists($category, self::ALLOWED_TYPES)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file category',
            ], 400);
        }

        // Validate file
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:'.self::MAX_FILE_SIZE,
                'mimes:'.implode(',', self::ALLOWED_TYPES[$category]),
            ],
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();

            // Generate unique filename
            $filename = Str::uuid().'_'.time().'.'.$extension;

            // Store file
            $path = $file->storeAs('', $filename, $category);

            if (! $path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload file',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'original_name' => $originalName,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'category' => $category,
                    'name' => $request->input('name', $originalName),
                    'description' => $request->input('description'),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a file from the specified category disk
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(string $category, string $filename)
    {
        // Validate category
        if (! array_key_exists($category, self::ALLOWED_TYPES)) {
            abort(400, 'Invalid file category');
        }

        // Check if file exists
        if (! Storage::disk($category)->exists($filename)) {
            abort(404, 'File not found');
        }

        try {
            $path = Storage::disk($category)->path($filename);
            $mimeType = Storage::disk($category)->mimeType($filename);

            return response()->download($path, $filename, [
                'Content-Type' => $mimeType,
            ]);

        } catch (\Exception $e) {
            abort(500, 'Failed to download file: '.$e->getMessage());
        }
    }

    /**
     * View a file (for PDFs and images)
     *
     * @return Response
     */
    public function view(string $category, string $filename)
    {
        // Validate category
        if (! array_key_exists($category, self::ALLOWED_TYPES)) {
            abort(400, 'Invalid file category');
        }

        // Check if file exists
        if (! Storage::disk($category)->exists($filename)) {
            abort(404, 'File not found');
        }

        try {
            $content = Storage::disk($category)->get($filename);
            $mimeType = Storage::disk($category)->mimeType($filename);

            // Only allow viewing of safe file types
            $viewableMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'text/plain'];

            if (! in_array($mimeType, $viewableMimes)) {
                return $this->download($category, $filename);
            }

            return response($content, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);

        } catch (\Exception $e) {
            abort(500, 'Failed to view file: '.$e->getMessage());
        }
    }

    /**
     * Delete a file from the specified category disk
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $category, string $filename)
    {
        // Validate category
        if (! array_key_exists($category, self::ALLOWED_TYPES)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file category',
            ], 400);
        }

        // Check if file exists
        if (! Storage::disk($category)->exists($filename)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        try {
            Storage::disk($category)->delete($filename);

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get file information
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFileInfo(string $category, string $filename)
    {
        // Validate category
        if (! array_key_exists($category, self::ALLOWED_TYPES)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file category',
            ], 400);
        }

        // Check if file exists
        if (! Storage::disk($category)->exists($filename)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        try {
            $size = Storage::disk($category)->size($filename);
            $lastModified = Storage::disk($category)->lastModified($filename);
            $mimeType = Storage::disk($category)->mimeType($filename);

            return response()->json([
                'success' => true,
                'data' => [
                    'filename' => $filename,
                    'category' => $category,
                    'size' => $size,
                    'size_human' => $this->formatBytes($size),
                    'mime_type' => $mimeType,
                    'last_modified' => date('Y-m-d H:i:s', $lastModified),
                    'download_url' => route('files.download', ['category' => $category, 'filename' => $filename]),
                    'view_url' => route('files.view', ['category' => $category, 'filename' => $filename]),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get file info: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format bytes to human readable format
     *
     * @param  int  $size
     * @param  int  $precision
     * @return string
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision).' '.$units[$i];
    }

    /**
     * Get allowed file types for a category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllowedTypes(?string $category = null)
    {
        if ($category && ! array_key_exists($category, self::ALLOWED_TYPES)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file category',
            ], 400);
        }

        $types = $category ? [$category => self::ALLOWED_TYPES[$category]] : self::ALLOWED_TYPES;

        return response()->json([
            'success' => true,
            'data' => $types,
            'max_size' => self::MAX_FILE_SIZE,
        ]);
    }
}
