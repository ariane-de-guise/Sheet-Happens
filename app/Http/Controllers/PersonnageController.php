<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonnageRequest;
use App\Services\PersonnageCreationService;
use Illuminate\Http\JsonResponse;

class PersonnageController extends Controller
{
    public function store(StorePersonnageRequest $request, PersonnageCreationService $service): JsonResponse
    {
        $payload = array_merge($request->validated(), [
            'utilisateur_id' => $request->user()->id,
        ]);

        $personnage = $service->create($payload);

        return response()->json($personnage, 201);
    }
}
