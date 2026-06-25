<?php

namespace Database\Seeders;

use App\Models\RendezVous;
use App\Models\RendezVousParticipant;
use Illuminate\Database\Seeder;

class RendezVousSeeder extends Seeder
{
    public function run(): void
    {
        $rv = RendezVous::create([
            'lie_type' => 'App\Models\DossierTransaction',
            'lie_id' => 1,
            'type' => 'signature',
            'date_time' => now()->addDays(7),
            'lieu' => 'Étude Da Silva, Cotonou',
            'organisateur_id' => 9,
            'statut' => 'planifie',
        ]);

        foreach ([
            ['rendez_vous_id' => $rv->id, 'user_id' => 3, 'est_confirme' => true, 'date_confirmation' => now()],
            ['rendez_vous_id' => $rv->id, 'user_id' => 2, 'est_confirme' => false],
            ['rendez_vous_id' => $rv->id, 'user_id' => 9, 'est_confirme' => true, 'date_confirmation' => now()],
        ] as $p) { RendezVousParticipant::create($p); }

        $rv2 = RendezVous::create([
            'lie_type' => 'App\Models\Mission',
            'lie_id' => 1,
            'type' => 'visite_terrain',
            'date_time' => now()->subDays(15),
            'lieu' => 'Gbégamey, Cotonou',
            'organisateur_id' => 5,
            'statut' => 'effectue',
        ]);

        foreach ([
            ['rendez_vous_id' => $rv2->id, 'user_id' => 2, 'est_confirme' => true, 'date_confirmation' => now()->subDays(15)],
            ['rendez_vous_id' => $rv2->id, 'user_id' => 5, 'est_confirme' => true, 'date_confirmation' => now()->subDays(15)],
        ] as $p) { RendezVousParticipant::create($p); }
    }
}
