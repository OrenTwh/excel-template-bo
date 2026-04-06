<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdministratorNotificationSeen extends Model
{
    use HasFactory;

    protected $fillable = [
        'an_id',
        'a_id',
    ];
}
