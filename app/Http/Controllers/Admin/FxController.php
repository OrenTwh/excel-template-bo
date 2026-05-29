<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\{FxCustomer, FxTransaction, FxArrangement, FxMasterDaily};
use App\Services\FxSpreadsheetService;

class FxController extends Controller
{
    public function __construct(protected FxSpreadsheetService $fx) {}

    // ── Main view ─────────────────────────────────────────────────────────────
    public function index()
    {
        $customers = FxCustomer::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        return view('fx.index', compact('customers'));
    }

    // ── 総表 (Master sheet) ───────────────────────────────────────────────────
    public function master(Request $request)
    {
        $year  = (int) ($request->year  ?? now()->year);
        $month = (int) ($request->month ?? now()->month);
        // AH1 — DATE# day picker; defaults to today's day when viewing the current month, else day 1
        $day   = $request->filled('day')
            ? (int) $request->day
            : (($year === now()->year && $month === now()->month) ? now()->day : 1);
        $day   = max(1, min($day, Carbon::create($year, $month, 1)->daysInMonth));
        $date  = Carbon::create($year, $month, $day);

        return response()->json([
            'daily_rows'     => $this->fx->masterDailyRows($year, $month),
            'manual_daily'   => FxMasterDaily::forMonth($year, $month),
            'customer_rows'  => $this->fx->masterCustomerRows($date),
            'header'         => $this->fx->masterHeaderTotals(),
            'need_pay'       => $this->fx->totalNeedPay(),
            'currency_cols'  => FxMasterDaily::$currencyCols,
            'year'           => $year,
            'month'          => $month,
            'day'            => $day,
        ]);
    }

    // ── Master daily cell save (single cell PATCH) ────────────────────────────
    public function masterDailyUpdate(Request $request)
    {
        $request->validate([
            'year'  => 'required|integer',
            'month' => 'required|integer',
            'day'   => 'required|integer',
            'col'   => 'required|string|in:sgd,thb,pgk,usdt,aud_slow,aud_fast,usd1,usd2,usd3,usd4,balance_myr,need_pay',
            'value' => 'nullable|numeric',
        ]);

        $row = FxMasterDaily::upsertDay(
            $request->year,
            $request->month,
            $request->day,
            $request->col,
            $request->value ?? 0
        );

        return response()->json(['success' => true, 'row' => $row]);
    }

    // ── 総表詳情 (Detail sheet) ────────────────────────────────────────────────
    public function detail(Request $request)
    {
        $year  = (int) ($request->year  ?? now()->year);
        $month = (int) ($request->month ?? now()->month);

        return response()->json($this->fx->detailMatrix($year, $month));
    }

    // ── Customer sheet ────────────────────────────────────────────────────────
    public function customer(Request $request, int $id)
    {
        $customer = FxCustomer::findOrFail($id);
        $year     = $request->filled('year')  ? (int) $request->year  : null;
        $month    = $request->filled('month') ? (int) $request->month : null;

        $summary  = $this->fx->customerSummary($customer);

        $txQuery  = FxTransaction::where('fx_customer_id', $id)->orderBy('date')->orderBy('id');
        $arrQuery = FxArrangement::where('fx_customer_id', $id)->orderBy('date')->orderBy('id');

        if ($year && $month) {
            $txQuery->whereYear('date',  $year)->whereMonth('date',  $month);
            $arrQuery->whereYear('date', $year)->whereMonth('date', $month);
        }

        return response()->json([
            'customer'     => $customer,
            'summary'      => $summary,
            'transactions' => $txQuery->get(),
            'arrangements' => $arrQuery->get(),
        ]);
    }

    // ── Customer CRUD ─────────────────────────────────────────────────────────
    public function customerList()
    {
        return response()->json(
            FxCustomer::orderBy('id')->get(['id', 'name', 'initial_balance', 'status', 'check_today'])
        );
    }

