<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rental;
use App\Models\Car;

/**
 * @OA\Tag(
 *     name="Rentals",
 *     description="Endpoints for managing car rentals"
 * )
 */
class RentalController extends Controller
{
    /**
     * @OA\Get(
     *     path="/rentals",
     *     summary="Get all rentals for the authenticated user",
     *     tags={"Rentals"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of rentals",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Rental")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function index()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $rentals = $user->rentals()->with('car')->paginate(10);
            return response()->json($rentals);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/rentals",
     *     summary="Create a new rental",
     *     tags={"Rentals"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"car_id", "start_date", "end_date"},
     *             @OA\Property(property="car_id", type="integer", example=1),
     *             @OA\Property(property="start_date", type="string", format="date", example="2023-10-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2023-10-05")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rental created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Rental")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (e.g., car not available)"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'car_id' => 'required|exists:cars,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $car = Car::findOrFail($request->car_id);

        if (!$car->available) {
            return response()->json(['error' => 'Car is not available'], 400);
        }

        $days = \Carbon\Carbon::parse($request->start_date)->diffInDays($request->end_date);
        $total_price = $days * $car->price_per_day;

        $rental = Rental::create([
            'user_id' => auth()->id(),
            'car_id' => $car->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_price' => $total_price,
            'status' => 'pending',
        ]);

        $car->update(['available' => false]);

        return response()->json($rental, 201);
    }

    /**
     * @OA\Post(
     *     path="/rentals/{rental}/cancel",
     *     summary="Cancel a rental",
     *     tags={"Rentals"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="rental",
     *         in="path",
     *         required=true,
     *         description="ID of the rental to cancel",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rental cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rental cancelled successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (e.g., rental cannot be cancelled)"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function cancel(Rental $rental)
    {
        if ($rental->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($rental->status !== 'pending') {
            return response()->json(['error' => 'Rental cannot be cancelled'], 400);
        }

        $rental->update(['status' => 'cancelled']);

        $rental->car->update(['available' => true]);

        return response()->json(['message' => 'Rental cancelled successfully']);
    }
}
