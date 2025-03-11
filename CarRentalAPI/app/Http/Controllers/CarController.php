<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Car Rental API",
 *      description="API de gestion des voitures en location",
 *      @OA\Contact(
 *          email="contact@carrental.com"
 *      ),
 * )
 */

/**
 * @OA\Schema(
 *     schema="Car",
 *     type="object",
 *     required={"brand", "model", "year", "price_per_day"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="brand", type="string", example="Toyota"),
 *     @OA\Property(property="model", type="string", example="Corolla"),
 *     @OA\Property(property="year", type="integer", example=2022),
 *     @OA\Property(property="price_per_day", type="number", format="float", example=50.5),
 *     @OA\Property(property="available", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CarController extends Controller
{
    /**
     * @OA\Get(
     *     path="/cars",
     *     summary="Liste des voitures",
     *     tags={"Cars"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste paginée des voitures",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Car")
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Car::paginate(10));
    }

    /**
     * @OA\Post(
     *     path="/cars",
     *     summary="Créer une voiture",
     *     tags={"Cars"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Car")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Voiture créée",
     *         @OA\JsonContent(ref="#/components/schemas/Car")
     *     ),
     *     @OA\Response(response=422, description="Données invalides")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'price_per_day' => 'required|numeric|min:0',
            'available' => 'boolean',
        ]);

        $car = Car::create($request->all());
        return response()->json($car, 201);
    }

    /**
     * @OA\Get(
     *     path="/cars/{id}",
     *     summary="Afficher une voiture spécifique",
     *     tags={"Cars"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails d'une voiture",
     *         @OA\JsonContent(ref="#/components/schemas/Car")
     *     ),
     *     @OA\Response(response=404, description="Voiture non trouvée")
     * )
     */
    public function show(Car $car)
    {
        return response()->json($car);
    }

    /**
     * @OA\Put(
     *     path="/cars/{id}",
     *     summary="Mettre à jour une voiture",
     *     tags={"Cars"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Car")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Voiture mise à jour",
     *         @OA\JsonContent(ref="#/components/schemas/Car")
     *     ),
     *     @OA\Response(response=404, description="Voiture non trouvée")
     * )
     */
    public function update(Request $request, Car $car)
    {
        $car->update($request->all());
        return response()->json($car);
    }

    /**
     * @OA\Delete(
     *     path="/cars/{id}",
     *     summary="Supprimer une voiture",
     *     tags={"Cars"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Voiture supprimée"),
     *     @OA\Response(response=404, description="Voiture non trouvée")
     * )
     */
    public function destroy(Car $car)
    {
        $car->delete();
        return response()->json(null, 204);
    }
}
