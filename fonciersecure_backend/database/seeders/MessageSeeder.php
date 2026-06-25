<?php

namespace Database\Seeders;

use App\Models\Message;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        Message::create([
            'messageable_type' => 'App\Models\DemandeAchat',
            'messageable_id' => 1,
            'sender_id' => 2,
            'receiver_id' => 4,
            'contenu' => 'Bonjour, est-ce que le prix est négociable ?',
        ]);

        Message::create([
            'messageable_type' => 'App\Models\DemandeAchat',
            'messageable_id' => 1,
            'sender_id' => 4,
            'receiver_id' => 2,
            'contenu' => 'Bonjour, je suis ouvert à une négociation. Quel est votre proposition ?',
        ]);

        Message::create([
            'messageable_type' => 'App\Models\DossierTransaction',
            'messageable_id' => 1,
            'sender_id' => 9,
            'receiver_id' => 2,
            'contenu' => 'Bonjour, j\'ai préparé l\'acte de vente. Veuillez prendre rendez-vous pour la signature.',
        ]);
    }
}