    public function customerStore(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100|unique:fx_customers,name',
            'initial_balance' => 'nullable|numeric',
        ]);

        $customer = FxCustomer::create([
            'name'            => $request->name,
            'initial_balance' => $request->initial_balance ?? 0,
            'status'          => 'active',
            'check_today'     => false,
        ]);

        return response()->json(['success' => true, 'customer' => $customer]);
    }

    public function customerUpdate(Request $request, int $id)
    {
        $request->validate([
            'name'            => 'required|string|max:100|unique:fx_customers,name,'.$id,
            'initial_balance' => 'nullable|numeric',
            'status'          => 'nullable|in:active,inactive',
        ]);

        $customer = FxCustomer::findOrFail($id);
        $customer->update([
            'name'            => $request->name,
            'initial_balance' => $request->initial_balance ?? $customer->initial_balance,
            'status'          => $request->status          ?? $customer->status,
        ]);

        return response()->json(['success' => true, 'customer' => $customer->fresh()]);
    }

    public function customerToggleCheckToday(int $id)
    {
        $customer = FxCustomer::findOrFail($id);
        $customer->update(['check_today' => !$customer->check_today]);
        return response()->json(['check_today' => $customer->check_today]);
    }

    public function customerDelete(int $id)
    {
        $customer = FxCustomer::findOrFail($id);
        $customer->transactions()->delete();
        $customer->arrangements()->delete();
        $customer->delete();
        return response()->json(['success' => true]);
    }

    // ── Transaction CRUD ──────────────────────────────────────────────────────
    public function transactionStore(Request $request)
    {
        $request->validate([
            'fx_customer_id' => 'required|exists:fx_customers,id',
            'date'           => 'required|date',
        ]);

        $amountIn  = (float) ($request->amount_in  ?? 0);
        $amountOut = (float) ($request->amount_out ?? 0);
        $rate      = (float) ($request->rate       ?? 0);
        $costRate  = (float) ($request->cost_rate  ?? 0);

        $tx = FxTransaction::create([
            'fx_customer_id' => $request->fx_customer_id,
            'date'           => $request->date,
            'currency'       => $request->currency,
            'amount_in'      => $amountIn,
            'amount_out'     => $amountOut,
            'rate'           => $rate,
            'myr_converted'  => $this->fx->myrConverted($rate, $amountIn, $amountOut),
            'myr_out'        => (float) ($request->myr_out ?? 0),
            'myr_in'         => (float) ($request->myr_in  ?? 0),
            'remark'         => $request->remark,
            'cost_rate'      => $costRate,
            'profit'         => $this->fx->profit($amountIn, $costRate, $rate),
            'created_by'     => auth()->id(),
        ]);

        return response()->json(['success' => true, 'data' => $tx]);
    }

    public function transactionUpdate(Request $request, int $id)
    {
        $tx = FxTransaction::findOrFail($id);

        $amountIn  = (float) ($request->amount_in  ?? $tx->amount_in);
        $amountOut = (float) ($request->amount_out ?? $tx->amount_out);
        $rate      = (float) ($request->rate       ?? $tx->rate);
        $costRate  = (float) ($request->cost_rate  ?? $tx->cost_rate);

        $tx->update([
            'date'          => $request->date          ?? $tx->date,
            'currency'      => $request->currency      ?? $tx->currency,
            'amount_in'     => $amountIn,
            'amount_out'    => $amountOut,
            'rate'          => $rate,
            'myr_converted' => $this->fx->myrConverted($rate, $amountIn, $amountOut),
            'myr_out'       => (float) ($request->myr_out ?? $tx->myr_out),
            'myr_in'        => (float) ($request->myr_in  ?? $tx->myr_in),
            'remark'        => $request->remark        ?? $tx->remark,
            'cost_rate'     => $costRate,
            'profit'        => $this->fx->profit($amountIn, $costRate, $rate),
        ]);

        return response()->json(['success' => true, 'data' => $tx->fresh()]);
    }

    public function transactionDelete(int $id)
    {
        FxTransaction::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // ── Arrangement CRUD ──────────────────────────────────────────────────────
    public function arrangementStore(Request $request)
    {
        $request->validate([
            'fx_customer_id' => 'required|exists:fx_customers,id',
            'date'           => 'required|date',
        ]);

        $arr = FxArrangement::create([
            'fx_customer_id'   => $request->fx_customer_id,
            'date'             => $request->date,
            'account_number'   => $request->account_number,
            'beneficiary_name' => $request->beneficiary_name,
            'bank'             => $request->bank,
            'arranging_amount' => (float) ($request->arranging_amount ?? 0),
            'done_amount'      => (float) ($request->done_amount      ?? 0),
            'is_done'          => (bool)  ($request->is_done          ?? false),
            'processed_by'     => $request->processed_by,
        ]);

        return response()->json(['success' => true, 'data' => $arr]);
    }

    public function arrangementUpdate(Request $request, int $id)
    {
        $arr = FxArrangement::findOrFail($id);
        $arr->update($request->only([
            'date', 'account_number', 'beneficiary_name', 'bank',
            'arranging_amount', 'done_amount', 'is_done', 'processed_by',
        ]));
        return response()->json(['success' => true, 'data' => $arr->fresh()]);
    }

    public function arrangementDelete(int $id)
    {
        FxArrangement::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
