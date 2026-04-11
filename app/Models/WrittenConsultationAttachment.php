<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class WrittenConsultationAttachment extends Model
{
    protected $fillable = [
        'written_consultation_request_id',
        'original_name',
        'path',
        'size',
        'mime_type',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(WrittenConsultationRequest::class, 'written_consultation_request_id');
    }

    public function url(): string
    {
        return Storage::disk('private')->url($this->path);
    }

    public function humanSize(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1048576, 1) . ' MB';
    }
}
