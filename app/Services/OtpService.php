<?php

namespace App\Services;

use App\Models\{
    AdministratorNotification,
    OtpAction,
    TmpUser,
    UserWallet,
    User,
    UserNotification,
    UserNotificationUser,
    UserWalletTransaction,
    ApiLog,
};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    Artisan,
    Crypt,
    DB,
    Validator,
    Storage,
};
use Carbon\Carbon;
use Helper;

class OtpService {

    public static function allOtp($request)
    {
        $otpBase = null;

        if ($request->type == 1) {
            // OtpAction only
            $otpBase = DB::table('otp_actions')
                ->leftJoin('users', 'otp_actions.user_id', '=', 'users.id')
                ->select(
                    'otp_actions.id',
                    'otp_actions.created_at',
                    'otp_actions.otp_code',
                    'otp_actions.status',
                    'otp_actions.expire_on',
                    DB::raw('NULL as country_id'),
                    'users.phone_number',
                    DB::raw('NULL as calling_code'),
                    DB::raw('NULL as email'),
                    'otp_actions.user_id',
                    'otp_actions.action',
                    DB::raw("'otp_actions' as source")
                );
        }
        elseif ($request->type == 2) {
            // TmpUser only
            $otpBase = DB::table('tmp_users')
                ->select(
                    'id',
                    'created_at',
                    'otp_code',
                    'status',
                    'expire_on',
                    'country_id',
                    'phone_number',
                    'calling_code',
                    'email',
                    DB::raw('NULL as user_id'),
                    DB::raw('NULL as action'),
                    DB::raw("'tmp_users' as source")
                );
        }
        else {
            // Both
            $otpBase = DB::table('otp_actions')
                ->leftJoin('users', 'otp_actions.user_id', '=', 'users.id')
                ->select(
                    'otp_actions.id',
                    'otp_actions.created_at',
                    'otp_actions.otp_code',
                    'otp_actions.status',
                    'otp_actions.expire_on',
                    DB::raw('NULL as country_id'),
                    'users.phone_number',
                    DB::raw('NULL as calling_code'),
                    DB::raw('NULL as email'),
                    'otp_actions.user_id',
                    'otp_actions.action',
                    DB::raw("'otp_actions' as source")
                )
                ->unionAll(
                    DB::table('tmp_users')
                        ->select(
                            'id',
                            'created_at',
                            'otp_code',
                            'status',
                            'expire_on',
                            'country_id',
                            'phone_number',
                            'calling_code',
                            'email',
                            DB::raw('NULL as user_id'),
                            DB::raw('NULL as action'),
                            DB::raw("'tmp_users' as source")
                        )
                );
        }
    
        // wrap the union in a subquery so we can filter it
        $otpBase->orderBy( 'created_at', 'DESC' );
        $otp = DB::query()->fromSub($otpBase, 'otp');
    
        // Apply filters
        if ( !empty( $request->phone_number ) ) {
            $userInput = $request->phone_number;
        
            $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $userInput );
        
            $otp->where( function ( $query ) use ( $normalizedPhone, $userInput ) {
                $query->where( 'phone_number', 'LIKE', "%$normalizedPhone%" );
            } );
        
            $filter = true;
        }
    
        if ($request->otp) {
            $otp->where('otp_code', 'like', '%' . $request->otp . '%');
        }

        $filterObject = self::filter($request, $otp);
        $otp = $filterObject['model'];
        $filter = $filterObject['filter'];
    
        $otpCount = $otp->count();
    
        $limit = $request->length != -1 ? $request->length : $otpCount;
        $offset = $request->start;
    
        $otps = $otp->skip($offset)->take($limit)->get();
    
        $otps->transform(function ($row) {

            $row->created_at = $row->created_at 
                ? \Carbon\Carbon::parse($row->created_at)->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s')
                : null;

            $row->expire_on = $row->expire_on 
                ? \Carbon\Carbon::parse($row->expire_on)->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s')
                : null;

            if ($row->source === 'otp_actions' && $row->user_id) {
                $row->user = User::find($row->user_id);
            }
            if( $row->status == 1 && ( $row->expire_on < now()->timezone( 'Asia/Kuala_Lumpur' ) ) ){
                $row->status = 2;
            }

            return $row;
        });
    
        $data = [
            'otp_actions'      => $otps,
            'draw'             => $request->draw,
            'recordsFiltered'  => $otpCount,
            'recordsTotal'     => $filter ? $otp : $otpCount,
        ];
    
        return $data;
    }
    

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            if( $request->status == 2 ){
                $model->where( 'status', 1 );
                $model->where( 'expire_on', '<', now()->timezone( 'Asia/Kuala_Lumpur' ) );
            }else{
                $model->where( 'status', $request->status );
            }
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }
    public static function resendOtp( $request ) {

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required'],
        ] );

        $validator->setAttributeNames( [
            'id' => strtolower( Str::singular( __( 'template.onboarding_forms' ) ) ),
        ] )->validate();
    
        $expiryAt = Carbon::now()->addMinutes( 30 );
        $timestamp = time();

        \DB::beginTransaction();

        try {
            if( $request->type == 1 ){
                $updateFormOtp = OtpAction::find( $request->id );
                $contactNumber = $updateFormOtp->user->phone_number;
            }else{
                $updateFormOtp = TmpUser::find( $request->id );
                $contactNumber = $updateFormOtp->phone_number;
            }

            // send otp
            $cleanedPhoneNumber = Helper::cleanPhoneNumber( '+60' . $contactNumber );
            
            self::sendSMS( $cleanedPhoneNumber, $updateFormOtp->otp_code, '' );

            \DB::commit();

            return response()->json( [
                'message' => 'Resend OTP Success',
                'message_key' => 'resend_otp_success',
                'data' => [
                    'otp_code' => '#DEBUG - ' . $updateFormOtp->otp_code,
                    'unique_identifier' => Crypt::encryptString( $updateFormOtp->id ),
                ]
            ] );

        } catch ( \Throwable $th ) {

            \DB::rollBack();
            abort( 500, $th->getMessage() . ' in line: ' . $th->getLine() );
        }
    }

    private static function sendSMS( $mobile, $otp, $message = '' ) {

        $url = config( 'services.sms.sms_url' );
        $builtMessage = 'Your One Time Password (OTP) is '.$otp.'. This OTP expires in 30 minutes.';
        $encodedMessage = rawurlencode($builtMessage);

        $request = array(
            'un' => config( 'services.sms.username' ),
            'pwd' => config( 'services.sms.password' ),
            'dstno' => $mobile,
            'msg' => $encodedMessage,
            'type' => 1,
            'agreedterm'=> 'YES',
        );

        $sendSMS = Helper::curlGet( $url . '?' . http_build_query( $request ) );
        // $sendSMS =  Helper::curlPost( $url, json_encode($request) );

        ApiLog::create( [
            'url' => $url . '?' . http_build_query( $request ),
            'method' => 'GET',
            'raw_response' => json_encode( $sendSMS ),
        ] );

    }
}