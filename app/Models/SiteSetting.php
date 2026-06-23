<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        try {
            $setting = static::query()->where('key', $key)->first();

            if ($setting && $setting->value !== null && $setting->value !== '') {
                return $setting->value;
            }
        } catch (\Throwable) {
            //
        }

        return $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function localized(string $key, ?string $default = null): ?string
    {
        $locale = app()->getLocale();

        if ($locale !== config('locales.default', 'fr')) {
            $value = static::get($key.'_'.$locale);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return static::get($key, $default);
    }

    public static function localizedJson(string $key, array $default = []): array
    {
        $locale = app()->getLocale();

        if ($locale !== config('locales.default', 'fr')) {
            $value = static::json($key.'_'.$locale, []);

            if ($value !== []) {
                return $value;
            }
        }

        return static::json($key, $default);
    }

    public static function contact(): array
    {
        $defaults = config('collectinfos.contact', []);

        return [
            'phone' => static::get('contact_phone', $defaults['phone'] ?? ''),
            'email' => static::get('contact_email', $defaults['email'] ?? ''),
            'zone' => static::get('contact_zone', $defaults['zone'] ?? "Afrique de l'Ouest et panafrique — réseau de correspondants dans 18 pays."),
        ];
    }

    public static function press(): array
    {
        $defaults = config('collectinfos.press', []);

        return [
            'intro' => static::localized('press_intro', $defaults['intro'] ?? ''),
            'services' => static::localizedJson('press_services', $defaults['services'] ?? []),
        ];
    }

    public static function factChecking(): array
    {
        $defaults = config('collectinfos.fact_checking', []);

        return [
            'intro' => static::localized('fact_checking_intro', $defaults['intro'] ?? ''),
            'criteria' => static::localizedJson('fact_checking_criteria', $defaults['criteria'] ?? []),
            'cta' => static::localized('fact_checking_cta', 'Vous avez une information à vérifier ?'),
        ];
    }

    public static function json(string $key, array $default = []): array
    {
        $raw = static::get($key);

        if ($raw) {
            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $default;
    }

    public static function setJson(string $key, array $value): void
    {
        static::set($key, json_encode($value, JSON_UNESCAPED_UNICODE));
    }

    public static function lines(string $key): string
    {
        return implode("\n", static::json($key, []));
    }
}
