<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Helper;

class Tyre extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'code',
        'name',
    ];

    public function vendor() {
        return $this->belongsTo( Vendor::class, 'vendor_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }
}
