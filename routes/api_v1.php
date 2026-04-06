<?php

use App\Http\Controllers\Api\{
    UserController,
    MailContentController,
    WalletController,
    MenuController,
    CheckinController,
    ApiRequestController,
    BannerController,
    ExclusiveDealController,
    AnnouncementController,
    PointsController,
    CMSController,
    SportController,
    VenueController,
    CourtController,
    CourtCalendarController,
    CourtBookingController,
    VoucherController,
    FeaturedSportController,
    PopularArenaController,
    SportProductController,
    SportProductPaymentController,
    CourtBookingPaymentController,
};

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Start Public route */

Route::prefix( 'contact-us' )->group( function() {
    Route::post( '/', [ UserController::class, 'sendContactForm' ] );
} );

// Phone Number based authentication
Route::post( 'otp', [ UserController::class, 'requestOtp' ] );
Route::post( 'otp/resend', [ UserController::class, 'resendOtp' ] );

Route::prefix( 'users' )->group( function() {
    // Phone Number based authentication
    Route::post( 'register', [ UserController::class, 'registerUser' ] );
    Route::post( 'login', [ UserController::class, 'loginUser' ] );

    Route::post( 'check', [ UserController::class, 'check' ] );
    Route::post( 'reset-password', [ UserController::class, 'resetPassword' ] );
    Route::post( 'verify-otp', [ UserController::class, 'verifyResetOtp' ] );

    // Social media authentication
    Route::post( 'social-login', [ UserController::class, 'socialLogin' ] );
} );

Route::prefix( 'banners' )->group( function() {
    Route::get( '/', [ BannerController::class, 'getBanners' ] );
    Route::any( 'details', [ BannerController::class, 'oneBanner' ] );
} );

Route::prefix( 'exclusive-deals' )->group( function() {
    Route::get( '/', [ ExclusiveDealController::class, 'getExclusiveDeals' ] );
    Route::any( 'details', [ ExclusiveDealController::class, 'oneExclusiveDeal' ] );
} );

Route::prefix( 'featured-sports' )->group( function() {
    Route::get( '/', [ FeaturedSportController::class, 'getFeaturedSports' ] );
} );

Route::prefix( 'popular-arenas' )->group( function() {
    Route::get( '/', [ PopularArenaController::class, 'getPopularArenas' ] );
} );

Route::prefix( 'sports' )->group( function() {
    Route::get( '/', [ SportController::class, 'getSports' ] );
    Route::get( 'details', [ SportController::class, 'oneSport' ] );
} );

Route::prefix( 'venues' )->group( function() {
    Route::get( '/', [ VenueController::class, 'getVenues' ] );
    Route::get( 'details', [ VenueController::class, 'oneVenue' ] );
    Route::get( 'sports', [ VenueController::class, 'getVenueSports' ] );
} );

Route::prefix( 'courts' )->group( function() {
    Route::get( '/', [ CourtController::class, 'getCourts' ] );
    Route::get( 'details', [ CourtController::class, 'oneCourt' ] );
    Route::get( 'available', [ CourtController::class, 'getAvailableCourts' ] );
    Route::get( 'available-dates', [ CourtController::class, 'getAvailableDates' ] );
    Route::get( 'available-times', [ CourtController::class, 'getAvailableTimes' ] );
    Route::get( 'calendar', [ CourtCalendarController::class, 'getSlots' ] );
    Route::get( 'calendar/availability', [ CourtCalendarController::class, 'getAvailability' ] );
} );

Route::prefix( 'events' )->group( function() {
    Route::get( '/', [ CourtCalendarController::class, 'getEvents' ] );
    Route::get( 'details', [ CourtCalendarController::class, 'getEventDetails' ] );
} );

Route::prefix( 'activities' )->group( function() {
    Route::get( '/',       [ CourtCalendarController::class, 'getUserActivities' ] );
    Route::get( 'details', [ CourtCalendarController::class, 'getUserActivityDetails' ] );
} );

Route::prefix( 'sport-products' )->group( function() {
    Route::get( 'categories',        [ SportProductController::class, 'getCategories' ] );
    Route::get( 'categories/details',[ SportProductController::class, 'getCategoryDetails' ] );
    Route::get( '/',                 [ SportProductController::class, 'getProducts' ] );
    Route::get( 'details',           [ SportProductController::class, 'getProductDetails' ] );
} );

Route::prefix( 'payment/ipay88/court-booking' )->group( function() {
    Route::get( '{id}',              [ CourtBookingPaymentController::class, 'paymentPage'     ] );
    Route::get( '{id}/mock',         [ CourtBookingPaymentController::class, 'mockPaymentPage' ] );
    Route::get( '{id}/mock/success', [ CourtBookingPaymentController::class, 'mockSuccess'     ] );
    Route::get( '{id}/mock/cancel',  [ CourtBookingPaymentController::class, 'mockCancel'      ] );
    Route::post( 'response',         [ CourtBookingPaymentController::class, 'paymentResponse' ] );
    Route::post( 'callback',         [ CourtBookingPaymentController::class, 'paymentCallback' ] );
} );

