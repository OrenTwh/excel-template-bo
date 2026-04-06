<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;
use App\Models\TicketType;

class Voucher extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    // applicable_to values
    const APPLICABLE_ALL     = 'all';
    const APPLICABLE_BOOKING = 'booking';
    const APPLICABLE_PRODUCT = 'product';

    protected $fillable = [
        'promo_code',
        'title',
        'description',
        'image',
        'start_date',
        'expired_date',
        'discount_type',
        'discount_amount',
        'type',
        'applicable_to',
        'status',
        'usable_amount',
        'points_required',
        'min_spend',
        'min_order',
        'buy_x_get_y_adjustment',
        'total_claimable',
        'validity_days',
        'claim_per_user',
    ];

    public function announcement()
    {
        return $this->hasOne( Announcement::class, 'voucher_id' );
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }
    
    public function getDecodedAdjustmentAttribute()
    {
        if (!$this->attributes['buy_x_get_y_adjustment']) {
            return null;
        }
    
        $adjustment = json_decode($this->attributes['buy_x_get_y_adjustment'], true);

        $adjustment['discount_type'] = $this->discount_type_label;

        if (isset($adjustment['buy_ticket_types']) && is_array($adjustment['buy_ticket_types'])) {

            $ticketTypes = TicketType::whereIn('id', $adjustment['buy_ticket_types'])->get(['id', 'name']);

            $adjustment['buy_ticket_types_info'] = $ticketTypes->toArray();
        }

        if (isset($adjustment['get_ticket_type'])) {
            $getTicketType = TicketType::find($adjustment['get_ticket_type'], ['id', 'name']);

            if ($getTicketType) {
                $adjustment['get_ticket_type_info'] = $getTicketType->toArray();
            }
        }
    
        return $adjustment;
    }

    public function getDiscountTypeLabelAttribute()
    {
        $discountTypes = [
            '1' => __('voucher.percentage'),
            '2' => __('voucher.fixed_amount'),
            '3' => __('voucher.free_cup'),
        ];

        return $discountTypes[$this->attributes['discount_type']] ?? null;
    }

    public function getVoucherTypeLabelAttribute()
    {
        $discountTypes = [
            '1' => __('voucher.public_voucher'),
            '2' => __('voucher.user_specific_voucher'),
        ];

        return $discountTypes[$this->attributes['type']] ?? null;
    }

    public function getVoucherTypeAttribute()
    {
        return $this->attributes['type'] ?? null;
    }

    public function getApplicableToLabelAttribute(): string
    {
        $labels = [
            self::APPLICABLE_ALL     => 'All',
            self::APPLICABLE_BOOKING => 'Court Booking',
            self::APPLICABLE_PRODUCT => 'Sport Product',
        ];

        return $labels[$this->attributes['applicable_to'] ?? self::APPLICABLE_ALL] ?? 'All';
    }

    /**
     * Check whether this voucher is valid for the given context.
     *
     * @param  string  $context  'booking' | 'product'
     */
    public function isApplicableTo(string $context): bool
    {
        $applicableTo = $this->attributes['applicable_to'] ?? self::APPLICABLE_ALL;

        return $applicableTo === self::APPLICABLE_ALL || $applicableTo === $context;
    }
    
    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'promo_code',
        'title',
        'description',
        'image',
        'start_date',
        'expired_date',
        'discount_type',
        'discount_amount',
        'type',
        'applicable_to',
        'status',
        'usable_amount',
        'points_required',
        'min_spend',
        'min_order',
        'buy_x_get_y_adjustment',
        'total_claimable',
        'validity_days',
        'claim_per_user',
    ];

    protected static $logName = 'vouchers';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} voucher";
    }
}
