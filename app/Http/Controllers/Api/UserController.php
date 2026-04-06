<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Crypt,
    Hash,
    Http,
    Storage
};

use App\Services\{
    UserService,
};

use App\Models\{
    User,
};

use Helper;

class UserController extends Controller {

    public function __construct() {}

    /**
     * Check Phone Number
     * @sort 3.1
     * 
     * Check if phone Number exists in the system
     * 
     * @group User API
     * 
     * @bodyParam calling_code string required The calling_code to check. Example: +60
     * @bodyParam phone_number string required The phone number to check. Example: 114123232c
     * 
     */
    public function check( Request $request ) {

        return UserService::checkPhoneNumber( $request );
    }

    /**
     * 5. Request an OTP
     * @sort 4
     *
     * <strong>request_type</strong><br>
     * 1: Register<br>
     * 2: Forget Password<br>
     *
     * @group User API
     *
     * @bodyParam phone_number string required The phone_number. Example: 0123982334
     * @bodyParam calling_code string optional The calling code. ( Default +60 ) Example: +60
     * @bodyParam email string optional The email (Optional when request_type is 1). Example: john@example.com
     * @bodyParam fullname string required The fullname (Required only when request_type is 1). Example: John Doe
     * @bodyParam password string required The password (Required only when request_type is 1, min 8 characters). Example: password123
     * @bodyParam password_confirmation string required The password confirmation (Required only when request_type is 1). Example: password123
     * @bodyParam request_type integer required The request type for OTP. Example: 1
     *
     */
    public function requestOtp( Request $request ) {

        $request->validate([
            'request_type' => 'required|in:1,2',
            'calling_code' => 'nullable|string|regex:/^\+\d{1,4}$/',
        ]);

        return UserService::requestOtp( $request );
    }

    /**
     * 6. Resend an OTP
     * @sort 5
     *
     * <strong>request_type</strong><br>
     * 1: Resend<br>
     * 2: Resend Forget Password<br>
     *
     * @group User API
     *
     * @bodyParam identifier string required The temporary user ID during request OTP. Example: eyJpdiI...
     * @bodyParam request_type integer required The request type for OTP. Example: 2
     *
     */
    public function resendOtp( Request $request ) {

        $request->validate([
            'identifier' => 'required|string',
            'request_type' => 'required|in:1,2',
        ]);

        if( $request->request_type == 1 ){

            $request->merge( [
                'action' => 'resend'
            ] );

        }else{

            $request->merge( [
                'action' => 'resend_forget_password'
            ] );
        }

        return UserService::requestOtp( $request );
    }

    /**
     * Get user
     * @sort 6
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * 
     */ 
    public function getUser( Request $request ) {
        
        return UserService::getUser( $request, 0 );
    }

    /**
     * Update user
     * @sort 7
     * 
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * @bodyParam fullname string The first_name to update. Example: wick John
     * @bodyParam email string The email to update. Example: wickjohn@mail.com
     * @bodyParam date_of_birth string The date of birth to update. Example: 2022-01-01
     * @bodyParam nationality integer optional Update the user nationality. Example: 1
     * @bodyParam to_remove integer Indicate remove photo or not. Example: 1
     * @bodyParam profile_picture file The photo to update. Will create when empty
     * 
     */
    public function updateUserApi( Request $request ) {

        return UserService::updateUserApi( $request );
    }

    /**
     * Update user password
     * @sort 8
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * @bodyParam old_password string required The old password of current user. Example: 1234abcd
     * @bodyParam password string required The new password to change. Example: abcd1234
     * @bodyParam password_confirmation string required The confirm password of new password to change. Example: abcd1234
     * 
     */    
    public function updateUserPassword( Request $request ) {

        return UserService::updateUserPassword( $request );
    }

    /**
     * Forgot Password (Request Otp)
     * @sort 9
     * 
     * Request an unique identifier to reset password.
     * 
     * @group User API
     * 
     * @bodyParam phone_number string required The phone_number for login. Example: 0123982334
     * @bodyParam calling_code string required The calling_code for register. Example: +60
     * 
     * 
     */
    public function forgotPasswordOtp( Request $request ) {

        return UserService::forgotPasswordOtp( $request );
    }

