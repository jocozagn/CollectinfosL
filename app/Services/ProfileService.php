<?php

namespace App\Services;

use App\Models\User;

class ProfileService
{
    /** @return list<string> */
    public function journalistMetaKeys(): array
    {
        return [
            'specialties',
            'languages',
            'nationality',
            'coverage_zones',
            'experience_years',
            'press_card',
            'portfolio_url',
            'media_worked',
            'linkedin_url',
            'twitter_url',
            'payment_preference',
            'mobile_money_number',
            'bank_details',
        ];
    }

    /** @return list<string> */
    public function buyerMetaKeys(): array
    {
        return [
            'organization_name',
            'structure_type',
            'organization_address',
            'website',
            'siret',
            'contact_name',
            'contact_title',
            'editorial_themes',
            'geo_priorities',
            'content_types_sought',
            'monthly_order_volume',
            'billing_address',
            'payment_preference',
            'billing_details',
        ];
    }

    /** @return array{percent: int, missing: list<string>} */
    public function completion(User $user): array
    {
        $required = $this->requiredFields($user);
        $filled = 0;
        $missing = [];

        foreach ($required as $label => $value) {
            if ($this->isFilled($value)) {
                $filled++;
            } else {
                $missing[] = $label;
            }
        }

        $total = count($required);

        return [
            'percent' => $total > 0 ? (int) round(($filled / $total) * 100) : 100,
            'missing' => $missing,
        ];
    }

    /** @return array<string, mixed> */
    public function extractMetaFromRequest(array $data, User $user): array
    {
        $keys = $user->isJournalist()
            ? array_merge($this->journalistMetaKeys(), $this->buyerMetaKeys())
            : $this->buyerMetaKeys();

        $meta = $user->profile_meta ?? [];

        foreach (array_unique($keys) as $key) {
            if (array_key_exists($key, $data)) {
                $meta[$key] = $data[$key];
            }
        }

        return array_filter($meta, fn ($v) => $v !== null && $v !== '');
    }

    /** @return array<string, mixed> */
    private function requiredFields(User $user): array
    {
        $base = [
            'Nom' => $user->name,
            'E-mail' => $user->email,
            'Téléphone' => $user->phone,
            'Pays' => $user->country,
        ];

        if ($user->isJournalist()) {
            return array_merge($base, [
                'Ville / région' => $user->city,
                'Bio' => $user->bio,
                'Spécialités' => $user->meta('specialties'),
                'Langues' => $user->meta('languages'),
                'Zones couvertes' => $user->meta('coverage_zones'),
                'Portfolio' => $user->meta('portfolio_url'),
            ]);
        }

        return array_merge($base, [
            'Organisation' => $user->meta('organization_name'),
            'Type de structure' => $user->meta('structure_type'),
            'Thématiques recherchées' => $user->meta('editorial_themes'),
        ]);
    }

    private function isFilled(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }
}
