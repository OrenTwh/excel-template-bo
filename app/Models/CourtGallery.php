<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Helper;

class CourtGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'court_id',
        'image',
        'status',
    ];

    protected $casts = [
        'court_id' => 'integer',
        'status'   => 'integer',
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->attributes['image'] ? asset('storage/' . $this->attributes['image']) : null;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }
}
