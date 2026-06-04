<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, LogsActivity, HasApiTokens;

    protected $hidden = ['password'];

    protected $appends = ['is_profile_complete'];

    protected $casts = [
        'nationality' => 'integer',
    ];

    protected $fillable = [
        'username',
        'fullname',
        'email',
        'email_verified_at',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'calling_code',
        'status',
        'phone_number',
        'account_type',
        'date_of_birth',
        'check_in_streak',
        'total_check_in',        
        'referral_id',
        'invitation_code',
        'referral_structure',
        'profile_picture',
        'first_name',
        'last_name',
        'is_social_account',
        'platform',
        'nationality',
        'identification_number',
    ];

    public function fxCustomers()
    {
        return $this->hasMany(FxCustomer::class, 'user_id');
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class, 'user_id');
    }

    public function referral() {
        return $this->hasOne( User::class, 'id', 'referral_id' );
    }

    public function downlines() {
        return $this->hasMany( User::class, 'referral_id', 'id' );
    }

    public function sentFriendRequests() {
        return $this->hasMany( UserFriend::class, 'user_id' );
    }

    public function receivedFriendRequests() {
        return $this->hasMany( UserFriend::class, 'friend_id' );
    }

    public function friends() {
        return $this->hasMany( UserFriend::class, 'user_id' )->where( 'status', UserFriend::STATUS_ACCEPTED )
            ->orWhere( function( $q ) {
                $q->where( 'friend_id', $this->id )->where( 'status', UserFriend::STATUS_ACCEPTED );
            } );
    }

    public function getProfilePicturePathNewAttribute() {
        return $this->attributes['profile_picture'] ? asset( 'storage/' . $this->attributes['profile_picture'] ) : asset( 'admin/images/profile_image.png' ) . Helper::assetVersion();
    }

    public function groups() {
        return $this->hasManyThrough( User::class, UserStructure::class, 'referral_id', 'id', 'id', 'user_id' );
    }

    public function uplines() {
        return $this->hasManyThrough( User::class, UserStructure::class, 'user_id', 'id', 'id', 'referral_id' )
            ->orderBy( 'level', 'ASC' );
    }

    public function nationalityInfo()
    {
        return $this->belongsTo(Nationality::class, 'nationality');
    }

    public function getSocialPlatformAttribute() {

        $user = UserSocial::where( 'user_id', $this->attributes['id'] )->first();

        return $user ? $user->platform : 0;
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getIsProfileCompleteAttribute() {
        return !empty( $this->attributes['date_of_birth'] )
            && !empty( $this->attributes['email'] )
            && !empty( $this->attributes['phone_number'] )
            && !empty( $this->attributes['fullname'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'username',
        'fullname',
        'email',
        'email_verified_at',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'calling_code',
        'status',
        'phone_number',
        'account_type',
        'date_of_birth',
        'check_in_streak',
        'total_check_in',        
        'referral_id',
        'invitation_code',
        'referral_structure',
        'profile_picture',
        'first_name',
        'last_name',
        'is_social_account',
        'platform',
        'nationality',
        'identification_number',
    ];

    protected static $logName = 'users';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} user";
    }
}
