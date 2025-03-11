<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Rental",
 *     title="Rental",
 *     description="Rental model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="user_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="car_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="start_date", type="string", format="date", example="2023-10-01"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2023-10-05"),
 *     @OA\Property(property="total_price", type="number", format="float", example=250.00),
 *     @OA\Property(property="status", type="string", enum={"pending", "active", "completed", "cancelled"}, example="pending"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T00:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-01T00:00:00.000000Z"),
 *     @OA\Property(
 *         property="car",
 *         ref="#/components/schemas/Car"
 *     )
 * )
 */

class Rental extends Model
{


    protected $table = 'rentals';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }


}