    /**
     * Reset Password
     * @sort 10
     * @group User API
     * 
     * @bodyParam phone_number string required The phone_number for login. Example: 0123982334
     * @bodyParam identifier string required The unique_identifier from forgot password. Example: WLnvrJw6YYK
     * @bodyParam otp_code string The otp code to verify password reset. Example: 123456 
     * @bodyParam password string required The new password to perform password reset. Example: abcd1234
     * @bodyParam password_confirmation string required The new password confirmation to perform password reset. Example: abcd1234
     * 
     */
    public function resetPassword( Request $request ) {

        return UserService::resetPassword( $request );
    }

    /**
     * Delete Verification
     * @sort 11
     * 
     * @group User API
     * 
     * @authenticated
     * @bodyParam password string required The password to perform account delete checking. Example: abcd1234
     * 
     * 
     */ 
    public function deleteVerification( Request $request ) {
        
        return UserService::deleteVerification( $request );
    }

    /**
     * Delete Confirm
     * @sort 12
     * 
     * @group User API
     * 
     * @authenticated
     * @bodyParam password string required The password to perform account delete checking. Example: abcd1234
     * 
     * 
     */ 
    public function deleteConfirm( Request $request ) {
        
        return UserService::deleteConfirm( $request );
    }

    /**
     * Get notifications
     * 
     * <strong>is_read</strong><br>
     * 0: New<br>
     * 1: Read<br>
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * @queryParam lang string Language code for title and content. Example: en
     * @queryParam is_read integer Leave empty for all. Example: 1
     * @queryParam per_page integer Show how many record in a page. Leave blank for default (100). Example: 5
     * @queryParam notification integer required The notification ID of notification. Example: 5
     * 
    */
    public function getNotifications( Request $request ) {

        return UserService::getNotifications( $request );
    }

    /**
     * Update notification seen
     * 
     * @group User API
     * 
     * @bodyParam notification integer required The notification ID of notification. Example: 5
     * 
     */ 
    public function updateNotificationSeen( Request $request ) {

        return UserService::updateNotificationSeen( $request );
    }
    
    /**
     * 1. Send Contact Form
     *
     * <aside class="notice">Send contact us form email to admin</aside>
     *
     * @group Contact Us API
     *
     * @bodyParam first_name string required Name of the person. Example: John
     * @bodyParam last_name string required Name of the person. Example:Doe
     * @bodyParam phone_number string required The phone_number for register. Example: 0123982334
     * @bodyParam calling_code string required The calling_code for register. Example: +60
     * @bodyParam email string required Email address. Example: john@example.com
     * @bodyParam location string optional location. Example: Selangor
     * @bodyParam subject string required Subject of the message. Example: Inquiry about project
     * @bodyParam message string required Message content. Example: I would like to know more about your projects
     *
     */
    public function sendContactForm(Request $request)
    {
        return UserService::sendContactFormApi($request);
    }

    /**
     * 1. Verify Otp
     * @sort 1
     * 
     * 
     * @group User API
     * 
     * @bodyParam identifier string required The temporary user ID during request OTP. Example: eyJpdiI...
     * @bodyParam otp_code string required The otp for register. Example: 123456
     * 
     */
    public function verifyResetOtp( Request $request ) {

        return UserService::verifyResetOtp( $request );
    }

    /**
     * Email-based User Registration
     * @sort 22
     * 
     * @group User API
     * 
     * @bodyParam identifier string required The temporary user ID during request OTP. Example: eyJpdiI...
     * @bodyParam calling_code string required The calling_code to login. Example: +60
     * @bodyParam phone_number string required The phone number to login. Example: 114123232
     * @bodyParam email string required The email address for register. Example: user@example.com
     * @bodyParam otp_code string required The otp for register. Example: 123456
     * @bodyParam password string required The password for register. Example: abcd1234
     * @bodyParam password_confirmation string required The confirmation password. Example: abcd1234
     * @bodyParam fullname string required The user's full name. Example: John Doe
     * @bodyParam date_of_birth string The date of birth of user. Example: 2022-01-01
     * @bodyParam invitation_code string optional A referrer's invitation code. Example: E2
     * @bodyParam device_type integer optional The device type required with register_token. Example: 1
     * @bodyParam register_token string optional The device token to receive notification. Example: 45ab6cc6-bcaa-461e-af5d-ea402e5b93da
     *
     */
    public function registerUser( Request $request ) {

        return UserService::registerUser( $request );
    }

