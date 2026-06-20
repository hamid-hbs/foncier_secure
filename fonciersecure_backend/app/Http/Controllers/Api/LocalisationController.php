<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Arrondissement;
use App\Models\Commune;
use App\Models\Quartier;
use Illuminate\Http\JsonResponse;

class LocalisationController extends Controller
{
    public function communes(): JsonResponse
    {
        return response()->json(Commune::with('arrondissements.quartiers')->get());
    }

    public function arrondissements(Commune $commune): JsonResponse
    {
        return response()->json($commune->arrondissements()->with('quartiers')->get());
    }

    public function quartiers(Arrondissement $arrondissement): JsonResponse
    {
        return response()->json($arrondissement->quartiers);
    }
}
