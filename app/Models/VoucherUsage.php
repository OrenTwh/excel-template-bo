<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class VoucherUsage extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'voucher_id',
        'order_id',
        'court_booking_id',
        'sport_product_order_id',
        'status',
    ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_id',
        'voucher_id',
        'order_id',
        'court_booking_id',
        'sport_product_order_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function courtBooking()
    {
        return $this->belongsTo(CourtBooking::class, 'court_booking_id');
    }

    public function sportProductOrder()
    {
        return $this->belongsTo(SportProductOrder::class, 'sport_product_order_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected static $logName = 'voucher_usages';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} voucher usage";
    }
}