    /**
     * Email-based User Login
     * @sort 23
     *
     * @group User API
     *
     * @bodyParam calling_code string required The calling_code to login. Example: +60
     * @bodyParam phone_number string required The phone number to login. Example: 114123232
     * @bodyParam password string required The password for login. Example: abcd1234
     * @bodyParam device_type integer optional The device type required with register_token. Example: 1
     * @bodyParam register_token string optional The device token to receive notification. Example: 45ab6cc6-bcaa-461e-af5d-ea402e5b93da
     *
     */
    public function loginUser( Request $request ) {

    return UserService::loginUser( $request );
    }

    /**
     * Social Media Login
     * @sort 24
     *
     * Sign in with an existing social media account.
     *
     * <strong>platform</strong><br>
     * 1: Google<br>
     * 2: Facebook<br>
     * 3: Apple<br>
     *
     * <strong>device_type</strong><br>
     * 1: iOS<br>
     * 2: Android<br>
     * 3: Web<br>
     *
     * @group User API
     *
     * @bodyParam identifier string required The unique social identifier (e.g. email or social ID). Example: user@gmail.com
     * @bodyParam platform integer required The social platform. Example: 1
     * @bodyParam device_type integer required The device type. Example: 2
     * @bodyParam register_token string optional The OneSignal push notification token. Example: E2
     *
     */
    public function socialLogin( Request $request ) {

        return UserService::socialLogin( $request );
    }

    /**
     * Get User QR Code
     *
     * Returns the authenticated user's invitation QR code as a base64 PNG,
     * along with the raw invitation code and shareable invitation link.
     *
     * @group User API
     *
     * @authenticated
     *
     */
    public function getUserQr( Request $request ) {

        return UserService::getUserQr( $request );
    }

    /**
     * Get Downlines
     * @sort 13
     *
     * Returns a paginated list of the authenticated user's direct downlines (users who registered using this user's invitation code).
     *
     * @group User API
     *
     * @authenticated
     *
     * @queryParam per_page integer Number of records per page. Leave blank for default (15). Example: 10
     * @queryParam page integer Page number. Example: 1
     *
     * @response 200 {
     *   "status": "success",
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": "E2",
     *         "fullname": "Jane Doe",
     *         "calling_code": "+60",
     *         "phone_number": "121234567",
     *         "profile_picture": "https://example.com/storage/users/1/photo.jpg",
     *         "downlines_count": 3,
     *         "joined_at": "2026-01-15 10:00:00"
     *       }
     *     ],
     *     "per_page": 15,
     *     "total": 1
     *   }
     * }
     */
    public function getDownlines( Request $request ) {

        $user    = auth( 'user' )->user();
        $perPage = (int) $request->input( 'per_page', 15 );

        $downlines = User::where( 'referral_id', $user->id )
            ->where( 'status', 10 )
            ->withCount( 'downlines' )
            ->orderBy( 'created_at', 'desc' )
            ->paginate( $perPage );

        $downlines->getCollection()->transform( function ( $u ) {
            return [
                'id'              => Helper::encode( $u->id ),
                'fullname'        => $u->fullname,
                'calling_code'    => $u->calling_code,
                'phone_number'    => $u->phone_number,
                'profile_picture' => $u->profile_picture_path_new,
                'downlines_count' => $u->downlines_count,
                'joined_at'       => $u->created_at,
            ];
        } );

        return response()->json( [
            'status' => 'success',
            'data'   => $downlines,
        ] );
    }

    public function sendFriendRequest( Request $request ) {

        return UserService::sendFriendRequest( $request );
    }

    public function respondFriendRequest( Request $request ) {

        return UserService::respondFriendRequest( $request );
    }

    public function removeFriend( Request $request ) {

        return UserService::removeFriend( $request );
    }

    public function getFriends( Request $request ) {

        return UserService::getFriends( $request );
    }

    public function getFriendRequests( Request $request ) {

        return UserService::getFriendRequests( $request );
    }
}