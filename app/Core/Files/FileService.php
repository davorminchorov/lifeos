<?php

namespace App\Core\Files;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Store a file and create a file attachment record.
     *
     * @param UploadedFile $file
     * @param string $entityId
     * @param string $entityType
     * @param string $disk
     * @return FileAttachment
     */
    public function storeFile(
        UploadedFile $file,
        string $entityId,
        string $entityType,
        string $disk = 'local'
    ): FileAttachment {
        // Generate a unique filename
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Define the storage path (entity_type/entity_id)
        $path = "{$entityType}/{$entityId}";

        // Store the file
        $filePath = $file->storeAs($path, $fileName, $disk);

        // Create a file attachment record
        return FileAttachment::create([
            'entity_id' => $entityId,
            'entity_type' => $entityType,
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $disk,
        ]);
    }

    /**
     * Retrieve a file by ID.
     *
     * @param string $fileId
     * @return FileAttachment|null
     */
    public function getFileById(string $fileId): ?FileAttachment
    {
        return FileAttachment::find($fileId);
    }

    /**
     * Get all files attached to an entity.
     *
     * @param string $entityId
     * @param string $entityType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFilesForEntity(string $entityId, string $entityType)
    {
        return FileAttachment::where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->get();
    }

    /**
     * Get the file contents.
     *
     * @param FileAttachment $file
     * @return string|false
     */
    public function getFileContents(FileAttachment $file)
    {
        return Storage::disk($file->disk)->get($file->file_path);
    }

    /**
     * Delete a file and its record.
     *
     * @param FileAttachment $file
     * @return bool
     */
    public function deleteFile(FileAttachment $file): bool
    {
        // Delete from storage
        $deleted = Storage::disk($file->disk)->delete($file->file_path);

        // Delete the record if file was deleted successfully
        if ($deleted) {
            return $file->delete();
        }

        return false;
    }
}
