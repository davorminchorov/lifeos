<?php

namespace App\Core\Files;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FileAttachment extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entity_id',
        'entity_type',
        'file_name',
        'original_name',
        'file_path',
        'mime_type',
        'size',
        'disk',
    ];

    /**
     * The model's primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file_attachments';

    /**
     * Get the parent entity that the file is attached to.
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the full URL to access the file.
     */
    public function getUrlAttribute(): string
    {
        return url("api/files/{$this->id}");
    }

    /**
     * Get the download URL for the file.
     */
    public function getDownloadUrlAttribute(): string
    {
        return url("api/files/{$this->id}/download");
    }
}
