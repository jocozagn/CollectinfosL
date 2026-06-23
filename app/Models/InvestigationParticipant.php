<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestigationParticipant extends Model
{
    protected $fillable = [
        'investigation_id',
        'user_id',
        'collaboration_request_id',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function investigation(): BelongsTo
    {
        return $this->belongsTo(Investigation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collaborationRequest(): BelongsTo
    {
        return $this->belongsTo(CollaborationRequest::class);
    }
}
