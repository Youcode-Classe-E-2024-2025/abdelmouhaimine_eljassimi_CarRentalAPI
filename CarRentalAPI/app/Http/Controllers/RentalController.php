<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rental;
use App\Models\Car;

class RentalController extends Controller
{

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



}
