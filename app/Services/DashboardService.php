<?php

namespace App\Services;

use App\Models\{
    Order,
    Owner,
    Farm,
    Buyer,
    Administrator,
    Invoice,
    Expense,
    User,
    InvoiceMeta,
    Outlet,
    Product,
    Bundle,
    Wallet,
    WalletTransaction,
    OrderMeta,
};

use Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class DashboardService
{
    public static function getDashboardData( $request ) {

        $today_revenue = Helper::numberFormat( 
            Order::whereIn( 'status', [1,3] )->whereDate('created_at', Carbon::today())->sum('total_price') + 
            WalletTransaction::whereDate('created_at', Carbon::today())->where( 'type',1 )->where('transaction_type', 1 )->where( 'status', 10 )->sum( 'amount' ), 
            2);
        $total_revenue = Helper::numberFormat( 
            Order::whereIn( 'status', [1,3] )->sum('total_price') + 
            WalletTransaction::where( 'type',1 )->where('transaction_type', 1 )->where( 'status', 10 )->sum( 'amount' ), 
            2, true );
        $new_users = $new_users = Helper::numberFormat(
            User::whereDate('created_at', Carbon::today())->count(),
            0
        );
        $total_users = Helper::numberFormat( User::where( 'status', 10 )->count(), 0 );

        return response()->json( [
            'today_revenue' => 'RM ' . $today_revenue,
            'total_revenue'  => 'RM ' . $total_revenue,
            'new_users' => $new_users,
            'total_users' => $total_users,
        ] );
    }

    public static function totalRevenueStatistics($request)
    {
        $type = $request->input('type', 'day');
        $chart = $request->chartId;
        $xAxis = [];
        $orderData = [];
    
        switch ($chart) {
            case 'chart1':
               
                if ($type === 'day') {
                    for ($x = 7; $x >= 0; $x--) {
                        $day = strtotime(date('Y-m-d') . ' -' . $x . ' day');
                        $thisDay = date('Y-m-d', $day);

                        $thisDayOrder = Order::whereIn('status', [1,3])
                            ->whereBetween('created_at', [$thisDay . ' 00:00:00', $thisDay . ' 23:59:59'])
                            ->sum( 'total_price' );

                        $thisDayTopUp = WalletTransaction::whereDate('created_at', [$thisDay . ' 00:00:00', $thisDay . ' 23:59:59'])
                        ->where( 'type',1 )
                        ->where('transaction_type', 1 )
                        ->where( 'status', 10 )
                        ->sum( 'amount' );

                        $xAxis[] = date('M d', $day);
                        $orderData[] = $thisDayOrder + $thisDayTopUp;
                    }
                } elseif ($type === 'week') {
                    for ($x = 6; $x >= 0; $x--) {
                        $startOfWeek = date('Y-m-d', strtotime("last sunday -{$x} week"));
                        $endOfWeek = date('Y-m-d', strtotime("next saturday -{$x} week"));
            
                        $weekOrder = Order::whereIn('status', [1,3])
                            ->whereBetween('created_at', [$startOfWeek . ' 00:00:00', $endOfWeek . ' 23:59:59'])
                            ->sum( 'total_price' );

                        $weekTopUp = WalletTransaction::whereDate('created_at', [$startOfWeek . ' 00:00:00', $endOfWeek . ' 23:59:59'])
                            ->where( 'type',1 )
                            ->where('transaction_type', 1 )
                            ->where( 'status', 10 )
                            ->sum( 'amount' );
            
                        $xAxis[] = date('M d', strtotime($startOfWeek)) . ' - ' . date('M d', strtotime($endOfWeek));
                        $orderData[] = $weekOrder + $weekTopUp;
                    }
                } elseif ($type === 'month') {
                    // Last 6 months
                    for ($x = 5; $x >= 0; $x--) {
                        $startOfMonth = date('Y-m-01', strtotime("-{$x} month"));
                        $endOfMonth = date('Y-m-t', strtotime("-{$x} month"));
            
                        $monthOrder = Order::whereIn('status', [1,3])
                            ->whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])
                            ->sum( 'total_price' );

                        $monthTopUp = WalletTransaction::whereDate('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])
                            ->where( 'type',1 )
                            ->where('transaction_type', 1 )
                            ->where( 'status', 10 )
                            ->sum( 'amount' );
            
                        $xAxis[] = date('M Y', strtotime($startOfMonth));
                        $orderData[] = $monthOrder + $monthTopUp;
                    }
                }

                break;

            case 'chart2':
            
                if ($type === 'day') {
                    for ($x = 7; $x >= 0; $x--) {
                        $day = strtotime(date('Y-m-d') . ' -' . $x . ' day');
                        $thisDay = date('Y-m-d', $day);

                        $thisDayTopUp = WalletTransaction::whereBetween('created_at', [$thisDay . ' 00:00:00', $thisDay . ' 23:59:59'])
                        ->where( 'type',1 )
                        ->where('transaction_type', 1 )
                        ->where( 'status', 10 )
                        ->sum( 'amount' );

                        $xAxis[] = date('M d', $day);
                        $orderData[] = $thisDayTopUp;
                    }
                } elseif ($type === 'week') {
                    for ($x = 6; $x >= 0; $x--) {
                        $startOfWeek = date('Y-m-d', strtotime("last sunday -{$x} week"));
                        $endOfWeek = date('Y-m-d', strtotime("next saturday -{$x} week"));
                        
                        $weekTopUp = WalletTransaction::whereBetween('created_at', [
                            $startOfWeek . ' 00:00:00',
                            $endOfWeek . ' 23:59:59',
                        ])
                        ->where( 'type',1 )
                        ->where('transaction_type', 1 )
                        ->where( 'status', 10 )
                        ->sum( 'amount' );
                        $xAxis[] = date('M d', strtotime($startOfWeek)) . ' - ' . date('M d', strtotime($endOfWeek));
                        $orderData[] = $weekTopUp;
                    }
                } elseif ($type === 'month') {
                    // Last 6 months
                    for ($x = 5; $x >= 0; $x--) {
                        $startOfMonth = date('Y-m-01', strtotime("-{$x} month"));
                        $endOfMonth = date('Y-m-t', strtotime("-{$x} month"));
            
                        $monthTopUp = WalletTransaction::whereBetween('created_at', [
                            $startOfMonth . ' 00:00:00',
                            $endOfMonth . ' 23:59:59',
                        ])
                            ->where( 'type',1 )
                            ->where('transaction_type', 1 )
                            ->where( 'status', 10 )
                            ->sum( 'amount' );
            
                        $xAxis[] = date('M Y', strtotime($startOfMonth));
                        $orderData[] = $monthTopUp;
                    }
                }

                break;

            case 'chart3':
        
                if ($type === 'day') {
                    for ($x = 7; $x >= 0; $x--) {
                        $day = strtotime(date('Y-m-d') . ' -' . $x . ' day');
                        $thisDay = date('Y-m-d', $day);

                        $thisDayOrder = OrderMeta::where('status', 10)
                            ->whereBetween('created_at', [$thisDay . ' 00:00:00', $thisDay . ' 23:59:59'])
                            ->count();

                        $xAxis[] = date('M d', $day);
                        $orderData[] = $thisDayOrder;
                    }
                } elseif ($type === 'week') {
                    for ($x = 6; $x >= 0; $x--) {
                        $startOfWeek = date('Y-m-d', strtotime("last sunday -{$x} week"));
                        $endOfWeek = date('Y-m-d', strtotime("next saturday -{$x} week"));
            
                        $weekOrder = OrderMeta::where('status', 10)
                            ->whereBetween('created_at', [$startOfWeek . ' 00:00:00', $endOfWeek . ' 23:59:59'])
                            ->count();
            
                        $xAxis[] = date('M d', strtotime($startOfWeek)) . ' - ' . date('M d', strtotime($endOfWeek));
                        $orderData[] = $weekOrder;
                    }
                } elseif ($type === 'month') {
                    // Last 6 months
                    for ($x = 5; $x >= 0; $x--) {
                        $startOfMonth = date('Y-m-01', strtotime("-{$x} month"));
                        $endOfMonth = date('Y-m-t', strtotime("-{$x} month"));
            
                        $monthOrder = OrderMeta::where('status', 10)
                            ->whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])
                            ->count();
            
                        $xAxis[] = date('M Y', strtotime($startOfMonth));
                        $orderData[] = $monthOrder;
                    }
                }

                break;

            case 'chart4':
    
                if ($type === 'day') {
                    for ($x = 7; $x >= 0; $x--) {
                        $day = strtotime(date('Y-m-d') . ' -' . $x . ' day');
                        $thisDay = date('Y-m-d', $day);

                        $thisDayOrder = User::where('status', 10)
                            ->whereBetween('created_at', [$thisDay . ' 00:00:00', $thisDay . ' 23:59:59'])
                            ->count();

                        $xAxis[] = date('M d', $day);
                        $orderData[] = $thisDayOrder;
                    }
                } elseif ($type === 'week') {
                    for ($x = 6; $x >= 0; $x--) {
                        $startOfWeek = date('Y-m-d', strtotime("last sunday -{$x} week"));
                        $endOfWeek = date('Y-m-d', strtotime("next saturday -{$x} week"));
            
                        $weekOrder = User::where('status', 10)
                            ->whereBetween('created_at', [$startOfWeek . ' 00:00:00', $endOfWeek . ' 23:59:59'])
                            ->count();
            
                        $xAxis[] = date('M d', strtotime($startOfWeek)) . ' - ' . date('M d', strtotime($endOfWeek));
                        $orderData[] = $weekOrder;
                    }
                } elseif ($type === 'month') {
                    // Last 6 months
                    for ($x = 5; $x >= 0; $x--) {
                        $startOfMonth = date('Y-m-01', strtotime("-{$x} month"));
                        $endOfMonth = date('Y-m-t', strtotime("-{$x} month"));
            
                        $monthOrder = User::where('status', 10)
                            ->whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])
                            ->count();
            
                        $xAxis[] = date('M Y', strtotime($startOfMonth));
                        $orderData[] = $monthOrder;
                    }
                }

                break;
            
            default:
                # code...
                break;
        }
    
        return response()->json([
            'orderData' => $orderData,
            'xAxis' => $xAxis,
        ]);
    }

    public static function getDashboardStatistics()
    {
        // Get Top 5 Outlets based on Revenue
        $topOutlets = Invoice::with(['outlet'])
            ->select('outlet_id', DB::raw('SUM(final_amount) as total_revenue'))
            ->where('status', 10)
            ->whereNotNull('outlet_id')
            ->groupBy('outlet_id')
            ->with('outlet:id,name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'name' => $invoice->outlet->name ?? 'No Outlet',
                    'revenue' => Helper::numberFormatV2($invoice->total_revenue, 2)
                ];
            });

        if( count($topOutlets) > 0 ){
            // If there are less than 5 outlets found, fetch the remaining outlets
            $remainingOutlets = Outlet::select('id', 'name')->whereNotIn('id', $topOutlets->pluck('outlet_id'))->get();

            // Merge remaining outlets with 0 revenue
            $allTopOutlets = $topOutlets->merge($remainingOutlets->map(function ($outlet) {
                return [
                    'name' => $outlet->name,
                    'revenue' => 41888
                ];
            }));
        }else{
            $allTopOutlets = Outlet::select('id', 'name')->get()->map(function ($outlet) {
                return [
                    'name' => $outlet->name,
                    'revenue' => 41888
                ];
            });
        }
        // Get Top 10 Best Selling Products
        $topProducts = InvoiceMeta::with(['product', 'variant', 'bundle'])
        ->select(
            'product_id',
            'variant_id',
            'bundle_id',
            DB::raw("IFNULL(variant_id, IFNULL(bundle_id, product_id)) as item_id"),
            DB::raw("SUM(quantity) as total_sales")
        )
        ->whereHas('invoice')
        ->groupBy('product_id', 'variant_id', 'bundle_id')
        ->orderByDesc('total_sales')
        ->take(10)
        ->get()
        ->map(function ($invoiceMeta) {
            $title = '-';
            if ($invoiceMeta->product) {
                $title = $invoiceMeta->product->title;
            } elseif ($invoiceMeta->variant) {
                $title = $invoiceMeta->variant->title;
            } elseif ($invoiceMeta->bundle) {
                $title = $invoiceMeta->bundle->title;
            }
    
            return [
                'product' => $title,
                'sales' => $invoiceMeta->total_sales
            ];
        });
    
        // If there are less than 10 products/variants/bundles, fetch the latest products/variants/bundles with zero sales
        $remainingProductsCount = 10 - $topProducts->count();
        if ($remainingProductsCount > 0) {
        $latestProducts = Product::select('id', 'title', 'created_at')
            ->union(
                ProductVariant::select('id', 'title', DB::raw('NULL as created_at'))
            )
            ->union(
                Bundle::select('id', 'title', DB::raw('NULL as created_at'))
            )
            ->orderByDesc('created_at')
            ->take($remainingProductsCount)
            ->get()
            ->map(function ($item) {
                return [
                    'product' => $item->title,
                    'sales' => 0,
                ];
            });
            
        // Merge the latest products/variants/bundles with 0 sales
        $topProducts = $topProducts->merge($latestProducts);
        }
        
        
        return response()->json([
            'topOutlets' => $allTopOutlets,
            'topProducts' => $topProducts,
        ]);
    
    }
    
    
}