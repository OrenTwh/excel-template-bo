<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class Expense extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'expenses_account_id',
        'expenses_category_id',
        'causer_id',
        'amount',
        'original_amount',
        'final_amount',
        'attachment',
        'status',
        'reference',
        'remarks',
        'expenses_date',
        'title',
    ];

    public function expensesAccount()
    {
        return $this->belongsTo(ExpenseAccount::class, 'expenses_account_id');
    }

    public function expensesCategory()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expenses_category_id');
    }

    public function getAttachmentPathAttribute() {
        return $this->attributes['attachment'] ? asset( 'storage/'.$this->attributes['attachment'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'name', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'expenses_account_id',
        'expenses_category_id',
        'causer_id',
        'amount',
        'original_amount',
        'final_amount',
        'attachment',
        'status',
        'reference',
        'remarks',
        'expenses_date',
        'title',
    ];

    protected static $logName = 'expenses';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} expense";
    }
}
