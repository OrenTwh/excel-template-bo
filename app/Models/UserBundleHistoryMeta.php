<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class UserBundleHistoryMeta extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $table = 'user_bundle_histories_metas';

    protected $fillable = [
        'user_bundle_history_id',
        'product_id',
        'froyo_id',
        'syrup_id',
        'topping_id',
        'bundle_selections',
        'status',
    ];

    protected $hidden = [
        'secret_code'
    ];

    public function userBundleHistory()
    {
        return $this->belongsTo(UserBundleHistory::class, 'user_bundle_history_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function froyo()
    {
        return $this->belongsTo(Froyo::class, 'froyo_id');
    }

    public function syrup()
    {
        return $this->belongsTo(Syrup::class, 'syrup_id');
    }

    public function topping()
    {
        return $this->belongsTo(Topping::class, 'topping_id');
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getUsedAtDateOnlyAttribute()
    {
        return $this->attributes['used_at'] ? $this->attributes['used_at']->format('Y-m-d') : null;
    }

    public function getRedeemFromLabelAttribute()
    {
        $rewardTypes = [
            '1' => __('user.checkin_rewards'),
            '2' => __('user.points_exchange'),
        ];

        return $rewardTypes[$this->attributes['redeem_from']] ?? null;
    }

    public function getBundleStatusLabelAttribute()
    {

        $statuses = [
            10 => __('bundle.active'),
            20 => __('bundle.used'),
            21 => __('bundle.expired'),
        ];

        return $statuses[$this->attributes['status']] ?? null;
    }

    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_bundle_history_id',
        'product_id',
        'froyo_id',
        'syrup_id',
        'topping_id',
        'bundle_selections',
        'status',
    ];

    protected static $logName = 'user_bundle_history_meta';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} user bundle history meta";
    }
}
