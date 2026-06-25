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
        'is_subscribable',
        'price_eur',
        'price_gnf',
        'billing_months',
        'discount_percent',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_subscribable' => 'boolean',
            'price_eur' => 'decimal:2',
            'price_gnf' => 'integer',
            'billing_months' => 'integer',
            'discount_percent' => 'integer',
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
                    'id' => $p->id,
                    'name' => $p->trans('name'),
                    'description' => $p->trans('description'),
                    'price' => $p->price,
                    'icon' => $p->icon,
                    'is_subscribable' => $p->is_subscribable,
                    'price_eur' => $p->price_eur,
                    'price_gnf' => $p->price_gnf,
                    'billing_months' => $p->billing_months,
                    'discount_percent' => $p->discount_percent,
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

    public function isSubscribable(): bool
    {
        return $this->is_subscribable
            && $this->price_eur !== null
            && (float) $this->price_eur > 0;
    }

    public function billingLabel(): string
    {
        return match ((int) $this->billing_months) {
            1 => 'Mensuel',
            12 => 'Annuel',
            default => (int) $this->billing_months.' mois',
        };
    }

    public function gnfAmount(): int
    {
        if ($this->price_gnf) {
            return (int) $this->price_gnf;
        }

        return (int) round((float) $this->price_eur * (float) config('collectinfos.currency.eur_to_gnf', 10000));
    }
}
