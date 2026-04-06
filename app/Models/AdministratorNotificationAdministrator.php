<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdministratorNotificationAdministrator extends Model
{
    use HasFactory;

    protected $fillable = [
        'an_id',
        'a_id',
    ];

    public function administratorNotification() {
        return $this->belongsTo( AdministratorNotification::class, 'an_id' );
    }

    public function administrator() {
        return $this->belongsTo( Administrator::class, 'a_id' );
    }
}
