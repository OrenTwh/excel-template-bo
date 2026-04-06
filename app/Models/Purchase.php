<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class Purchase extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'warehouse_id',
        'supplier_id',
        'causer_id',
        'purchase_date',
        'amount',
        'original_amount',
        'paid_amount',
        'final_amount',
        'attachment',
        'remarks',
        'reference',
        'tax_type',
        'status',
        'order_tax',
        'order_discount',
        'shipping_cost'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseMetas()
    {
        return $this->hasMany(PurchaseMeta::class, 'purchase_id');
    }

    public function purchaseTransactions()
    {
        return $this->hasMany(PurchaseTransaction::class, 'purchase_id');
    }

    public function admin()
    {
        return $this->belongsTo(Administrator::class, 'causer_id');
    }

    public function getAttachmentPathAttribute() {
        return $this->attributes['attachment'] ? asset( 'storage/'.$this->attributes['attachment'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getDueAmountAttribute() {
        return Helper::numberFormatV2(( $this->attributes['paid_amount'] && $this->attributes['final_amount'] > 0 ) ? $this->attributes['final_amount'] - $this->attributes['paid_amount'] : 0, 2 );
    }
    
    public function getPaymentStatusAttribute() {

        if( $this->attributes['paid_amount'] && $this->attributes['final_amount'] > 0 ) {
            if ( $this->attributes['final_amount'] - $this->attributes['paid_amount'] > 0 ) {
                return __( 'datatables.due' );
            }else{
                return __( 'datatables.paid' );
            }
        }else{
            return __( 'datatables.due' );
        }
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'name', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'warehouse_id',
        'supplier_id',
        'causer_id',
        'purchase_date',
        'amount',
        'original_amount',
        'paid_amount',
        'final_amount',
        'attachment',
        'remarks',
        'reference',
        'tax_type',
        'status',
        'order_tax',
        'order_discount',
        'shipping_cost'
    ];

    protected static $logName = 'purchases';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} purchase";
    }
}
