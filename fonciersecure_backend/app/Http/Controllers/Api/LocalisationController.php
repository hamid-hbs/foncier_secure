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
        return response()->json(Commune::orderBy('nom')->get());
    }

    public function arrondissements(Commune $commune): JsonResponse
    {
        return response()->json($commune->arrondissements()->orderBy('nom')->get());
    }

    public function quartiers(Arrondissement $arrondissement): JsonResponse
    {
        return response()->json($arrondissement->quartiers()->orderBy('nom')->get());
    }
}
