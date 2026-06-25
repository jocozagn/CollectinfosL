<?php

namespace App\Services;

use App\Models\Content;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function __construct(
        private SubscriptionService $subscriptions,
    ) {}

    public function items(): Collection
    {
        $ids = $this->ids();

        if ($ids === []) {
            return collect();
        }

        return Content::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Content $content) => array_search($content->id, $ids, true))
            ->values();
    }

    public function count(): int
    {
        return count($this->ids());
    }

    public function total(?User $user = null): float
    {
        return (float) $this->items()->sum(
            fn (Content $content) => $this->subscriptions->effectiveEurPrice($content, $user)
        );
    }

    public function has(int $contentId): bool
    {
        return in_array($contentId, $this->ids(), true);
    }

    public function add(Content $content): void
    {
        if (! $content->isPurchasable()) {
            return;
        }

        $ids = $this->ids();

        if (! in_array($content->id, $ids, true)) {
            $ids[] = $content->id;
            session([self::SESSION_KEY => $ids]);
        }
    }

    public function remove(int $contentId): void
    {
        $ids = array_values(array_filter(
            $this->ids(),
            fn (int $id) => $id !== $contentId
        ));

        session([self::SESSION_KEY => $ids]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function itemsFromIds(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return Content::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Content $content) => array_search($content->id, $ids, true))
            ->values();
    }

  /** @return list<int> */
    private function ids(): array
    {
        $ids = session(self::SESSION_KEY, []);

        return is_array($ids)
            ? array_values(array_unique(array_map('intval', $ids)))
            : [];
    }
}
