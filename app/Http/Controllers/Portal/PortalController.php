<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FxCustomer;
use App\Models\FxTransaction;
use App\Models\FxArrangement;

class PortalController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('portal.index');
        }
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        $request->session()->regenerate();
        return redirect()->route('portal.index');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.login');
    }

    public function index(Request $request)
    {
        return view('portal.index');
    }

    public function myCustomers(Request $request)
    {
        $userId    = Auth::guard('web')->id();
        $customers = FxCustomer::where('user_id', $userId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'initial_balance']);

        return response()->json($customers);
    }

    public function customerDetail(Request $request, $id)
    {
        $userId   = Auth::guard('web')->id();
        $customer = FxCustomer::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $year  = $request->filled('year')  ? (int) $request->year  : null;
        $month = $request->filled('month') ? (int) $request->month : null;

        // Summary always computed from ALL transactions (matches FX sheet behaviour)
        $allTx = FxTransaction::where('fx_customer_id', $id)->get();
        $allArr = FxArrangement::where('fx_customer_id', $id)->get();

        $totalAmtIn   = (float) $allTx->sum('amount_in');
        $totalAmtOut  = (float) $allTx->sum('amount_out');
        $totalMyrConv = (float) $allTx->sum('myr_converted');
        $totalMyrOut  = (float) $allTx->sum('myr_out');
        $totalMyrIn   = (float) $allTx->sum('myr_in');
        $totalProfit  = (float) $allTx->sum('profit');
        $balance      = (float) $customer->initial_balance + $totalMyrConv - $totalMyrOut + $totalMyrIn;
        $pendingMyr   = (float) $allArr->where('is_done', false)->sum('arranging_amount');

        // Transactions and arrangements filtered by year/month for display
        $txQuery  = FxTransaction::where('fx_customer_id', $id)->orderBy('date')->orderBy('id');
        $arrQuery = FxArrangement::where('fx_customer_id', $id)->orderBy('date')->orderBy('id');

        if ($year && $month) {
            $txQuery->whereYear('date',  $year)->whereMonth('date',  $month);
            $arrQuery->whereYear('date', $year)->whereMonth('date', $month);
        }

        return response()->json([
            'customer'     => $customer,
            'transactions' => $txQuery->get(),
            'arrangements' => $arrQuery->get(),
            'summary'      => [
                'balance'      => round($balance, 2),
                'pending_myr'  => round($pendingMyr, 2),
                'total_profit' => round($totalProfit, 2),
                'totals'       => [
                    'amount_in'     => round($totalAmtIn, 4),
                    'amount_out'    => round($totalAmtOut, 4),
                    'myr_converted' => round($totalMyrConv, 2),
                    'myr_out'       => round($totalMyrOut, 2),
                    'myr_in'        => round($totalMyrIn, 2),
                ],
            ],
        ]);
    }
}
