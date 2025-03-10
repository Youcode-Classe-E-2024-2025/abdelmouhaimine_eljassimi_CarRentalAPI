<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Car;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function __construct()
    {

        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function createPaymentIntent($rentalId)
    {
        try {
            $rental = Rental::findOrFail($rentalId);

            $price = $rental->total_price;

            $paymentIntent = PaymentIntent::create([
                'amount' => $price * 100,
                'currency' => 'usd',
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'rental_id' => $rental->id,
                'message' => 'payment success'
            ]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
