<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class PropertyPayment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'property_id',
        'property_unit_id',
        'user_id',
        'fullname',
        'email',
        'phone_number',
        'calling_code',
        'customer_name',
        'customer_email',
        'customer_phone',
        'payment_date',
        'payment_method',
        'transaction_reference',
        'remarks',
        'checkout_id',
        'checkout_url',
        'transaction_id',
        'layout_version',
        'redirect_url',
        'notify_url',
        'order_no',
        'order_title',
        'payment_attempt',
        'order_detail',
        'amount',
        'currency',
        'transaction_type',
        'status',
        'payment_gateway',
        'payment_gateway_ref',
        'payment_gateway_response',
    ];

    public static function transactionTypeOptions(): array {
        return [
            1 => 'Booking Payment',
            2 => 'Rental Payment',
            3 => 'Short-Rental Payment',
        ];
    }

    public static function paymentMethodOptions(): array {
        return [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'cheque' => 'Cheque',
            'online_payment' => 'Online Payment',
        ];
    }

    public static function statusOptions(): array {
        return [
            1 => 'Pending Payment',
            10 => 'Success',
            20 => 'Failed',
        ];
    }

    public function user() {
        return $this->belongsTo( User::class, 'user_id' );
    }

    public function property() {
        return $this->belongsTo( Property::class, 'property_id' );
    }

    public function propertyUnit() {
        return $this->belongsTo( PropertyUnit::class, 'property_unit_id' );
    }

    public function propertyPaymentProperties() {
        return $this->hasMany( PropertyPaymentProperty::class, 'property_payment_id' );
    }

    public function getTransactionTypeAttribute() {
        $types = self::transactionTypeOptions();
        return $types[$this->attributes['transaction_type']] ?? 'Unknown';
    }

    public function getStatusLabelAttribute() {
        $statuses = self::statusOptions();
        return $statuses[$this->attributes['status']] ?? 'Unknown';
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getFormattedAmountAttribute() {
        return number_format($this->attributes['amount'], 2);
    }

    public function getBlockAttribute() {
        return $this->property ? $this->property->block : null;
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'property_id',
        'property_unit_id',
        'user_id',
        'fullname',
        'email',
        'phone_number',
        'calling_code',
        'customer_name',
        'customer_email',
        'customer_phone',
        'payment_date',
        'payment_method',
        'transaction_reference',
        'remarks',
        'checkout_id',
        'checkout_url',
        'transaction_id',
        'layout_version',
        'redirect_url',
        'notify_url',
        'order_no',
        'order_title',
        'payment_attempt',
        'order_detail',
        'amount',
        'currency',
        'transaction_type',
        'status',
        'payment_gateway',
        'payment_gateway_ref',
        'payment_gateway_response',
    ];

    protected static $logName = 'property_payments';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} property payment";
    }
}