<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InvestigationFile extends Model
{
    protected $fillable = [
        'investigation_id',
        'user_id',
        'original_name',
        'path',
        'mime_type',
        'size',
        'description',
    ];

    public function investigation(): BelongsTo
    {
        return $this->belongsTo(Investigation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function humanSize(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' Mo';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' Ko';
        }

        return $bytes.' o';
    }

    public function deleteFromStorage(): void
    {
        if ($this->path && ! str_starts_with($this->path, 'http')) {
            Storage::disk('public')->delete($this->path);
        }
    }
}
