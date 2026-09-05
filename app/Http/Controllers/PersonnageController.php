<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonnageRequest;
use App\Services\PersonnageCreationService;
use Illuminate\Http\JsonResponse;

class PersonnageController extends Controller
{
    public function store(StorePersonnageRequest $request, PersonnageCreationService $service): JsonResponse
    {
        $personnage = $service->create($request->validated());

        return response()->json($personnage, 201);
    }
}