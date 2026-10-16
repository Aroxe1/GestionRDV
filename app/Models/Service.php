<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'description', 'duration_minutes', 'price_cents', 'is_active'];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