Route::prefix( 'payment/ipay88/sport-product' )->group( function() {
    Route::get( '{id}',                [ SportProductPaymentController::class, 'paymentPage'      ] );
    Route::get( '{id}/mock',           [ SportProductPaymentController::class, 'mockPaymentPage'  ] );
    Route::get( '{id}/mock/success',   [ SportProductPaymentController::class, 'mockSuccess'      ] );
    Route::get( '{id}/mock/cancel',    [ SportProductPaymentController::class, 'mockCancel'       ] );
    Route::post( 'response',           [ SportProductPaymentController::class, 'paymentResponse'  ] );
    Route::post( 'callback',           [ SportProductPaymentController::class, 'paymentCallback'  ] );
} );

Route::prefix( 'version' )->group( function() {
    Route::get('/', [UserController::class, 'checkVersion']);
    Route::get('v2', [UserController::class, 'checkVersionV2']);
} );

/* End Public route */

/* Start Protected route */

Route::middleware( 'auth:user' )->group( function() {

    Route::prefix( 'wallets' )->group( function() {
        Route::get( '/', [ WalletController::class, 'getWallet' ] );
        Route::get( 'transactions', [ WalletController::class, 'getWalletTransactions' ] );
        Route::post( 'topup', [ WalletController::class, 'topup' ] );
        Route::get( 'points', [ WalletController::class, 'getPointsHistories' ] );
    } );

    Route::prefix( 'users' )->group( function() {
        Route::get( '/', [ UserController::class, 'getUser' ] );
        Route::post( 'delete-verification', [ UserController::class, 'deleteVerification' ] );
        Route::post( 'delete-confirm', [ UserController::class, 'deleteConfirm' ] );
        Route::post( '/update', [ UserController::class, 'updateUserApi' ] );
        Route::post( '/update-password', [ UserController::class, 'updateUserPassword' ] );

        Route::get( 'notifications', [ UserController::class, 'getNotifications' ] );
        Route::post( 'notification', [ UserController::class, 'updateNotificationSeen' ] );

        Route::get( 'qr',        [ UserController::class, 'getUserQr' ] );
        Route::get( 'downlines', [ UserController::class, 'getDownlines' ] );

        Route::prefix( 'vouchers' )->group( function() {
            Route::post( 'gift', [ VoucherController::class, 'giftVoucher' ] );
        } );

    } );

    Route::prefix( 'court-bookings' )->group( function() {
        Route::get( '/',       [ CourtBookingController::class, 'getMyBookings' ] );
        Route::get( 'details', [ CourtBookingController::class, 'getOneBooking' ] );
        Route::get( 'receipt', [ CourtBookingController::class, 'getReceipt' ] );
        Route::post( 'create', [ CourtBookingController::class, 'createBooking' ] );
        Route::post( 'cancel', [ CourtBookingController::class, 'cancelBooking' ] );
    } );

    Route::prefix( 'events' )->group( function() {
        Route::post( 'join',  [ CourtCalendarController::class, 'joinEvent' ] );
        Route::post( 'leave', [ CourtCalendarController::class, 'leaveEvent' ] );
        Route::get( 'my',     [ CourtCalendarController::class, 'getMyEvents' ] );
    } );

    Route::prefix( 'activities' )->group( function() {
        Route::post( 'create', [ CourtCalendarController::class, 'createUserActivity' ] );
        Route::post( 'cancel', [ CourtCalendarController::class, 'cancelUserActivity' ] );
        Route::get( 'my',      [ CourtCalendarController::class, 'getMyUserActivities' ] );
        // join/leave reuse the event endpoints (same logic, same table)
        Route::post( 'join',  [ CourtCalendarController::class, 'joinEvent' ] );
        Route::post( 'leave', [ CourtCalendarController::class, 'leaveEvent' ] );
    } );

    Route::prefix( 'sport-products' )->group( function() {
        Route::get(    'cart',          [ SportProductController::class, 'getCart' ] );
        Route::post(   'cart/add',      [ SportProductController::class, 'addToCart' ] );
        Route::post(   'cart/update',   [ SportProductController::class, 'updateCartItem' ] );
        Route::delete( 'cart/remove',   [ SportProductController::class, 'removeFromCart' ] );
        Route::delete( 'cart/clear',    [ SportProductController::class, 'clearCart' ] );
        Route::post(   'cart/voucher',  [ SportProductController::class, 'applyVoucher' ] );
        Route::delete( 'cart/voucher',  [ SportProductController::class, 'removeVoucher' ] );

        Route::get(  'orders',         [ SportProductController::class, 'getMyOrders' ] );
        Route::get(  'orders/details', [ SportProductController::class, 'getOrderDetails' ] );
        Route::post( 'orders/create',  [ SportProductController::class, 'createOrder' ] );
        Route::post( 'orders/cancel',  [ SportProductController::class, 'cancelOrder' ] );
    } );

});
