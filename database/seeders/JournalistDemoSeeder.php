<?php

namespace Database\Seeders;

use App\Models\CollaborationRequest;
use App\Models\Investigation;
use App\Models\InvestigationParticipant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class JournalistDemoSeeder extends Seeder
{
    public function run(): void
    {
        $journalist = User::query()->updateOrCreate(
            ['email' => 'journalist@collectinfos.org'],
            [
                'name' => 'Journaliste Démo',
                'password' => Hash::make('demo123'),
                'role' => 'journalist',
            ]
        );

        $owned = Investigation::query()->where('title', 'Économie informelle et jeunesse à Conakry')->first();
        if ($owned) {
            $owned->update([
                'user_id' => $journalist->id,
                'status' => 'pending',
                'published_at' => null,
            ]);
        }

        $joined = Investigation::query()->where('title', 'Migrations climatiques en Afrique de l\'Ouest')->first();
        if ($joined) {
            InvestigationParticipant::query()->updateOrCreate(
                [
                    'investigation_id' => $joined->id,
                    'user_id' => $journalist->id,
                ],
                ['joined_at' => now()->subDays(5)]
            );
        }

        CollaborationRequest::query()->updateOrCreate(
            [
                'email' => $journalist->email,
                'type' => 'join',
                'investigation_id' => $joined?->id,
            ],
            [
                'user_id' => $journalist->id,
                'name' => $journalist->name,
                'country' => 'Guinée',
                'message' => 'Je souhaite contribuer à cette enquête transfrontalière sur les migrations climatiques.',
                'status' => 'accepted',
            ]
        );

        CollaborationRequest::query()->updateOrCreate(
            [
                'email' => $journalist->email,
                'type' => 'propose',
                'proposed_title' => 'Accès à l\'eau potable en zone rurale',
            ],
            [
                'user_id' => $journalist->id,
                'name' => $journalist->name,
                'country' => 'Guinée',
                'message' => 'Proposition d\'enquête sur les infrastructures hydrauliques et les inégalités d\'accès.',
                'status' => 'pending',
            ]
        );
    }
}
