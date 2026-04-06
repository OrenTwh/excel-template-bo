<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Helper;

class PopularArena extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'sequence',
        'status',
    ];

    protected $casts = [
        'venue_id' => 'integer',
        'sequence' => 'integer',
        'status'   => 'integer',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }
}
