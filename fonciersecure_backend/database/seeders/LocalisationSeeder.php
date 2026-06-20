<?php

namespace Database\Seeders;

use App\Models\Arrondissement;
use App\Models\Commune;
use App\Models\Quartier;
use Illuminate\Database\Seeder;

class LocalisationSeeder extends Seeder
{
    public function run(): void
    {
        $cotonou = Commune::create(['nom' => 'Cotonou']);
        $calavi = Commune::create(['nom' => 'Abomey-Calavi']);
        $portoNovo = Commune::create(['nom' => 'Porto-Novo']);
        $parakou = Commune::create(['nom' => 'Parakou']);
        $lokossa = Commune::create(['nom' => 'Lokossa']);

        $cotonouArr1 = Arrondissement::create(['nom' => '1er Arrondissement', 'commune_id' => $cotonou->id]);
        $cotonouArr2 = Arrondissement::create(['nom' => '2ème Arrondissement', 'commune_id' => $cotonou->id]);
        $cotonouArr3 = Arrondissement::create(['nom' => '3ème Arrondissement', 'commune_id' => $cotonou->id]);
        $cotonouArr4 = Arrondissement::create(['nom' => '4ème Arrondissement', 'commune_id' => $cotonou->id]);
        $cotonouArr5 = Arrondissement::create(['nom' => '5ème Arrondissement', 'commune_id' => $cotonou->id]);
        $cotonouArr6 = Arrondissement::create(['nom' => '6ème Arrondissement', 'commune_id' => $cotonou->id]);
        $cotonouArr7 = Arrondissement::create(['nom' => '7ème Arrondissement', 'commune_id' => $cotonou->id]);
        $cotonouArr8 = Arrondissement::create(['nom' => '8ème Arrondissement', 'commune_id' => $cotonou->id]);
        $cotonouArr9 = Arrondissement::create(['nom' => '9ème Arrondissement', 'commune_id' => $cotonou->id]);
        $cotonouArr10 = Arrondissement::create(['nom' => '10ème Arrondissement', 'commune_id' => $cotonou->id]);
        $cotonouArr11 = Arrondissement::create(['nom' => '11ème Arrondissement', 'commune_id' => $cotonou->id]);
        $cotonouArr12 = Arrondissement::create(['nom' => '12ème Arrondissement', 'commune_id' => $cotonou->id]);
        $cotonouArr13 = Arrondissement::create(['nom' => '13ème Arrondissement', 'commune_id' => $cotonou->id]);

        $calaviArr1 = Arrondissement::create(['nom' => '1er Arrondissement', 'commune_id' => $calavi->id]);
        $calaviArr2 = Arrondissement::create(['nom' => '2ème Arrondissement', 'commune_id' => $calavi->id]);
        $calaviArr3 = Arrondissement::create(['nom' => '3ème Arrondissement', 'commune_id' => $calavi->id]);

        $pnArr1 = Arrondissement::create(['nom' => '1er Arrondissement', 'commune_id' => $portoNovo->id]);
        $pnArr2 = Arrondissement::create(['nom' => '2ème Arrondissement', 'commune_id' => $portoNovo->id]);
        $pnArr3 = Arrondissement::create(['nom' => '3ème Arrondissement', 'commune_id' => $portoNovo->id]);

        $parakouArr1 = Arrondissement::create(['nom' => '1er Arrondissement', 'commune_id' => $parakou->id]);
        $parakouArr2 = Arrondissement::create(['nom' => '2ème Arrondissement', 'commune_id' => $parakou->id]);
        $parakouArr3 = Arrondissement::create(['nom' => '3ème Arrondissement', 'commune_id' => $parakou->id]);

        $lokossaArr = Arrondissement::create(['nom' => 'Arrondissement Central', 'commune_id' => $lokossa->id]);

        $quartiers = [
            [$cotonouArr1->id, ['Ganhi', 'Guinkomè', 'Suru Léré']],
            [$cotonouArr2->id, ['Jéricho', 'Ahouansori', 'Agondji']],
            [$cotonouArr3->id, ['Gbégamey', 'Gangban', 'Amidaho']],
            [$cotonouArr4->id, ['Akpakpa', 'Agla', 'Sainte Cécile']],
            [$cotonouArr5->id, ['Gbèto', 'Avotrou', 'Mènontin']],
            [$cotonouArr6->id, ['Fidjrossè', 'Kpota', 'Kouhounou']],
            [$cotonouArr7->id, ['Vossa', 'Donaten', 'Aïdjèdo']],
            [$cotonouArr8->id, ['Enagnon', 'Hlazounto', 'Dédokpo']],
            [$cotonouArr9->id, ['Sainte Cécile', 'Agla Sud', 'Ahouansori']],
            [$cotonouArr10->id, ['Dandji', 'Hôpital', 'Commerce']],
            [$cotonouArr11->id, ['Gbèto Nord', 'Aïdjèdo', 'Tokplegbè']],
            [$cotonouArr12->id, ['Ganhi Ouest', 'Suru Léré', 'Gbènamè']],
            [$cotonouArr13->id, ['Kouhounou', 'Sènadé', 'Agontinkon']],
            [$calaviArr1->id, ['Zogbadjè', 'Tokpa', 'Glo-Djigbé']],
            [$calaviArr2->id, ['Akassato', 'Ouèdo', 'Togoudo']],
            [$calaviArr3->id, ['Godomey', 'Kpota Calavi', 'Zènan']],
            [$pnArr1->id, ['Ouando', 'Sèkèrè', 'Dowa']],
            [$pnArr2->id, ['Djèrègbè', 'Akpakpa Porto', 'Tokple']],
            [$pnArr3->id, ['Ponka', 'Gounoukogbé', 'Setto']],
            [$parakouArr1->id, ['Camp Guezo', 'Albarika', 'Bawéra']],
            [$parakouArr2->id, ['Zongo', 'Banikanni', 'Kpérou']],
            [$parakouArr3->id, ['Maro', 'Sabouta', 'Béthol']],
            [$lokossaArr->id, ['Centre', 'Agbara', 'Kpodji']],
        ];

        foreach ($quartiers as [$arrId, $noms]) {
            foreach ($noms as $nom) {
                Quartier::create(['nom' => $nom, 'arrondissement_id' => $arrId]);
            }
        }
    }
}
