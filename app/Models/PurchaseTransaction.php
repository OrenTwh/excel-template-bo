<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class PurchaseTransaction extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'purchase_id',
        'account_id',
        'reference',
        'paid_amount',
        'paid_by',
        'status',

    ];
    
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function account()
    {
        return $this->belongsTo(ExpensesAccount::class, 'account_id');
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'name', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'purchase_id',
        'account_id',
        'reference',
        'paid_amount',
        'paid_by',
        'status',
    ];

    protected static $logName = 'purchase_transactions';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} purchase_transaction";
    }
}
