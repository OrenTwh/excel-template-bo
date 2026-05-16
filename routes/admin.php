<?php

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Http\Controllers\Admin\{
    BoDashboardController,
    FxController,
    AdministratorController,
    AnnouncementController,
    DateClosureController,
    RoleController,
    AuditController,
    CoreController,
    DashboardController,
    EmployeeController,
    FileController,
    ModuleController,
    SettingController,
    UserController,
    WalletController,
    WalletTransactionController,
    VoucherController,
    UserVoucherController,
    VoucherUsageController,
    UserCheckinController,
    CMSController,
    BannerController,
    ExclusiveDealController,

    // new
    SportController,
    SportsTagController,
    CourtController,
    CourtBookingController,
    CourtCalendarController,
    LocationController,
    BlogController,
    OtpController,
    VenueController,
    UserFriendController,
    FeaturedSportController,
    PopularArenaController,
    SportProductCategoryController,
    SportProductController,
    SportProductOrderController,
    UtilsController,
};

use App\Models\{
    ApiLog,
};

use App\Helpers\Helper;

use Carbon\Carbon;

Route::prefix( config( 'services.url.admin_path' ) )->group( function() {

    // Protected Route
    Route::group( [ 'middleware' => [ 'auth:admin' ] ], function() {

        Route::get( 'setup', [ SettingController::class, 'firstSetup' ] )->name( 'admin.first_setup' );
        Route::post( 'settings/setup-mfa', [ SettingController::class, 'setupMFA' ] )->name( 'admin.setupMFA' );
        Route::get( 'verify', [ AdministratorController::class, 'verify' ] )->name( 'admin.verify' );
        Route::post( 'verify-code', [ AdministratorController::class, 'verifyCode' ] )->name( 'admin.verifyCode' );

        Route::post( 'signout', [ AdministratorController::class, 'logout' ] )->name( 'admin.signout' );

        Route::group( [ 'middleware' => [ 'checkAdminIsMFA', 'checkMFA' ] ], function() {

            Route::prefix( 'core' )->group( function() {
                Route::post( 'get-notification-list', [ CoreController::class, 'getNotificationList' ] )->name( 'admin.core.getNotificationList' );
                Route::post( 'seen-notification', [ CoreController::class, 'seenNotification' ] )->name( 'admin.core.seenNotification' );
            } );

            Route::get('/', function() {
                return redirect()->route('bo.index');
            })->name('admin.home');

            // ── Docs ─────────────────────────────────────────────────────────────
            Route::get( 'docs', function() { return view('docs.index'); } )->name( 'docs.index' );
            Route::get( 'docs-cn', function() { return view('docs_cn.index'); } )->name( 'docs_cn.index' );
            Route::get( 'docs-qa', function() { return view('docs_qa.index'); } )->name( 'docs_qa.index' );
            // ────────────────────────────────────────────────────────────────────

            // ── FX Spreadsheet ───────────────────────────────────────────────────
            Route::prefix( 'fx' )->group( function() {
                Route::get(  '/',                              [ FxController::class, 'index'                  ] )->name( 'fx.index' );

                // Sheet data
                Route::get(   'master',                        [ FxController::class, 'master'                 ] )->name( 'fx.master' );
                Route::patch( 'master-daily',                  [ FxController::class, 'masterDailyUpdate'      ] )->name( 'fx.master_daily.update' );
                Route::get(  'detail',                         [ FxController::class, 'detail'                 ] )->name( 'fx.detail' );
                Route::get(  'customer/{id}',                  [ FxController::class, 'customer'               ] )->name( 'fx.customer' );

                // Customers
                Route::get(  'customers',                      [ FxController::class, 'customerList'           ] )->name( 'fx.customers' );
                Route::post( 'customers',                      [ FxController::class, 'customerStore'          ] )->name( 'fx.customers.store' );
                Route::put(    'customers/{id}',               [ FxController::class, 'customerUpdate'          ] )->name( 'fx.customers.update' );
                Route::post( 'customers/{id}/toggle-today',    [ FxController::class, 'customerToggleCheckToday'] )->name( 'fx.customers.toggle' );
                Route::delete( 'customers/{id}',               [ FxController::class, 'customerDelete'         ] )->name( 'fx.customers.delete' );

                // Transactions
                Route::post( 'transactions',                   [ FxController::class, 'transactionStore'       ] )->name( 'fx.transactions.store' );
                Route::put(  'transactions/{id}',              [ FxController::class, 'transactionUpdate'      ] )->name( 'fx.transactions.update' );
                Route::delete( 'transactions/{id}',            [ FxController::class, 'transactionDelete'      ] )->name( 'fx.transactions.delete' );

                // Arrangements
                Route::post( 'arrangements',                   [ FxController::class, 'arrangementStore'       ] )->name( 'fx.arrangements.store' );
                Route::put(  'arrangements/{id}',              [ FxController::class, 'arrangementUpdate'      ] )->name( 'fx.arrangements.update' );
                Route::delete( 'arrangements/{id}',            [ FxController::class, 'arrangementDelete'      ] )->name( 'fx.arrangements.delete' );
            } );
            // ────────────────────────────────────────────────────────────────────

            // ── BO Simplified Dashboard ──────────────────────────────────────────
            Route::prefix( 'bo' )->group( function() {
                Route::get(  '/',                          [ BoDashboardController::class, 'index'                ] )->name( 'bo.index' );
                Route::get(  'announcement',               [ BoDashboardController::class, 'announcement'         ] )->name( 'bo.announcement' );

                // Transactions
                Route::get(  'transactions',               [ BoDashboardController::class, 'transactions'         ] )->name( 'bo.transactions' );
                Route::get(  'transactions/export',        [ BoDashboardController::class, 'transactionExport'    ] )->name( 'bo.transactions.export' );

                // Banks
                Route::get(  'banks',                      [ BoDashboardController::class, 'banks'                ] )->name( 'bo.banks' );
                Route::post( 'banks',                      [ BoDashboardController::class, 'bankStore'            ] )->name( 'bo.banks.store' );
                Route::put(  'banks/{id}',                 [ BoDashboardController::class, 'bankUpdate'           ] )->name( 'bo.banks.update' );
                Route::put(  'banks/{id}/amount',          [ BoDashboardController::class, 'bankUpdateAmount'     ] )->name( 'bo.banks.amount' );
                Route::get(  'banks/{id}/history',         [ BoDashboardController::class, 'bankHistory'          ] )->name( 'bo.banks.history' );

                // Bank Transactions
                Route::get(  'bank-transactions',          [ BoDashboardController::class, 'bankTransactions'     ] )->name( 'bo.bank_transactions' );
                Route::post( 'bank-transactions',          [ BoDashboardController::class, 'bankTransactionStore' ] )->name( 'bo.bank_transactions.store' );
                Route::put(  'bank-transactions/{id}',     [ BoDashboardController::class, 'bankTransactionUpdate'] )->name( 'bo.bank_transactions.update' );
                Route::delete( 'bank-transactions/{id}',   [ BoDashboardController::class, 'bankTransactionDelete'] )->name( 'bo.bank_transactions.delete' );

                // Cashflow / Reports
                Route::get(  'cashflow',                   [ BoDashboardController::class, 'cashflow'             ] )->name( 'bo.cashflow' );
                Route::get(  'cashflow/bank',              [ BoDashboardController::class, 'cashflowBank'         ] )->name( 'bo.cashflow.bank' );
                Route::get(  'cashflow/staff',             [ BoDashboardController::class, 'cashflowStaff'        ] )->name( 'bo.cashflow.staff' );
                Route::get(  'cashflow/activity',          [ BoDashboardController::class, 'cashflowActivity'     ] )->name( 'bo.cashflow.activity' );

                // Customers
                Route::get(  'customers',                  [ BoDashboardController::class, 'customers'            ] )->name( 'bo.customers' );
                Route::post( 'customers',                  [ BoDashboardController::class, 'customerStore'        ] )->name( 'bo.customers.store' );
                Route::post( 'customers/transactions',     [ BoDashboardController::class, 'customerTxStore'      ] )->name( 'bo.customer_tx.store' );
                Route::put(  'customers/transactions/{id}',[ BoDashboardController::class, 'customerTxUpdate'     ] )->name( 'bo.customer_tx.update' );
                Route::get(    'customers/{id}',             [ BoDashboardController::class, 'customer'             ] )->name( 'bo.customer' );
                Route::put(    'customers/{id}',             [ BoDashboardController::class, 'customerUpdate'        ] )->name( 'bo.customers.update' );
                Route::delete( 'customers/{id}',             [ BoDashboardController::class, 'customerDelete'        ] )->name( 'bo.customers.delete' );

                // Roles
                Route::get(  'roles',                      [ BoDashboardController::class, 'roles'                ] )->name( 'bo.roles' );

                // Admins
                Route::get(  'admins',                     [ BoDashboardController::class, 'admins'               ] )->name( 'bo.admins' );
                Route::post( 'admins',                     [ BoDashboardController::class, 'adminStore'           ] )->name( 'bo.admins.store' );
                Route::put(  'admins/{id}',                [ BoDashboardController::class, 'adminUpdate'          ] )->name( 'bo.admins.update' );

                // Menu / settings
                Route::post( 'update-password',            [ BoDashboardController::class, 'updatePassword'       ] )->name( 'bo.update_password' );
                Route::post( 'logout',                     [ BoDashboardController::class, 'logout'               ] )->name( 'bo.logout' );
            } );
            // ────────────────────────────────────────────────────────────────────

            Route::post( 'file/upload', [ FileController::class, 'upload' ] )->withoutMiddleware( [\App\Http\Middleware\VerifyCsrfToken::class] )->name( 'admin.file.upload' );

            Route::prefix( 'dashboard' )->group( function() {
                Route::get( '/', [ DashboardController::class, 'index' ] )->name( 'admin.dashboard' );

                Route::post( '/', [ DashboardController::class, 'getDashboardData' ] )->name( 'admin.dashboard.getDashboardData' );
            } );

            Route::prefix( 'administrators' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view administrators' ] ], function() {
                    Route::get( '/', [ AdministratorController::class, 'index' ] )->name( 'admin.module_parent.administrator.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add administrators' ] ], function() {
                    Route::get( 'add', [ AdministratorController::class, 'add' ] )->name( 'admin.administrator.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit administrators' ] ], function() {
                    Route::get( 'edit', [ AdministratorController::class, 'edit' ] )->name( 'admin.administrator.edit' );
                } );

                Route::post( 'all-administrators', [ AdministratorController::class, 'allAdministrators' ] )->name( 'admin.administrator.allAdministrators' );
                Route::post( 'one-administrator', [ AdministratorController::class, 'oneAdministrator' ] )->name( 'admin.administrator.oneAdministrator' );
                Route::post( 'create-administrator', [ AdministratorController::class, 'createAdministrator' ] )->name( 'admin.administrator.createAdministrator' );
                Route::post( 'update-administrator', [ AdministratorController::class, 'updateAdministrator' ] )->name( 'admin.administrator.updateAdministrator' );

            } );

            Route::prefix( 'roles' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view roles' ] ], function() {
                    Route::get( '/', [ RoleController::class, 'index' ] )->name( 'admin.module_parent.role.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add roles' ] ], function() {
                    Route::get( 'add', [ RoleController::class, 'add' ] )->name( 'admin.role.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit roles' ] ], function() {
                    Route::get( 'edit', [ RoleController::class, 'edit' ] )->name( 'admin.role.edit' );
                } );

                Route::post( 'all-roles', [ RoleController::class, 'allRoles' ] )->name( 'admin.role.allRoles' );
                Route::post( 'one-role', [ RoleController::class, 'oneRole' ] )->name( 'admin.role.oneRole' );
                Route::post( 'create-role', [ RoleController::class, 'createRole' ] )->name( 'admin.role.createRole' );
                Route::post( 'update-role', [ RoleController::class, 'updateRole' ] )->name( 'admin.role.updateRole' );
            } );

            Route::prefix( 'modules' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view modules' ] ], function() {
                    Route::get( '/', [ ModuleController::class, 'index' ] )->name( 'admin.module_parent.module.index' );
                } );

                Route::post( 'all-modules', [ ModuleController::class, 'allModules' ] )->name( 'admin.module.allModules' );
            } );

            Route::prefix( 'audits' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view audits' ] ], function() {
                    Route::get( '/', [ AuditController::class, 'index' ] )->name( 'admin.module_parent.audit.index' );
                } );

                Route::post( 'all-audits', [ AuditController::class, 'allAudits' ] )->name( 'admin.audit.allAudits' );
                Route::post( 'one-audit', [ AuditController::class, 'oneAudit' ] )->name( 'admin.audit.oneAudit' );
            } );

            Route::prefix( 'users' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view users' ] ], function() {
                    Route::get( '/', [ UserController::class, 'index' ] )->name( 'admin.module_parent.user.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add users' ] ], function() {
                    Route::get( 'add', [ UserController::class, 'add' ] )->name( 'admin.user.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit users' ] ], function() {
                    Route::get( 'edit', [ UserController::class, 'edit' ] )->name( 'admin.user.edit' );
                } );

                Route::post( 'all-users', [ UserController::class, 'allUsers' ] )->name( 'admin.user.allUsers' );
                Route::post( 'one-user', [ UserController::class, 'oneUser' ] )->name( 'admin.user.oneUser' );
                Route::post( 'create-user', [ UserController::class, 'createUser' ] )->name( 'admin.user.createUser' );
                Route::post( 'update-user', [ UserController::class, 'updateUser' ] )->name( 'admin.user.updateUser' );
                Route::post( 'update-user-status', [ UserController::class, 'updateUserStatus' ] )->name( 'admin.user.updateUserStatus' );
                Route::post( 'user-downlines', [ UserController::class, 'userDownlines' ] )->name( 'admin.user.userDownlines' );
                Route::post( 'send-test-notification', [ UserController::class, 'sendTestNotification' ] )->name( 'admin.user.sendTestNotification' );

            } );

            Route::prefix( 'user-friends' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view user_friends' ] ], function() {
                    Route::get( '/', [ UserFriendController::class, 'index' ] )->name( 'admin.module_parent.user_friend.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add user_friends' ] ], function() {
                    Route::get( 'add', [ UserFriendController::class, 'add' ] )->name( 'admin.user_friend.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit user_friends' ] ], function() {
                    Route::get( 'edit', [ UserFriendController::class, 'edit' ] )->name( 'admin.user_friend.edit' );
                } );

                Route::post( 'all-user-friends', [ UserFriendController::class, 'allUserFriends' ] )->name( 'admin.user_friend.allUserFriends' );
                Route::post( 'one-user-friend', [ UserFriendController::class, 'oneUserFriend' ] )->name( 'admin.user_friend.oneUserFriend' );
                Route::post( 'create-user-friend', [ UserFriendController::class, 'createUserFriend' ] )->name( 'admin.user_friend.createUserFriend' );
                Route::post( 'update-user-friend', [ UserFriendController::class, 'updateUserFriend' ] )->name( 'admin.user_friend.updateUserFriend' );
                Route::post( 'update-user-friend-status', [ UserFriendController::class, 'updateUserFriendStatus' ] )->name( 'admin.user_friend.updateUserFriendStatus' );
            } );

            Route::prefix( 'wallets' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view wallets' ] ], function() {
                    Route::get( '/', [ WalletController::class, 'index' ] )->name( 'admin.module_parent.wallet.index' );
                } );

                Route::post( 'all-wallets', [ WalletController::class, 'allWallets' ] )->name( 'admin.wallet.allWallets' );
                Route::post( 'one-wallet', [ WalletController::class, 'oneWallet' ] )->name( 'admin.wallet.oneWallet' );
                Route::post( 'update-wallet', [ WalletController::class, 'updateWallet' ] )->name( 'admin.wallet.updateWallet' );
                Route::post( 'update-wallet-multiple', [ WalletController::class, 'updateWalletMultiple' ] )->name( 'admin.wallet.updateWalletMultiple' );
            } );
            
            Route::prefix( 'wallet-transactions' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view wallet_transactions' ] ], function() {
                    Route::get( '/', [ WalletTransactionController::class, 'index' ] )->name( 'admin.module_parent.wallet_transaction.index' );
                } );

                Route::post( 'all-wallet-transactions', [ WalletTransactionController::class, 'allWalletTransactions' ] )->name( 'admin.wallet_transaction.allWalletTransactions' );
            } );
            
            // new routes ( 23/12 ) 
            Route::prefix( 'vouchers' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vouchers' ] ], function() {
                    Route::get( '/', [ VoucherController::class, 'index' ] )->name( 'admin.module_parent.voucher.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vouchers' ] ], function() {
                    Route::get( 'add', [ VoucherController::class, 'add' ] )->name( 'admin.voucher.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vouchers' ] ], function() {
                    Route::get( 'edit', [ VoucherController::class, 'edit' ] )->name( 'admin.voucher.edit' );
                } );
    
                Route::post( 'all-vouchers', [ VoucherController::class, 'allVouchers' ] )->name( 'admin.voucher.allVouchers' );
                Route::post( 'one-voucher', [ VoucherController::class, 'oneVoucher' ] )->name( 'admin.voucher.oneVoucher' );
                Route::post( 'create-voucher', [ VoucherController::class, 'createVoucher' ] )->name( 'admin.voucher.createVoucher' );
                Route::post( 'update-voucher', [ VoucherController::class, 'updateVoucher' ] )->name( 'admin.voucher.updateVoucher' );
                Route::post( 'update-voucher-status', [ VoucherController::class, 'updateVoucherStatus' ] )->name( 'admin.voucher.updateVoucherStatus' );
                Route::post( 'remove-voucher-gallery-image', [ VoucherController::class, 'removeVoucherGalleryImage' ] )->name( 'admin.voucher.removeVoucherGalleryImage' );
                Route::post( 'ckeUpload', [ VoucherController::class, 'ckeUpload' ] )->name( 'admin.voucher.ckeUpload' );
            } );

            Route::prefix( 'user-checkins' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view user_checkins' ] ], function() {
                    Route::get( '/', [ UserCheckinController::class, 'index' ] )->name( 'admin.module_parent.user_checkin.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add user_checkins' ] ], function() {
                    Route::get( 'add', [ UserCheckinController::class, 'add' ] )->name( 'admin.user_checkin.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit user_checkins' ] ], function() {
                    Route::get( 'edit', [ UserCheckinController::class, 'edit' ] )->name( 'admin.user_checkin.edit' );
                } );
                Route::group( [ 'middleware' => [ 'permission:view checkin_rewards' ] ], function() {
                    Route::get( 'calendar', [ UserCheckinController::class, 'calendar' ] )->name( 'admin.user_checkin.calendar' );
                } );
    
                Route::post( 'all-user-checkins', [ UserCheckinController::class, 'allUserCheckins' ] )->name( 'admin.user_checkin.allUserCheckins' );
                Route::post( 'all-user-checkin-calendars', [ UserCheckinController::class, 'allUserCheckinCalendars' ] )->name( 'admin.user_checkin.allUserCheckinCalendars' );
                Route::post( 'one-user-checkin', [ UserCheckinController::class, 'oneUserCheckin' ] )->name( 'admin.user_checkin.oneUserCheckin' );
                Route::post( 'create-user-checkin', [ UserCheckinController::class, 'createUserCheckin' ] )->name( 'admin.user_checkin.createUserCheckin' );
                Route::post( 'update-user-checkin', [ UserCheckinController::class, 'updateUserCheckin' ] )->name( 'admin.user_checkin.updateUserCheckin' );
                Route::post( 'update-user-checkin-status', [ UserCheckinController::class, 'updateUserCheckinStatus' ] )->name( 'admin.user_checkin.updateUserCheckinStatus' );
                Route::post( 'remove-user-checkin-gallery-image', [ UserCheckinController::class, 'removeUserCheckinGalleryImage' ] )->name( 'admin.user_checkin.removeUserCheckinGalleryImage' );
                Route::post( 'ckeUpload', [ UserCheckinController::class, 'ckeUpload' ] )->name( 'admin.user_checkin.ckeUpload' );
            } );

            Route::prefix( 'checkin-rewards' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view checkin_rewards' ] ], function() {
                    Route::get( '/', [ CheckinRewardController::class, 'index' ] )->name( 'admin.module_parent.checkin_reward.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add checkin_rewards' ] ], function() {
                    Route::get( 'add', [ CheckinRewardController::class, 'add' ] )->name( 'admin.checkin_reward.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit checkin_rewards' ] ], function() {
                    Route::get( 'edit', [ CheckinRewardController::class, 'edit' ] )->name( 'admin.checkin_reward.edit' );
                } );
    
                Route::post( 'all-checkin-rewards', [ CheckinRewardController::class, 'allCheckinRewards' ] )->name( 'admin.checkin_reward.allCheckinRewards' );
                Route::post( 'one-checkin-reward', [ CheckinRewardController::class, 'oneCheckinReward' ] )->name( 'admin.checkin_reward.oneCheckinReward' );
                Route::post( 'create-checkin-reward', [ CheckinRewardController::class, 'createCheckinReward' ] )->name( 'admin.checkin_reward.createCheckinReward' );
                Route::post( 'update-checkin-reward', [ CheckinRewardController::class, 'updateCheckinReward' ] )->name( 'admin.checkin_reward.updateCheckinReward' );
                Route::post( 'update-checkin-reward-status', [ CheckinRewardController::class, 'updateCheckinRewardStatus' ] )->name( 'admin.checkin_reward.updateCheckinRewardStatus' );
                Route::post( 'remove-checkin-reward-gallery-image', [ CheckinRewardController::class, 'removeCheckinRewardGalleryImage' ] )->name( 'admin.checkin_reward.removeCheckinRewardGalleryImage' );
                Route::post( 'ckeUpload', [ CheckinRewardController::class, 'ckeUpload' ] )->name( 'admin.checkin_reward.ckeUpload' );
            } );

            Route::prefix( 'settings' )->group( function() {

                Route::group( [ 'middleware' => [ 'permission:add settings|view settings|edit settings|delete settings' ] ], function() {
                    Route::get( '/', [ SettingController::class, 'index' ] )->name( 'admin.module_parent.setting.index' );
                } );

                Route::post( 'settings', [ SettingController::class, 'settings' ] )->name( 'admin.setting.settings' );
                Route::post( 'bonus-settings', [ SettingController::class, 'bonusSettings' ] )->name( 'admin.setting.bonusSettings' );
                Route::post( 'maintenance-settings', [ SettingController::class, 'maintenanceSettings' ] )->name( 'admin.setting.maintenanceSettings' );
                Route::post( 'update-bonus-setting', [ SettingController::class, 'updateBonusSetting' ] )->name( 'admin.setting.updateBonusSetting' );
                Route::post( 'update-maintenance-setting', [ SettingController::class, 'updateMaintenanceSetting' ] )->name( 'admin.setting.updateMaintenanceSetting' );
                Route::post( 'update-app-version-setting', [ SettingController::class, 'updateAppVersionSetting' ] )->name( 'admin.setting.updateAppVersionSetting' );
            } );

            Route::prefix( 'user-vouchers' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vouchers' ] ], function() {
                    Route::get( '/', [ UserVoucherController::class, 'index' ] )->name( 'admin.module_parent.user_voucher.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vouchers' ] ], function() {
                    Route::get( 'add', [ UserVoucherController::class, 'add' ] )->name( 'admin.user_voucher.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vouchers' ] ], function() {
                    Route::get( 'edit', [ UserVoucherController::class, 'edit' ] )->name( 'admin.user_voucher.edit' );
                } );
    
                Route::post( 'all-user-vouchers', [ UserVoucherController::class, 'allUserVouchers' ] )->name( 'admin.user_voucher.allUserVouchers' );
                Route::post( 'one-user-voucher', [ UserVoucherController::class, 'oneUserVoucher' ] )->name( 'admin.user_voucher.oneUserVoucher' );
                Route::post( 'create-user-voucher', [ UserVoucherController::class, 'createUserVoucher' ] )->name( 'admin.user_voucher.createUserVoucher' );
                Route::post( 'update-user-voucher', [ UserVoucherController::class, 'updateUserVoucher' ] )->name( 'admin.user_voucher.updateUserVoucher' );
                Route::post( 'update-user-user-voucher-status', [ UserVoucherController::class, 'updateUserVoucherStatus' ] )->name( 'admin.user_voucher.updateUserVoucherStatus' );
                Route::post( 'remove-user-user-voucher-gallery-image', [ UserVoucherController::class, 'removeUserVoucherGalleryImage' ] )->name( 'admin.user_voucher.removeUserVoucherGalleryImage' );
                Route::post( 'ckeUpload', [ UserVoucherController::class, 'ckeUpload' ] )->name( 'admin.user_voucher.ckeUpload' );
            } );
            
            Route::prefix( 'announcements' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view announcements' ] ], function() {
                    Route::get( '/', [ AnnouncementController::class, 'index' ] )->name( 'admin.module_parent.announcement.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add announcements' ] ], function() {
                    Route::get( 'add', [ AnnouncementController::class, 'add' ] )->name( 'admin.announcement.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit announcements' ] ], function() {
                    Route::get( 'edit', [ AnnouncementController::class, 'edit' ] )->name( 'admin.announcement.edit' );
                } );

                Route::post( 'all-announcements', [ AnnouncementController::class, 'allAnnouncements' ] )->name( 'admin.announcement.allAnnouncements' );
                Route::post( 'one-announcement', [ AnnouncementController::class, 'oneAnnouncement' ] )->name( 'admin.announcement.oneAnnouncement' );
                Route::post( 'create-announcement', [ AnnouncementController::class, 'createAnnouncement' ] )->name( 'admin.announcement.createAnnouncement' );
                Route::post( 'update-announcement', [ AnnouncementController::class, 'updateAnnouncement' ] )->name( 'admin.announcement.updateAnnouncement' );
                Route::post( 'update-announcement-status', [ AnnouncementController::class, 'updateAnnouncementStatus' ] )->name( 'admin.announcement.updateAnnouncementStatus' );
                Route::post( 'remove-announcement-gallery-image', [ AnnouncementController::class, 'removeAnnouncementGalleryImage' ] )->name( 'admin.announcement.removeAnnouncementGalleryImage' );
                Route::post( 'ckeUpload', [ AnnouncementController::class, 'ckeUpload' ] )->name( 'admin.announcement.ckeUpload' );
            } );

            Route::prefix( 'sports' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view sports' ] ], function() {
                    Route::get( '/', [ SportController::class, 'index' ] )->name( 'admin.module_parent.sport.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add sports' ] ], function() {
                    Route::get( 'add', [ SportController::class, 'add' ] )->name( 'admin.sport.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit sports' ] ], function() {
                    Route::get( 'edit', [ SportController::class, 'edit' ] )->name( 'admin.sport.edit' );
                } );

                Route::post( 'all-sports', [ SportController::class, 'allSports' ] )->name( 'admin.sport.allSports' );
                Route::post( 'one-sport', [ SportController::class, 'oneSport' ] )->name( 'admin.sport.oneSport' );
                Route::post( 'create-sport', [ SportController::class, 'createSport' ] )->name( 'admin.sport.createSport' );
                Route::post( 'update-sport', [ SportController::class, 'updateSport' ] )->name( 'admin.sport.updateSport' );
                Route::post( 'update-sport-status', [ SportController::class, 'updateSportStatus' ] )->name( 'admin.sport.updateSportStatus' );
                Route::post( 'update-sequence', [ SportController::class, 'updateSequence' ] )->name( 'admin.sport.updateSequence' );
                Route::post( 'reorder',         [ SportController::class, 'reorder' ] )->name( 'admin.sport.reorder' );
                Route::post( 'remove-sport-gallery-image', [ SportController::class, 'removeSportGalleryImage' ] )->name( 'admin.sport.removeSportGalleryImage' );
                Route::post( 'ckeUpload', [ SportController::class, 'ckeUpload' ] )->name( 'admin.sport.ckeUpload' );
                Route::post( 'remove-icon-image', [ SportController::class, 'removeIconImage' ] )->name( 'admin.sport.removeIconImage' );
                Route::post( 'remove-image', [ SportController::class, 'removeImage' ] )->name( 'admin.sport.removeImage' );
                Route::post( 'remove-thumbnail', [ SportController::class, 'removeThumbnail' ] )->name( 'admin.sport.removeThumbnail' );
            } );

            Route::prefix( 'sports-tags' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view sports' ] ], function() {
                    Route::get( '/', [ SportsTagController::class, 'index' ] )->name( 'admin.module_parent.sports_tag.index' );
                } );

                Route::post( 'all',           [ SportsTagController::class, 'all' ] )->name( 'admin.sports_tag.all' );
                Route::post( 'all-tags',      [ SportsTagController::class, 'allSportsTags' ] )->name( 'admin.sports_tag.allSportsTags' );
                Route::post( 'one',           [ SportsTagController::class, 'oneSportsTag' ] )->name( 'admin.sports_tag.oneSportsTag' );
                Route::post( 'create',        [ SportsTagController::class, 'createSportsTag' ] )->name( 'admin.sports_tag.createSportsTag' );
                Route::post( 'update',        [ SportsTagController::class, 'updateSportsTag' ] )->name( 'admin.sports_tag.updateSportsTag' );
                Route::post( 'update-status', [ SportsTagController::class, 'updateSportsTagStatus' ] )->name( 'admin.sports_tag.updateSportsTagStatus' );
                Route::post( 'delete',        [ SportsTagController::class, 'deleteSportsTag' ] )->name( 'admin.sports_tag.deleteSportsTag' );
            } );

            Route::prefix( 'venues' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view courts' ] ], function() {
                    Route::get( '/', [ VenueController::class, 'index' ] )->name( 'admin.module_parent.venue.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add courts' ] ], function() {
                    Route::get( 'add', [ VenueController::class, 'add' ] )->name( 'admin.venue.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit courts' ] ], function() {
                    Route::get( 'edit', [ VenueController::class, 'edit' ] )->name( 'admin.venue.edit' );
                } );

                Route::post( 'all-venues', [ VenueController::class, 'allVenues' ] )->name( 'admin.venue.allVenues' );
                Route::post( 'one-venue', [ VenueController::class, 'oneVenue' ] )->name( 'admin.venue.oneVenue' );
                Route::post( 'create-venue', [ VenueController::class, 'createVenue' ] )->name( 'admin.venue.createVenue' );
                Route::post( 'update-venue', [ VenueController::class, 'updateVenue' ] )->name( 'admin.venue.updateVenue' );
                Route::post( 'update-venue-status', [ VenueController::class, 'updateVenueStatus' ] )->name( 'admin.venue.updateVenueStatus' );

                Route::group( [ 'middleware' => [ 'permission:view courts' ] ], function() {
                    Route::get( 'sports', [ VenueController::class, 'sportIndex' ] )->name( 'admin.module_parent.venue_sport.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add courts' ] ], function() {
                    Route::get( 'sports/add', [ VenueController::class, 'sportAdd' ] )->name( 'admin.venue_sport.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit courts' ] ], function() {
                    Route::get( 'sports/edit', [ VenueController::class, 'sportEdit' ] )->name( 'admin.venue_sport.edit' );
                } );

                Route::post( 'all-venue-sports', [ VenueController::class, 'allVenueSports' ] )->name( 'admin.venue.allVenueSports' );
                Route::post( 'sports/all', [ VenueController::class, 'allVenueSportsGlobal' ] )->name( 'admin.venue_sport.allVenueSportsGlobal' );
                Route::post( 'sports/one', [ VenueController::class, 'oneVenueSport' ] )->name( 'admin.venue_sport.oneVenueSport' );
                Route::post( 'add-venue-sport', [ VenueController::class, 'addVenueSport' ] )->name( 'admin.venue.addVenueSport' );
                Route::post( 'update-venue-sport', [ VenueController::class, 'updateVenueSport' ] )->name( 'admin.venue.updateVenueSport' );
                Route::post( 'update-venue-sport-status', [ VenueController::class, 'updateVenueSportStatus' ] )->name( 'admin.venue.updateVenueSportStatus' );
                Route::post( 'remove-venue-sport', [ VenueController::class, 'removeVenueSport' ] )->name( 'admin.venue.removeVenueSport' );
                Route::post( 'sports/import', [ VenueController::class, 'importVenueSports' ] )->name( 'admin.venue_sport.import' );
            } );

            Route::prefix( 'courts' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view courts' ] ], function() {
                    Route::get( '/', [ CourtController::class, 'index' ] )->name( 'admin.module_parent.court.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add courts' ] ], function() {
                    Route::get( 'add', [ CourtController::class, 'add' ] )->name( 'admin.court.add' );
                    Route::get( 'bulk-add', [ CourtController::class, 'bulkAdd' ] )->name( 'admin.court.bulkAdd' );
                    Route::post( 'bulk-create-courts', [ CourtController::class, 'bulkCreateCourts' ] )->name( 'admin.court.bulkCreateCourts' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit courts' ] ], function() {
                    Route::get( 'edit', [ CourtController::class, 'edit' ] )->name( 'admin.court.edit' );
                } );

                Route::post( 'all-courts', [ CourtController::class, 'allCourts' ] )->name( 'admin.court.allCourts' );
                Route::post( 'one-court', [ CourtController::class, 'oneCourt' ] )->name( 'admin.court.oneCourt' );
                Route::post( 'create-court', [ CourtController::class, 'createCourt' ] )->name( 'admin.court.createCourt' );
                Route::post( 'update-court', [ CourtController::class, 'updateCourt' ] )->name( 'admin.court.updateCourt' );
                Route::post( 'update-court-status', [ CourtController::class, 'updateCourtStatus' ] )->name( 'admin.court.updateCourtStatus' );
                Route::post( 'duplicate-court', [ CourtController::class, 'duplicateCourt' ] )->name( 'admin.court.duplicateCourt' );
                Route::post( 'remove-court-image', [ CourtController::class, 'removeCourtImage' ] )->name( 'admin.court.removeCourtImage' );
                Route::post( 'remove-court-gallery-image', [ CourtController::class, 'removeCourtGalleryImage' ] )->name( 'admin.court.removeCourtGalleryImage' );
                Route::post( 'ckeUpload', [ CourtController::class, 'ckeUpload' ] )->name( 'admin.court.ckeUpload' );
            } );

            Route::prefix('court-booking')->group(function () {
                Route::group( [ 'middleware' => [ 'permission:view court_bookings' ] ], function() {
                    Route::get('/',          [CourtBookingController::class, 'index'])->name('admin.module_parent.court_booking.index');
                    Route::get('/pending',  [CourtBookingController::class, 'pendingIndex'])->name('admin.module_parent.court_booking.pending');
                    Route::get('/upcoming',  [CourtBookingController::class, 'upcomingIndex'])->name('admin.module_parent.court_booking.upcoming');
                    Route::get('/complete',  [CourtBookingController::class, 'completeIndex'])->name('admin.module_parent.court_booking.complete');
                    Route::get('/suspended', [CourtBookingController::class, 'suspendedIndex'])->name('admin.module_parent.court_booking.suspended');
                    Route::get('/booking-calendar',       [CourtBookingController::class, 'bookingCalendar'])->name('admin.court_booking.booking_calendar');
                    Route::get('/availability-statistic', [CourtBookingController::class, 'availabilityStatistic'])->name('admin.court_booking.availability_statistic');
                    Route::get('/view',      [CourtBookingController::class, 'view'])->name('admin.court_booking.view');
                } );

                Route::group( [ 'middleware' => [ 'permission:add court_bookings' ] ], function() {
                    Route::get('/add', [CourtBookingController::class, 'add'])->name('admin.court_booking.add');
                } );

                Route::group( [ 'middleware' => [ 'permission:edit court_bookings' ] ], function() {
                    Route::get('/edit', [CourtBookingController::class, 'edit'])->name('admin.court_booking.edit');
                } );

                Route::post('/allCourtBookings',    [CourtBookingController::class, 'allCourtBookings'])->name('admin.court_booking.allCourtBookings');
                Route::post('/oneCourtBooking',     [CourtBookingController::class, 'oneCourtBooking'])->name('admin.court_booking.oneCourtBooking');
                Route::post('/createCourtBooking',  [CourtBookingController::class, 'createCourtBooking'])->name('admin.court_booking.createCourtBooking');
                Route::post('/updateCourtBooking',  [CourtBookingController::class, 'updateCourtBooking'])->name('admin.court_booking.updateCourtBooking');
                Route::post('/updateBookingStatus', [CourtBookingController::class, 'updateBookingStatus'])->name('admin.court_booking.updateBookingStatus');
                Route::post('/updatePaymentStatus', [CourtBookingController::class, 'updatePaymentStatus'])->name('admin.court_booking.updatePaymentStatus');
                Route::post('/cancelBooking',       [CourtBookingController::class, 'cancelBooking'])->name('admin.court_booking.cancelBooking');
                Route::post('/confirmBooking',      [CourtBookingController::class, 'confirmBooking'])->name('admin.court_booking.confirmBooking');
                Route::post('/deleteCourtBooking',  [CourtBookingController::class, 'deleteCourtBooking'])->name('admin.court_booking.deleteCourtBooking');
                Route::post('/checkAvailability',   [CourtBookingController::class, 'checkAvailability'])->name('admin.court_booking.checkAvailability');
                Route::post('/getBookingsByDate',   [CourtBookingController::class, 'getBookingsByDate'])->name('admin.court_booking.getBookingsByDate');
                Route::post('/getAvailabilityData', [CourtBookingController::class, 'getAvailabilityData'])->name('admin.court_booking.getAvailabilityData');
            });
          
            // Court Calendar Routes
            Route::prefix('court-calendar')->group(function () {
                Route::group( [ 'middleware' => [ 'permission:view court_bookings' ] ], function() {
                    Route::get('/', [CourtCalendarController::class, 'index'])->name('admin.module_parent.court_calendar.index');
                    Route::get('/user-activities', [CourtCalendarController::class, 'userActivities'])->name('admin.court_calendar.user_activities');
                });
                Route::group( [ 'middleware' => [ 'permission:add court_bookings' ] ], function() {
                    Route::get('/add', [CourtCalendarController::class, 'add'])->name('admin.court_calendar.add');
                });
                Route::group( [ 'middleware' => [ 'permission:edit court_bookings' ] ], function() {
                    Route::get('/edit', [CourtCalendarController::class, 'edit'])->name('admin.court_calendar.edit');
                });
          
                Route::post('/allCourtCalendars', [CourtCalendarController::class, 'allCourtCalendars'])->name('admin.court_calendar.allCourtCalendars');
                Route::post('/oneCourtCalendar', [CourtCalendarController::class, 'oneCourtCalendar'])->name('admin.court_calendar.oneCourtCalendar');
                Route::post('/createCourtCalendar', [CourtCalendarController::class, 'createCourtCalendar'])->name('admin.court_calendar.createCourtCalendar');
                Route::post('/updateCourtCalendar', [CourtCalendarController::class, 'updateCourtCalendar'])->name('admin.court_calendar.updateCourtCalendar');
                Route::post('/updateCalendarStatus', [CourtCalendarController::class, 'updateCalendarStatus'])->name('admin.court_calendar.updateCalendarStatus');
                Route::post('/deleteCourtCalendar', [CourtCalendarController::class, 'deleteCourtCalendar'])->name('admin.court_calendar.deleteCourtCalendar');
                Route::post('/getCourtAvailability', [CourtCalendarController::class, 'getCourtAvailability'])->name('admin.court_calendar.getCourtAvailability');
                Route::post('/bulkCreateSchedules', [CourtCalendarController::class, 'bulkCreateSchedules'])->name('admin.court_calendar.bulkCreateSchedules');
                Route::post('/getEventParticipants', [CourtCalendarController::class, 'getEventParticipants'])->name('admin.court_calendar.getEventParticipants');
                Route::get('/event-participants', [CourtCalendarController::class, 'eventParticipants'])->name('admin.court_calendar.event_participants');
                Route::post('/allEventParticipants', [CourtCalendarController::class, 'allEventParticipants'])->name('admin.court_calendar.allEventParticipants');
            });

            // Sport Product Categories
            Route::prefix( 'sport-product-categories' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view sport_products' ] ], function() {
                    Route::get( '/', [ SportProductCategoryController::class, 'index' ] )->name( 'admin.module_parent.sport_product_category.index' );
                });
                Route::group( [ 'middleware' => [ 'permission:add sport_products' ] ], function() {
                    Route::get( '/add', [ SportProductCategoryController::class, 'add' ] )->name( 'admin.sport_product_category.add' );
                });
                Route::group( [ 'middleware' => [ 'permission:edit sport_products' ] ], function() {
                    Route::get( '/edit', [ SportProductCategoryController::class, 'edit' ] )->name( 'admin.sport_product_category.edit' );
                });
                Route::post( '/all',    [ SportProductCategoryController::class, 'allCategories' ] )->name( 'admin.sport_product_category.allCategories' );
                Route::post( '/one',    [ SportProductCategoryController::class, 'oneCategory' ] )->name( 'admin.sport_product_category.oneCategory' );
                Route::post( '/create', [ SportProductCategoryController::class, 'createCategory' ] )->name( 'admin.sport_product_category.createCategory' );
                Route::post( '/update', [ SportProductCategoryController::class, 'updateCategory' ] )->name( 'admin.sport_product_category.updateCategory' );
                Route::post( '/update-status', [ SportProductCategoryController::class, 'updateCategoryStatus' ] )->name( 'admin.sport_product_category.updateCategoryStatus' );
                Route::post( '/delete', [ SportProductCategoryController::class, 'deleteCategory' ] )->name( 'admin.sport_product_category.deleteCategory' );
            });

            // Sport Products
            Route::prefix( 'sport-products' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view sport_products' ] ], function() {
                    Route::get( '/', [ SportProductController::class, 'index' ] )->name( 'admin.module_parent.sport_product.index' );
                });
                Route::group( [ 'middleware' => [ 'permission:add sport_products' ] ], function() {
                    Route::get( '/add', [ SportProductController::class, 'add' ] )->name( 'admin.sport_product.add' );
                });
                Route::group( [ 'middleware' => [ 'permission:edit sport_products' ] ], function() {
                    Route::get( '/edit', [ SportProductController::class, 'edit' ] )->name( 'admin.sport_product.edit' );
                });
                Route::post( '/all',            [ SportProductController::class, 'allProducts' ] )->name( 'admin.sport_product.allProducts' );
                Route::post( '/one',            [ SportProductController::class, 'oneProduct' ] )->name( 'admin.sport_product.oneProduct' );
                Route::post( '/create',         [ SportProductController::class, 'createProduct' ] )->name( 'admin.sport_product.createProduct' );
                Route::post( '/update',         [ SportProductController::class, 'updateProduct' ] )->name( 'admin.sport_product.updateProduct' );
                Route::post( '/update-status',  [ SportProductController::class, 'updateProductStatus' ] )->name( 'admin.sport_product.updateProductStatus' );
                Route::post( '/remove-image',   [ SportProductController::class, 'removeProductImage' ] )->name( 'admin.sport_product.removeProductImage' );
                Route::post( '/delete',         [ SportProductController::class, 'deleteProduct' ] )->name( 'admin.sport_product.deleteProduct' );
                Route::post( '/variant/create',       [ SportProductController::class, 'createVariant' ] )->name( 'admin.sport_product.createVariant' );
                Route::post( '/variant/update',       [ SportProductController::class, 'updateVariant' ] )->name( 'admin.sport_product.updateVariant' );
                Route::post( '/variant/delete',       [ SportProductController::class, 'deleteVariant' ] )->name( 'admin.sport_product.deleteVariant' );
                Route::post( '/variant/remove-image', [ SportProductController::class, 'removeVariantImage' ] )->name( 'admin.sport_product.removeVariantImage' );
                Route::post( '/stock/adjust',   [ SportProductController::class, 'adjustStock' ] )->name( 'admin.sport_product.adjustStock' );
                Route::post( '/stock/logs',     [ SportProductController::class, 'getStockLogs' ] )->name( 'admin.sport_product.getStockLogs' );
                Route::post( '/stock/get',      [ SportProductController::class, 'getVariantStock' ] )->name( 'admin.sport_product.getVariantStock' );
            });

            // Sport Product Orders
            Route::prefix( 'sport-product-orders' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view sport_products' ] ], function() {
                    Route::get( '/',     [ SportProductOrderController::class, 'index' ] )->name( 'admin.module_parent.sport_product_order.index' );
                    Route::get( '/view', [ SportProductOrderController::class, 'view'  ] )->name( 'admin.sport_product_order.view' );
                });
                Route::group( [ 'middleware' => [ 'permission:edit sport_products' ] ], function() {
                    Route::post( '/update-status', [ SportProductOrderController::class, 'updateOrderStatus' ] )->name( 'admin.sport_product_order.updateOrderStatus' );
                });
                Route::post( '/all', [ SportProductOrderController::class, 'allOrders' ] )->name( 'admin.sport_product_order.allOrders' );
                Route::post( '/one', [ SportProductOrderController::class, 'oneOrder'  ] )->name( 'admin.sport_product_order.oneOrder' );
            });

            Route::prefix( 'locations' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view locations' ] ], function() {
                    Route::get( '/', [ LocationController::class, 'index' ] )->name( 'admin.module_parent.location.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add locations' ] ], function() {
                    Route::get( 'add', [ LocationController::class, 'add' ] )->name( 'admin.location.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit locations' ] ], function() {
                    Route::get( 'edit', [ LocationController::class, 'edit' ] )->name( 'admin.location.edit' );
                } );

                Route::post( 'all-locations', [ LocationController::class, 'allLocations' ] )->name( 'admin.location.allLocations' );
                Route::post( 'one-location', [ LocationController::class, 'oneLocation' ] )->name( 'admin.location.oneLocation' );
                Route::post( 'create-location', [ LocationController::class, 'createLocation' ] )->name( 'admin.location.createLocation' );
                Route::post( 'update-location', [ LocationController::class, 'updateLocation' ] )->name( 'admin.location.updateLocation' );
                Route::post( 'update-location-status', [ LocationController::class, 'updateLocationStatus' ] )->name( 'admin.location.updateLocationStatus' );
                Route::post( 'remove-location-gallery-image', [ LocationController::class, 'removeLocationGalleryImage' ] )->name( 'admin.location.removeLocationGalleryImage' );
                Route::post( 'ckeUpload', [ LocationController::class, 'ckeUpload' ] )->name( 'admin.location.ckeUpload' );
            } );

            Route::prefix( 'banners' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view banners' ] ], function() {
                    Route::get( '/', [ BannerController::class, 'index' ] )->name( 'admin.module_parent.banner.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add banners' ] ], function() {
                    Route::get( 'add', [ BannerController::class, 'add' ] )->name( 'admin.banner.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit banners' ] ], function() {
                    Route::get( 'edit', [ BannerController::class, 'edit' ] )->name( 'admin.banner.edit' );
                } );
    
                Route::post( 'update-order', [ BannerController::class, 'updateOrder' ] )->name( 'admin.banner.updateOrder' );
                Route::post( 'all-banners', [ BannerController::class, 'allBanners' ] )->name( 'admin.banner.allBanners' );
                Route::post( 'one-banner', [ BannerController::class, 'oneBanner' ] )->name( 'admin.banner.oneBanner' );
                Route::post( 'create-banner', [ BannerController::class, 'createBanner' ] )->name( 'admin.banner.createBanner' );
                Route::post( 'update-banner', [ BannerController::class, 'updateBanner' ] )->name( 'admin.banner.updateBanner' );
                Route::post( 'delete-banner', [ BannerController::class, 'deleteBanner' ] )->name( 'admin.banner.deleteBanner' );
                Route::post( 'update-banner-status', [ BannerController::class, 'updateBannerStatus' ] )->name( 'admin.banner.updateBannerStatus' );
                Route::post( 'remove-banner-gallery-image', [ BannerController::class, 'removeBannerGalleryImage' ] )->name( 'admin.banner.removeBannerGalleryImage' );
                Route::post( 'ckeUpload', [ BannerController::class, 'ckeUpload' ] )->name( 'admin.banner.ckeUpload' );
            } );

            Route::prefix( 'exclusive-deals' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view exclusive deals' ] ], function() {
                    Route::get( '/', [ ExclusiveDealController::class, 'index' ] )->name( 'admin.module_parent.exclusive_deal.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add exclusive deals' ] ], function() {
                    Route::get( 'add', [ ExclusiveDealController::class, 'add' ] )->name( 'admin.exclusive_deal.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit exclusive deals' ] ], function() {
                    Route::get( 'edit', [ ExclusiveDealController::class, 'edit' ] )->name( 'admin.exclusive_deal.edit' );
                } );

                Route::post( 'update-order', [ ExclusiveDealController::class, 'updateOrder' ] )->name( 'admin.exclusive_deal.updateOrder' );
                Route::post( 'all-exclusive-deals', [ ExclusiveDealController::class, 'allExclusiveDeals' ] )->name( 'admin.exclusive_deal.allExclusiveDeals' );
                Route::post( 'one-exclusive-deal', [ ExclusiveDealController::class, 'oneExclusiveDeal' ] )->name( 'admin.exclusive_deal.oneExclusiveDeal' );
                Route::post( 'create-exclusive-deal', [ ExclusiveDealController::class, 'createExclusiveDeal' ] )->name( 'admin.exclusive_deal.createExclusiveDeal' );
                Route::post( 'update-exclusive-deal', [ ExclusiveDealController::class, 'updateExclusiveDeal' ] )->name( 'admin.exclusive_deal.updateExclusiveDeal' );
                Route::post( 'delete-exclusive-deal', [ ExclusiveDealController::class, 'deleteExclusiveDeal' ] )->name( 'admin.exclusive_deal.deleteExclusiveDeal' );
                Route::post( 'update-exclusive-deal-status', [ ExclusiveDealController::class, 'updateExclusiveDealStatus' ] )->name( 'admin.exclusive_deal.updateExclusiveDealStatus' );
                Route::post( 'remove-exclusive-deal-image', [ ExclusiveDealController::class, 'removeExclusiveDealImage' ] )->name( 'admin.exclusive_deal.removeExclusiveDealImage' );
                Route::post( 'ckeUpload', [ ExclusiveDealController::class, 'ckeUpload' ] )->name( 'admin.exclusive_deal.ckeUpload' );
            } );

            Route::prefix( 'blogs' )->group( function() {

                Route::group( [ 'middleware' => [ 'permission:view blogs' ] ], function() {
                    Route::get( '/', [ BlogController::class, 'index' ] )
                        ->name( 'admin.module_parent.blog.index' );
                } );
            
                Route::group( [ 'middleware' => [ 'permission:add blogs' ] ], function() {
                    Route::get( 'add', [ BlogController::class, 'add' ] )
                        ->name( 'admin.blog.add' );
                } );
            
                Route::group( [ 'middleware' => [ 'permission:edit blogs' ] ], function() {
                    Route::get( 'edit', [ BlogController::class, 'edit' ] )
                        ->name( 'admin.blog.edit' );
                } );
            
                Route::post( 'all-blogs', [ BlogController::class, 'allBlogs' ] )
                    ->name( 'admin.blog.allBlogs' );
            
                Route::post( 'one-blog', [ BlogController::class, 'oneBlog' ] )
                    ->name( 'admin.blog.oneBlog' );
            
                Route::post( 'create-blog', [ BlogController::class, 'createBlog' ] )
                    ->name( 'admin.blog.createBlog' );
            
                Route::post( 'update-blog', [ BlogController::class, 'updateBlog' ] )
                    ->name( 'admin.blog.updateBlog' );
            
                Route::post( 'update-blog-status', [ BlogController::class, 'updateBlogStatus' ] )
                    ->name( 'admin.blog.updateBlogStatus' );
            
                Route::post( 'ckeUpload', [ BlogController::class, 'ckeUpload' ] )
                    ->name( 'admin.blog.ckeUpload' );
            
                Route::post( 'imageUpload', [ BlogController::class, 'imageUpload' ] )
                    ->name( 'admin.blog.imageUpload' );
            
            } );            

            Route::prefix( 'featured-sports' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view sports' ] ], function() {
                    Route::get( '/', [ FeaturedSportController::class, 'index' ] )->name( 'admin.module_parent.featured_sport.index' );
                } );
                Route::post( 'all',           [ FeaturedSportController::class, 'allFeaturedSports' ] )->name( 'admin.featured_sport.allFeaturedSports' );
                Route::post( 'create',        [ FeaturedSportController::class, 'createFeaturedSport' ] )->name( 'admin.featured_sport.createFeaturedSport' );
                Route::post( 'update-status', [ FeaturedSportController::class, 'updateFeaturedSportStatus' ] )->name( 'admin.featured_sport.updateFeaturedSportStatus' );
                Route::post( 'update-sequence', [ FeaturedSportController::class, 'updateSequence' ] )->name( 'admin.featured_sport.updateSequence' );
                Route::post( 'reorder',         [ FeaturedSportController::class, 'reorder' ] )->name( 'admin.featured_sport.reorder' );
                Route::post( 'delete',        [ FeaturedSportController::class, 'deleteFeaturedSport' ] )->name( 'admin.featured_sport.deleteFeaturedSport' );
            } );

            Route::prefix( 'popular-arenas' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view courts' ] ], function() {
                    Route::get( '/', [ PopularArenaController::class, 'index' ] )->name( 'admin.module_parent.popular_arena.index' );
                } );
                Route::post( 'all',             [ PopularArenaController::class, 'allPopularArenas' ] )->name( 'admin.popular_arena.allPopularArenas' );
                Route::post( 'create',          [ PopularArenaController::class, 'createPopularArena' ] )->name( 'admin.popular_arena.createPopularArena' );
                Route::post( 'update-status',   [ PopularArenaController::class, 'updatePopularArenaStatus' ] )->name( 'admin.popular_arena.updatePopularArenaStatus' );
                Route::post( 'update-sequence', [ PopularArenaController::class, 'updateSequence' ] )->name( 'admin.popular_arena.updateSequence' );
                Route::post( 'reorder',         [ PopularArenaController::class, 'reorder' ] )->name( 'admin.popular_arena.reorder' );
                Route::post( 'delete',          [ PopularArenaController::class, 'deletePopularArena' ] )->name( 'admin.popular_arena.deletePopularArena' );
            } );

            Route::prefix( 'otp-records' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view otps' ] ], function() {
                    Route::get( '/', [ OtpController::class, 'index' ] )->name( 'admin.module_parent.otp.index' );
                } );
                
                Route::post( 'all-otp', [ OtpController::class, 'allOtp' ] )->name( 'admin.otp.allOtp' );
                Route::post( 'resend-otp', [ OtpController::class, 'resendOtp' ] )->name( 'admin.otp.resendOtp' );
            } );

            Route::prefix( 'cms-articles' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view cms_article' ] ], function() {
                    Route::get( '/', [ CMSController::class, 'index' ] )->name( 'admin.module_parent.cms_article.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add cms_article' ] ], function() {
                    Route::get( 'add', [ CMSController::class, 'add' ] )->name( 'admin.cms_article.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit cms_article' ] ], function() {
                    Route::get( 'edit', [ CMSController::class, 'edit' ] )->name( 'admin.cms_article.edit' );
                } );
          
                Route::post( 'all-cms-articles', [ CMSController::class, 'allProjects' ] )->name( 'admin.cms_article.allProjects' );
                Route::post( 'one-cms-article', [ CMSController::class, 'oneProject' ] )->name( 'admin.cms_article.oneProject' );
                Route::post( 'create-cms-article', [ CMSController::class, 'createProject' ] )->name( 'admin.cms_article.createProject' );
                Route::post( 'update-cms-article', [ CMSController::class, 'updateProject' ] )->name( 'admin.cms_article.updateProject' );
                Route::post( 'update-cms-article-status', [ CMSController::class, 'updateProjectStatus' ] )->name( 'admin.cms_article.updateProjectStatus' );
                Route::post( 'delete-cms-article', [ CMSController::class, 'deleteProject' ] )->name( 'admin.cms_article.deleteProject' );
                Route::post( 'ckeUpload', [ CMSController::class, 'ckeUpload' ] )->name( 'admin.cms_article.ckeUpload' );
                Route::post( 'get-gallery', [ CMSController::class, 'getBanner' ] )->name( 'admin.cms_article.getBanner' );
                Route::post( 'remove-cms-article-banner-image', [ CMSController::class, 'removeCmsBannerImage' ] )->name( 'admin.cms_article.removeCmsBannerImage' );
          
            } );

            // Utils
            Route::prefix( 'utils' )->group( function() {
                Route::get(  'showcase',      [ UtilsController::class, 'showcase' ] )->name( 'admin.utils.showcase' );
                Route::post( 'calling-codes', [ UtilsController::class, 'getCallingCodes' ] )->name( 'admin.utils.getCallingCodes' );
                Route::post( 'cke-upload',    [ UtilsController::class, 'ckeUpload' ] )->name( 'admin.utils.ckeUpload' );
            } );

        } );

    } );

    // Public Route
    Route::get( 'lang/{lang}', function( $lang ) {

        if ( array_key_exists( $lang, Config::get( 'languages' ) ) ) {
            Session::put( 'appLocale', $lang );
        }
        return Redirect::back();
    } )->name( 'admin.switchLanguage' );

    Route::get( 'login', [ AdministratorController::class, 'login' ] )->middleware( 'guest:admin' )->name( 'admin.signin' );

    $limiter = config( 'fortify.limiters.login' );

    Route::post( 'login', [ AuthenticatedSessionController::class, 'store' ] )->middleware( array_filter( [ 'guest:admin', $limiter ? 'throttle:'.$limiter : null ] ) )->name( 'admin.login' );

    Route::post( 'logout', [ AuthenticatedSessionController::class, 'destroy' ] )->middleware( 'auth:admin' )->name( 'admin.logout' );
} );