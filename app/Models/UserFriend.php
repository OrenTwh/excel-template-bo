<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Model;

use Helper;

class UserFriend extends Model
{
    protected $table = 'user_friends';

    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
    ];

    // status constants
    const STATUS_PENDING  = 10;
    const STATUS_ACCEPTED = 20;
    const STATUS_DECLINED = 30;

    public function user()
    {
        return $this->belongsTo( User::class, 'user_id' );
    }

    public function friend()
    {
        return $this->belongsTo( User::class, 'friend_id' );
    }

    public function getEncryptedIdAttribute()
    {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date )
    {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }
}
