<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Helper;

class CourtCalendarParticipant extends Model
{
    use HasFactory;

    const STATUS_CONFIRMED  = 10;
    const STATUS_CANCELLED  = 20;

    protected $fillable = [
        'court_calendar_id',
        'user_id',
        'status',
        'joined_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'court_calendar_id' => 'integer',
        'user_id'           => 'integer',
        'status'            => 'integer',
        'joined_at'         => 'datetime',
        'cancelled_at'      => 'datetime',
    ];

    public function courtCalendar()
    {
        return $this->belongsTo(CourtCalendar::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->attributes['status']) {
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CANCELLED => 'Cancelled',
            default                => 'Unknown',
        };
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }
}
