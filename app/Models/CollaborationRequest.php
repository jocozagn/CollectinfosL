<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollaborationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'investigation_id',
        'type',
        'proposed_title',
        'name',
        'email',
        'phone',
        'country',
        'message',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function investigation(): BelongsTo
    {
        return $this->belongsTo(Investigation::class);
    }

    public function typeLabel(): string
    {
        return $this->type === 'join' ? 'Rejoindre une enquête' : 'Proposer une enquête';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'accepted' => 'Acceptée',
            'rejected' => 'Refusée',
            default => 'En attente',
        };
    }
}
