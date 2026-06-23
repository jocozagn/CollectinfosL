<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PressRequest extends Model
{
    protected $fillable = [
        'company_name',
        'email',
        'experience_years',
        'company_experience',
        'topics',
        'topics_other',
        'country',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'topics' => 'array',
            'experience_years' => 'integer',
        ];
    }

    public function experienceLabel(): string
    {
        return match (true) {
            $this->experience_years <= 0 => 'Non renseigné',
            $this->experience_years >= 10 => '10 ans et plus',
            $this->experience_years === 1 => '1 an',
            default => $this->experience_years.' ans',
        };
    }

    public function topicsLabels(): array
    {
        $map = config('collectinfos.press.topics', []);
        $labels = [];

        foreach ($this->topics ?? [] as $key) {
            if ($key === 'other' && $this->topics_other) {
                $labels[] = 'Autre : '.$this->topics_other;
            } elseif (isset($map[$key])) {
                $labels[] = $map[$key];
            }
        }

        return $labels;
    }
}
