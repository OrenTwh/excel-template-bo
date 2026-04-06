<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'destination',
        'type',
    ];
}
