<?php

namespace App\Models\Concerns;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function trans(string $field, ?string $locale = null): mixed
    {
        $locale = $locale ?? app()->getLocale();
        $default = config('locales.default', 'fr');

        if ($locale === $default) {
            return $this->getAttribute($field);
        }

        if ($this->relationLoaded('translations')) {
            $value = $this->translations
                ->where('locale', $locale)
                ->where('field', $field)
                ->value('value');
        } else {
            $value = $this->translations()
                ->where('locale', $locale)
                ->where('field', $field)
                ->value('value');
        }

        return ($value !== null && $value !== '') ? $value : $this->getAttribute($field);
    }

    public function saveTranslations(string $locale, array $fields): void
    {
        if ($locale === config('locales.default', 'fr')) {
            return;
        }

        foreach ($fields as $field => $value) {
            if ($value === null || trim((string) $value) === '') {
                $this->translations()
                    ->where('locale', $locale)
                    ->where('field', $field)
                    ->delete();

                continue;
            }

            $this->translations()->updateOrCreate(
                ['locale' => $locale, 'field' => $field],
                ['value' => $value]
            );
        }
    }

    public function translationMap(): array
    {
        $map = [];

        foreach ($this->translations as $translation) {
            $map[$translation->locale][$translation->field] = $translation->value;
        }

        return $map;
    }
}
