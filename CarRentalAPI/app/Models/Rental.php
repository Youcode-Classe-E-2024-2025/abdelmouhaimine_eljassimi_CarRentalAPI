<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
