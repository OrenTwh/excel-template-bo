            <!-- sidebar @s -->
            <div class="nk-sidebar nk-sidebar-fixed is-light " data-content="sidebarMenu">
                <div class="nk-sidebar-element nk-sidebar-head">
                    <div class="nk-sidebar-brand">
                        <a href="{{ route( 'admin.home' ) }}" class="logo-link nk-sidebar-logo">
                            <img class="logo-dark logo-img" src="{{ asset( 'admin/images/logo.png' ) . Helper::assetVersion() }}" srcset="{{ asset( 'admin/images/logo.png' ) . Helper::assetVersion() }} 2x" alt="logo-dark">
                            <img class="logo-small logo-img logo-img-small" src="{{ asset( 'admin/images/logo.png' ) . Helper::assetVersion() }}" srcset="{{ asset( 'admin/images/logo.png' ) . Helper::assetVersion() }} 2x" alt="logo-small">
                        </a>
                    </div>
                    <div class="nk-menu-trigger me-n2">
                        <a href="#" class="nk-nav-toggle nk-quick-nav-icon d-xl-none" data-target="sidebarMenu"><em class="icon ni ni-arrow-left"></em></a>
                        <a href="#" class="nk-nav-compact nk-quick-nav-icon d-none d-xl-inline-flex" data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
                    </div>
                </div><!-- .nk-sidebar-element -->
                <div class="nk-sidebar-element">
                    <div class="nk-sidebar-content">
                        <div class="nk-sidebar-menu" data-simplebar>
                            <ul class="nk-menu">
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\DashboardController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.dashboard' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-growth-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.dashboard' ) }}</span>
                                    </a>
                                </li>
                                @can( 'view administrators' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AdministratorController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.administrator.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user-list-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.administrators' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view roles' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\RoleController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.role.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user-list-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.roles' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view audits' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AuditController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.audit.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-db-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.audit_logs' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view otp_logs' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\OtpController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.otp.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-mobile"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.otp_logs' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                {{-- ===================== USERS ===================== --}}
                                <li class="nk-menu-heading">
                                    <h6 class="overline-title text-primary-alt">{{ __( 'template.operations' ) }}</h6>
                                </li>

                                @can( 'view Users' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\UserController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.user.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-user-group-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.users' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @can( 'view Wallets' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\WalletController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.wallet.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-money"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.wallets' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view Wallet Transactions' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\WalletTransactionController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.wallet_transaction.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-swap"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.wallet_transactions' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @if( 1 == 2 )
                                @can( 'view user_friends' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\UserFriendController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.user_friend.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-users-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.user_friends' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                @endif

                                {{-- ===================== MARKETING ===================== --}}
                                <li class="nk-menu-heading">
                                    <h6 class="overline-title text-primary-alt">{{ __( 'template.marketing' ) }}</h6>
                                </li>

                                @can( 'view banners' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\BannerController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.banner.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-flag-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.banners' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view exclusive deals' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ExclusiveDealController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.exclusive_deal.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-tag-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.exclusive_deals' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view Vouchers' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\VoucherController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.voucher.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-ticket-alt"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.vouchers' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view User Vouchers' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\UserVoucherController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.user_voucher.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-ticket-plus"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.user_vouchers' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                {{-- ===================== ECOMMERCE ===================== --}}
                                <li class="nk-menu-heading">
                                    <h6 class="overline-title text-primary-alt">{{ __( 'Ecommerce' ) }}</h6>
                                </li>

                                @can( 'view sport_products' )
                                @php $isProductCatController   = $controller == 'App\Http\Controllers\Admin\SportProductCategoryController'; @endphp
                                @php $isProductController      = $controller == 'App\Http\Controllers\Admin\SportProductController'; @endphp
                                @php $isProductOrderController = $controller == 'App\Http\Controllers\Admin\SportProductOrderController'; @endphp
                                <li class="nk-menu-item has-sub {{ ( $isProductCatController || $isProductController || $isProductOrderController ) ? 'active' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-bag-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.sport_products' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item {{ $isProductController && ( $action ?? '' ) == 'index' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.sport_product.index' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-text">{{ __( 'template.sport_products' ) }}</span>
                                            </a>
                                        </li>
                                        <li class="nk-menu-item {{ $isProductCatController && ( $action ?? '' ) == 'index' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.sport_product_category.index' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-text">{{ __( 'template.sport_product_categories' ) }}</span>
                                            </a>
                                        </li>
                                        @if( 1 == 2)
                                        <li class="nk-menu-item {{ $isProductOrderController && ( $action ?? '' ) == 'index' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.sport_product_order.index' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-text">{{ __( 'Orders' ) }}</span>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </li>
                                @endcan

                                {{-- ===================== SPORTS & VENUES ===================== --}}
                                {{-- Chain: Sports → Venue → VenueSport → Court → CourtBooking --}}
                                <li class="nk-menu-heading">
                                    <h6 class="overline-title text-primary-alt">{{ __( 'template.sports_venues' ) }}</h6>
                                </li>

                                @can( 'view sports' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\SportController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.sport.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-star"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.sports' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view courts' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\VenueController' && $action == 'index' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.venue.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-building"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.venues' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view courts' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\VenueController' && $action == 'sportIndex' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.venue_sport.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-layers-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.venue_sports' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view courts' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\CourtController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.court.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-book-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.courts' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view court_bookings' )
                                @php $isBookingController = $controller == 'App\Http\Controllers\Admin\CourtBookingController'; @endphp
                                <li class="nk-menu-item has-sub {{ $isBookingController ? 'active' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-calendar-check"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.court_bookings' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item {{ $isBookingController && ( $data['section'] ?? '' ) == 'pending' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.court_booking.pending' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-text">{{ __( 'court_booking.pending_payment' ) }}</span>
                                            </a>
                                        </li>
                                        <li class="nk-menu-item {{ $isBookingController && ( $data['section'] ?? '' ) == 'upcoming' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.court_booking.upcoming' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-text">{{ __( 'court_booking.upcoming' ) }}</span>
                                            </a>
                                        </li>
                                        <li class="nk-menu-item {{ $isBookingController && ( $data['section'] ?? '' ) == 'complete' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.court_booking.complete' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-text">{{ __( 'court_booking.complete' ) }}</span>
                                            </a>
                                        </li>
                                        <li class="nk-menu-item {{ $isBookingController && ( $data['section'] ?? '' ) == 'suspended' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.court_booking.suspended' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-text">{{ __( 'court_booking.suspended' ) }}</span>
                                            </a>
                                        </li>
                                        <li class="nk-menu-item {{ $isBookingController && $action == 'bookingCalendar' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.court_booking.booking_calendar' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-text">{{ __( 'court_booking.booking_calendar' ) }}</span>
                                            </a>
                                        </li>
                                        <li class="nk-menu-item {{ $isBookingController && $action == 'availabilityStatistic' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.court_booking.availability_statistic' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-text">{{ __( 'court_booking.availability_statistic' ) }}</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan

                                @can( 'view court_calendars' )
                                @php $isCalendarController = $controller == 'App\Http\Controllers\Admin\CourtCalendarController'; @endphp
                                <li class="nk-menu-item has-sub {{ $isCalendarController ? 'active' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-calendar-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.court_calendars' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item {{ $isCalendarController && ( $action ?? '' ) == 'index' && ( $data['section'] ?? '' ) == 'events' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.court_calendar.index' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-text">{{ __( 'Events' ) }}</span>
                                            </a>
                                        </li>
                                        <li class="nk-menu-item {{ $isCalendarController && ( $action ?? '' ) == 'userActivities' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.court_calendar.user_activities' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-text">{{ __( 'User Activities' ) }}</span>
                                            </a>
                                        </li>
                                        <li class="nk-menu-item {{ $isCalendarController && ( $action ?? '' ) == 'eventParticipants' ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.court_calendar.event_participants' ) }}" class="nk-menu-link">
                                                <span class="nk-menu-text">{{ __( 'Joining History' ) }}</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan

                                @can( 'view sports' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\FeaturedSportController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.featured_sport.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-star-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'Featured Sports' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view courts' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\PopularArenaController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.popular_arena.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-map-pin-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'Popular Arenas' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view sports' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\SportsTagController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.sports_tag.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-tags-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.amenities' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                {{-- ===================== SYSTEM ===================== --}}
                                <li class="nk-menu-heading">
                                    <h6 class="overline-title text-primary-alt">{{ __( 'template.system' ) }}</h6>
                                </li>

                                @if( 1 == 2 )
                                @can( 'view locations' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\LocationController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.location.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-navigate-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.locations' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                @endif

                                @can( 'view cms_articles' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\CMSController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.cms_article.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-article"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.cms_articles' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view Settings' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\SettingController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.setting.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-setting-alt"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.settings' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @if( 1 == 2 )
                                @can( 'view announcements' )
                                    <li class="nk-menu-item has-sub {{ ($controller == 'App\Http\Controllers\Admin\AnnouncementController' || $controller == 'App\Http\Controllers\Admin\AnnouncementRewardController' ) ? 'active current-page' : '' }}">
                                        <a href="#" class="nk-menu-link nk-menu-toggle">
                                            <span class="nk-menu-icon"><em class="icon ni ni-list-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.announcements' ) }}</span>
                                        </a>
                                        <ul class="nk-menu-sub">
                                            <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AnnouncementController' && in_array( $action, [ 'index', 'edit', 'add' ] ) ? 'active current-page' : '' }}">
                                                <a href="{{ route( 'admin.module_parent.announcement.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.announcements' ) }}</span></a>
                                            </li>
                                        </ul>
                                    </li>
                                @endcan

                                @can( 'view Product' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ProductController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.product.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-notes-alt"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.products' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                @endif

                            </ul><!-- .nk-menu -->
                        </div><!-- .nk-sidebar-menu -->
                    </div><!-- .nk-sidebar-content -->
                </div><!-- .nk-sidebar-element -->
            </div>
            <!-- sidebar @e -->