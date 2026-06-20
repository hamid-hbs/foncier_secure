<?php

namespace App\Services;

use App\Models\DossierTransaction;
use App\Models\Notification;
use App\Models\User;

class IndiceConfianceService
{
    public function ajouterPoints(User|int $user, int $points, string $raison): int
    {
        $user = $this->resoudreUtilisateur($user);
        $score = min(100, ($user->indice_confiance ?? 0) + $points);
        $user->update(['indice_confiance' => $score]);
        return $score;
    }

    public function retirerPoints(User|int $user, int $points, string $raison): int
    {
        $user = $this->resoudreUtilisateur($user);
        $score = max(0, ($user->indice_confiance ?? 0) - $points);
        $user->update(['indice_confiance' => $score]);
        return $score;
    }

    private function resoudreUtilisateur(User|int $user): User
    {
        if ($user instanceof User) {
            return $user;
        }
        return User::findOrFail($user);
    }

    public function recalculer(User $user): int
    {
        $score = 0;

        $transactionsReussies = DossierTransaction::where(function ($q) use ($user) {
            $q->where('vendeur_id', $user->id)->orWhere('acheteur_id', $user->id);
        })->where('statut', 'cloture')->count();
        $score += $transactionsReussies * 15;

        $annees = $user->created_at?->diffInYears(now()) ?? 0;
        $score += $annees * 2;

        $score = max(0, min(100, $score));

        $user->update(['indice_confiance' => $score]);

        return $score;
    }
}
