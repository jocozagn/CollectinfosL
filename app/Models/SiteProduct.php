<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class SiteProduct extends Model
{
    use HasTranslations;
    protected $fillable = [
        'name',
        'description',
        'price',
        'icon',
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
            $items = static::query()
                ->where('is_active', true)
                ->with('translations')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (self $p) => [
                    'name' => $p->trans('name'),
                    'description' => $p->trans('description'),
                    'price' => $p->price,
                    'icon' => $p->icon,
                ])
                ->all();

            if ($items !== []) {
                return $items;
            }
        } catch (\Throwable) {
            //
        }

        return config('collectinfos.products', []);
    }
}
