<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class CheckinReward extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'consecutive_days',
        'reward_type',
        'reward_value',
        'validity_days',
        'voucher_id',
        'status',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getRewardTypeLabelAttribute()
    {
        $rewardTypes = [
            '1' => __('checkin_reward.points'),
            '2' => __('checkin_reward.voucher'),
        ];

        return $rewardTypes[$this->attributes['reward_type']] ?? null;
    }

    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'consecutive_days',
        'reward_type',
        'reward_value',
        'validity_days',
        'voucher_id',
        'status',
    ];

    protected static $logName = 'checkin_rewards';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} checkin reward";
    }
}
