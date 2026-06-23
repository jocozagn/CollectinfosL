<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteStat extends Model
{
    protected $fillable = [
        'value',
        'label',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function activeList(): array
    {
        try {
            $stats = static::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (self $stat) => [
                    'value' => $stat->value,
                    'label' => $stat->label,
                ])
                ->all();

            if ($stats !== []) {
                return $stats;
            }
        } catch (\Throwable) {
            //
        }

        return config('collectinfos.stats', []);
    }
}
