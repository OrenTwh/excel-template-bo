<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\{
    BoBank,
    BoBankTransaction,
    BoTransaction,
    Administrator,
    Announcement,
    Role,
    FxCustomer,
    FxTransaction,
};

class BoDashboardController extends Controller
{
    // ─── Main SPA view ───────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        return view('bo.index');
    }

    // ─── Announcement ticker ──────────────────────────────────────────────────────

    public function announcement(Request $request)
    {
        $text = '';
        try {
            $ann = Announcement::where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->first();
            if ($ann) {
                $text = strip_tags($ann->title ?? $ann->content ?? '');
            }
        } catch (\Throwable $e) {
            // silently ignore if table doesn't exist yet
        }

        return response()->json(['text' => $text ?: 'Welcome to the Back Office.']);
    }

    // ─── Transactions ─────────────────────────────────────────────────────────────

    public function transactions(Request $request)
    {
        $query = FxTransaction::with('customer');

        if ($request->filled('customer')) {
            $query->whereHas('customer', fn($q) => $q->where('name', 'like', '%' . $request->customer . '%'));
        }
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $total   = (clone $query)->sum('myr_converted');
        $records = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(50);

        return response()->json([
            'records' => $records->total(),
            'total'   => number_format($total, 2),
            'data'    => $records->items(),
            'pages'   => $records->lastPage(),
            'current' => $records->currentPage(),
        ]);
    }

    public function transactionExport(Request $request)
    {
        $query = FxTransaction::with('customer');

        if ($request->filled('customer')) {
            $query->whereHas('customer', fn($q) => $q->where('name', 'like', '%' . $request->customer . '%'));
        }
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $records = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        $filename = 'fx_transactions_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $columns = ['Date', 'Customer', 'Currency', 'BUY IN', 'SELL OUT', 'Rate', 'MYR Conv.', 'MYR OUT', 'MYR IN', 'Remark', 'Cost Rate', 'Profit'];

        $callback = function () use ($records, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            foreach ($records as $row) {
                fputcsv($handle, [
                    $row->date?->format('Y-m-d'),
                    $row->customer?->name,
                    $row->currency,
                    $row->amount_in,
                    $row->amount_out,
                    $row->rate,
                    $row->myr_converted,
                    $row->myr_out,
                    $row->myr_in,
                    $row->remark,
                    $row->cost_rate,
                    $row->profit,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── Banks ───────────────────────────────────────────────────────────────────

    public function banks(Request $request)
    {
        $query = BoBank::query();
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        $banks = $query->orderBy('display_order')->orderBy('id')->get();

        return response()->json($banks);
    }

    public function bankStore(Request $request)
    {
        $request->validate([
            'bank_name'      => 'required|string|max:255',
            'account_name'   => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
        ]);

        $bank = BoBank::create([
            'bank_id'        => (string) (time()),
            'display_order'  => $request->display_order ?? 0,
            'gateway'        => $request->gateway,
            'bank_name'      => $request->bank_name,
            'account_name'   => $request->account_name,
            'account_number' => $request->account_number,
            'balance'        => $request->balance ?? 0,
            'remark'         => $request->remark,
            'status'         => 'active',
        ]);

        return response()->json(['success' => true, 'data' => $bank]);
    }

    public function bankUpdate(Request $request, $id)
    {
        $bank = BoBank::findOrFail($id);
        $bank->update($request->only([
            'display_order', 'gateway', 'bank_name',
            'account_name', 'account_number', 'remark', 'status', 'config',
        ]));

        return response()->json(['success' => true, 'data' => $bank->fresh()]);
    }

    public function bankUpdateAmount(Request $request, $id)
    {
        $request->validate(['balance' => 'required|numeric']);
        $bank = BoBank::findOrFail($id);

        $oldBalance = $bank->balance;
        $newBalance = $request->balance;
        $diff       = $newBalance - $oldBalance;

        $bank->update(['balance' => $newBalance]);

        // Record adjustment in history
        if ($diff != 0) {
            BoBankTransaction::create([
                'bo_bank_id'  => $bank->id,
                'date'        => Carbon::today()->toDateString(),
                'description' => 'Manual balance adjustment: ' . number_format($oldBalance, 2) . ' → ' . number_format($newBalance, 2),
                'amount_in'   => $diff > 0 ? abs($diff) : null,
                'amount_out'  => $diff < 0 ? abs($diff) : null,
                'time'        => Carbon::now()->format('H:i:s'),
                'remarks'     => 'Balance edited by admin',
                'created_by'  => auth()->id(),
            ]);
        }

        return response()->json(['success' => true, 'balance' => $bank->balance]);
    }

    public function bankHistory(Request $request, $id)
    {
        $bank = BoBank::findOrFail($id);
        $rows = $bank->transactions()
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(200)
            ->get();

        return response()->json($rows);
    }

    // ─── Bank Transactions ────────────────────────────────────────────────────────

    public function bankTransactions(Request $request)
    {
        $date   = $request->date ?? Carbon::today()->toDateString();
        $bankId = $request->bank_id;

        if (!$bankId) {
            return response()->json(['error' => 'bank_id required'], 422);
        }

        $bank = BoBank::findOrFail($bankId);

        // starting balance = balance minus today's net
        $todayIn  = BoBankTransaction::where('bo_bank_id', $bankId)
            ->whereDate('date', $date)->sum('amount_in');
        $todayOut = BoBankTransaction::where('bo_bank_id', $bankId)
            ->whereDate('date', $date)->sum('amount_out');
        $todayNet  = $todayIn - $todayOut;
        $startBal  = $bank->balance - $todayNet;

        $query = BoBankTransaction::where('bo_bank_id', $bankId)
            ->whereDate('date', $date);

        if ($request->filled('type') && $request->type !== 'all') {
            if ($request->type === 'in') {
                $query->whereNotNull('amount_in')->where('amount_in', '>', 0);
            } elseif ($request->type === 'out') {
                $query->whereNotNull('amount_out')->where('amount_out', '>', 0);
            }
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('ref_id', 'like', '%' . $request->search . '%');
            });
        }

        $rows    = $query->orderBy('id')->get();
        $totalIn = $rows->sum('amount_in');
        $totalOut= $rows->sum('amount_out');

        return response()->json([
            'bank'      => $bank,
            'date'      => $date,
            'start_bal' => number_format($startBal, 2),
            'today_in'  => number_format($todayIn, 2),
            'balance'   => number_format($bank->balance, 2),
            'rows'      => $rows,
            'total_in'  => number_format($totalIn, 2),
            'total_out' => number_format($totalOut, 2),
        ]);
    }

    public function bankTransactionStore(Request $request)
    {
        $request->validate([
            'bo_bank_id'  => 'required|exists:bo_banks,id',
            'date'        => 'required|date',
        ]);

        $row = BoBankTransaction::create([
            'bo_bank_id'  => $request->bo_bank_id,
            'date'        => $request->date,
            'description' => $request->description,
            'amount_in'   => $request->amount_in ?: null,
            'amount_out'  => $request->amount_out ?: null,
            'time'        => $request->time,
            'ref_id'      => $request->ref_id,
            'match'       => $request->match,
            'fee'         => $request->fee ?: null,
            'remarks'     => $request->remarks,
            'info'        => $request->info,
            'created_by'  => auth()->id(),
        ]);

        // update bank balance
        $bank = BoBank::find($request->bo_bank_id);
        if ($bank) {
            $bank->increment('balance', ($request->amount_in ?? 0) - ($request->amount_out ?? 0));
        }

        return response()->json(['success' => true, 'data' => $row]);
    }

    public function bankTransactionUpdate(Request $request, $id)
    {
        $row = BoBankTransaction::findOrFail($id);

        $oldIn  = $row->amount_in  ?? 0;
        $oldOut = $row->amount_out ?? 0;

        $row->update([
            'description' => $request->description,
            'amount_in'   => $request->amount_in ?: null,
            'amount_out'  => $request->amount_out ?: null,
            'time'        => $request->time,
            'ref_id'      => $request->ref_id,
            'match'       => $request->match,
            'fee'         => $request->fee ?: null,
            'remarks'     => $request->remarks,
            'info'        => $request->info,
        ]);

        // adjust bank balance for the diff
        $newIn  = $request->amount_in  ?? 0;
        $newOut = $request->amount_out ?? 0;
        $diff   = ($newIn - $newOut) - ($oldIn - $oldOut);
        if ($diff != 0) {
            BoBank::where('id', $row->bo_bank_id)->increment('balance', $diff);
        }

        return response()->json(['success' => true, 'data' => $row->fresh()]);
    }

    public function bankTransactionDelete(Request $request, $id)
    {
        $row = BoBankTransaction::findOrFail($id);

        // reverse balance effect
        $diff = ($row->amount_in ?? 0) - ($row->amount_out ?? 0);
        if ($diff != 0) {
            BoBank::where('id', $row->bo_bank_id)->decrement('balance', $diff);
        }

        $row->delete();

        return response()->json(['success' => true]);
    }

    // ─── Cashflow / Reports ───────────────────────────────────────────────────────

    public function cashflow(Request $request)
    {
        $from    = $request->date_from ?? Carbon::now()->startOfMonth()->toDateString();
        $to      = $request->date_to   ?? Carbon::now()->endOfMonth()->toDateString();
        $display = $request->display   ?? 'daily';   // daily | monthly | yearly
        $type    = $request->type      ?? 'all';

        $query = BoTransaction::whereBetween(DB::raw('DATE(transacted_at)'), [$from, $to]);
        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $groupFormat = match ($display) {
            'monthly' => '%Y-%m',
            'yearly'  => '%Y',
            default   => '%Y-%m-%d',
        };

        $rows = $query->select(
                DB::raw("DATE_FORMAT(transacted_at, '{$groupFormat}') as period"),
                DB::raw("SUM(CASE WHEN amount > 0 THEN 1 ELSE 0 END) as deposit_count"),
                DB::raw("SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as deposit_amount"),
                DB::raw("SUM(CASE WHEN amount < 0 THEN 1 ELSE 0 END) as withdraw_count"),
                DB::raw("SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as withdraw_amount"),
                DB::raw("SUM(amount) as net")
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $totals = [
            'deposit_count'   => $rows->sum('deposit_count'),
            'deposit_amount'  => number_format($rows->sum('deposit_amount'), 2),
            'withdraw_count'  => $rows->sum('withdraw_count'),
            'withdraw_amount' => number_format($rows->sum('withdraw_amount'), 2),
            'net'             => number_format($rows->sum('net'), 2),
        ];

        return response()->json(['rows' => $rows, 'totals' => $totals]);
    }

    public function cashflowBank(Request $request)
    {
        $from    = $request->date_from ?? Carbon::now()->startOfMonth()->toDateString();
        $to      = $request->date_to   ?? Carbon::now()->endOfMonth()->toDateString();
        $display = $request->display   ?? 'daily';

        $query = BoBankTransaction::whereBetween('date', [$from, $to]);

        if ($request->filled('bank_id')) {
            $query->where('bo_bank_id', $request->bank_id);
        }

        $groupFormat = match ($display) {
            'monthly' => '%Y-%m',
            'yearly'  => '%Y',
            default   => '%Y-%m-%d',
        };

        $rows = $query->select(
                DB::raw("DATE_FORMAT(date, '{$groupFormat}') as period"),
                DB::raw('COALESCE(SUM(amount_in), 0) as total_in'),
                DB::raw('COALESCE(SUM(amount_out), 0) as total_out'),
                DB::raw('COALESCE(SUM(amount_in), 0) - COALESCE(SUM(amount_out), 0) as net')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $totals = [
            'total_in'  => number_format($rows->sum('total_in'), 2),
            'total_out' => number_format($rows->sum('total_out'), 2),
            'net'       => number_format($rows->sum('net'), 2),
        ];

        return response()->json(['rows' => $rows, 'totals' => $totals]);
    }

    public function cashflowStaff(Request $request)
    {
        $from = $request->date_from ?? Carbon::now()->startOfMonth()->toDateString();
        $to   = $request->date_to   ?? Carbon::now()->endOfMonth()->toDateString();

        $baseQuery = BoTransaction::whereBetween(DB::raw('DATE(transacted_at)'), [$from, $to]);

        // Deposit stats by agent
        $depositRows = (clone $baseQuery)->where('amount', '>', 0)
            ->select(
                'agent_username as staff',
                DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as total_approval"),
                DB::raw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as total_reject"),
                DB::raw("AVG(TIMESTAMPDIFF(SECOND, transacted_at, updated_at)) as avg_response"),
                DB::raw("AVG(TIMESTAMPDIFF(SECOND, transacted_at, updated_at)) as avg_process")
            )
            ->groupBy('agent_username')
            ->get()
            ->map(fn($r) => [
                'staff'          => $r->staff ?? '-',
                'total_approval' => $r->total_approval,
                'total_reject'   => $r->total_reject,
                'response'       => round($r->avg_response ?? 0) . 's',
                'process'        => round($r->avg_process ?? 0) . 's',
            ]);

        $depositTotals = [
            'total_approval' => $depositRows->sum('total_approval'),
            'total_reject'   => $depositRows->sum('total_reject'),
            'response'       => $depositRows->count() ? round($depositRows->avg(fn($r) => (int)$r['response'])) . 's' : '0s',
            'process'        => $depositRows->count() ? round($depositRows->avg(fn($r) => (int)$r['process'])) . 's' : '0s',
        ];

        // Withdraw stats by agent
        $withdrawRows = (clone $baseQuery)->where('amount', '<', 0)
            ->select(
                'agent_username as staff',
                DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as total_approval"),
                DB::raw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as total_reject"),
                DB::raw("AVG(TIMESTAMPDIFF(SECOND, transacted_at, updated_at)) as avg_response"),
                DB::raw("AVG(TIMESTAMPDIFF(SECOND, transacted_at, updated_at)) as avg_process")
            )
            ->groupBy('agent_username')
            ->get()
            ->map(fn($r) => [
                'staff'          => $r->staff ?? '-',
                'total_approval' => $r->total_approval,
                'total_reject'   => $r->total_reject,
                'response'       => round($r->avg_response ?? 0) . 's',
                'process'        => round($r->avg_process ?? 0) . 's',
            ]);

        $withdrawTotals = [
            'total_approval' => $withdrawRows->sum('total_approval'),
            'total_reject'   => $withdrawRows->sum('total_reject'),
            'response'       => $withdrawRows->count() ? round($withdrawRows->avg(fn($r) => (int)$r['response'])) . 's' : '0s',
            'process'        => $withdrawRows->count() ? round($withdrawRows->avg(fn($r) => (int)$r['process'])) . 's' : '0s',
        ];

        return response()->json([
            'deposit'  => ['rows' => $depositRows, 'totals' => $depositTotals],
            'withdraw' => ['rows' => $withdrawRows, 'totals' => $withdrawTotals],
        ]);
    }

    public function cashflowActivity(Request $request)
    {
        $from   = $request->date_from ?? Carbon::now()->startOfMonth()->toDateString();
        $to     = $request->date_to   ?? Carbon::now()->endOfMonth()->toDateString();
        $action = $request->action;

        $query = \Spatie\Activitylog\Models\Activity::whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->orderBy('created_at', 'desc');

        if ($action) {
            $query->where('description', $action);
        }

        $paginated = $query->paginate(50);

        $data = collect($paginated->items())->map(function ($log) {
            $causer = $log->causer;
            $props  = $log->properties ?? collect();
            $attrs  = $props->get('attributes', []);

            return [
                'date_time'   => $log->created_at->format('Y-m-d H:i:s'),
                'username'    => $causer ? ($causer->name ?? '-') : '-',
                'player_name' => $attrs['fullname'] ?? $attrs['name'] ?? ($log->subject ? ($log->subject->fullname ?? $log->subject->name ?? '-') : '-'),
                'mobile'      => $attrs['phone_number'] ?? ($log->subject ? ($log->subject->phone_number ?? '-') : '-'),
                'action_by'   => $causer ? ($causer->fullname ?? $causer->name ?? '-') : '-',
                'description' => $log->description,
            ];
        });

        return response()->json([
            'data'    => $data,
            'current' => $paginated->currentPage(),
            'pages'   => $paginated->lastPage(),
        ]);
    }

    // ─── Customers ───────────────────────────────────────────────────────────────

    public function customers(Request $request)
    {
        return response()->json(
            FxCustomer::where('status', 'active')->orderBy('id')->get(['id', 'name', 'initial_balance', 'status', 'check_today'])
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

    public function customer(Request $request, $id)
    {
        $customer = FxCustomer::findOrFail($id);

        $transactions = FxTransaction::where('fx_customer_id', $id)
            ->orderBy('date')->orderBy('id')
            ->get();

        $totalMyrIn  = $transactions->sum('myr_in');
        $totalMyrOut = $transactions->sum('myr_out');
        $totalMyrConverted = $transactions->sum('myr_converted');
        $balance = $customer->initial_balance + $totalMyrConverted - $totalMyrOut + $totalMyrIn;

        return response()->json([
            'customer'     => $customer,
            'transactions' => $transactions,
            'summary'      => [
                'total_myr_in'  => number_format($totalMyrIn, 2),
                'total_myr_out' => number_format($totalMyrOut, 2),
                'balance'       => number_format($balance, 2),
            ],
        ]);
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

    public function customerDelete(int $id)
    {
        FxCustomer::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function customerTxStore(Request $request)
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
            'myr_converted'  => $rate * ($amountIn - $amountOut),
            'myr_out'        => (float) ($request->myr_out ?? 0),
            'myr_in'         => (float) ($request->myr_in  ?? 0),
            'remark'         => $request->remark,
            'cost_rate'      => $costRate,
            'profit'         => $amountIn * ($costRate - $rate),
            'created_by'     => auth()->id(),
        ]);

        return response()->json(['success' => true, 'data' => $tx]);
    }

    public function customerTxUpdate(Request $request, $id)
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
            'myr_converted' => $rate * ($amountIn - $amountOut),
            'myr_out'       => (float) ($request->myr_out ?? $tx->myr_out),
            'myr_in'        => (float) ($request->myr_in  ?? $tx->myr_in),
            'remark'        => $request->remark        ?? $tx->remark,
            'cost_rate'     => $costRate,
            'profit'        => $amountIn * ($costRate - $rate),
        ]);

        return response()->json(['success' => true, 'data' => $tx->fresh()]);
    }

    // ─── Roles ────────────────────────────────────────────────────────────────────

    public function roles(Request $request)
    {
        $roles = Role::select('id', 'name')->orderBy('name')->get();

        return response()->json($roles);
    }

    // ─── Admins ───────────────────────────────────────────────────────────────────

    public function admins(Request $request)
    {
        $query = Administrator::query();

        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('fullname', 'like', '%' . $request->name . '%')
                  ->orWhere('name', 'like', '%' . $request->name . '%');
            });
        }
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $admins = $query->select('id', 'name', 'fullname', 'email', 'phone_number', 'role', 'status', 'last_login_at', 'last_login_ip', 'created_at', 'updated_at')
            ->orderBy('id')
            ->get()
            ->map(function ($a) {
                return [
                    'id'            => $a->id,
                    'username'      => $a->name,
                    'name'          => $a->fullname,
                    'email'         => $a->email,
                    'phone_number'  => $a->phone_number,
                    'created_by'    => '-',
                    'last_login'    => $a->last_login_at ? $a->last_login_at->format('Y-m-d H:i') : '-',
                    'last_login_ip' => $a->last_login_ip ?? '-',
                    'role'          => $a->role,
                    'status'        => $a->status,
                ];
            });

        return response()->json($admins);
    }

    public function adminStore(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:administrators,name|max:100',
            'fullname' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'role'     => 'required|string',
        ]);

        $admin = Administrator::create([
            'name'         => $request->username,
            'fullname'     => $request->fullname,
            'email'        => $request->email ?? ($request->username . '@bo.local'),
            'phone_number' => $request->phone_number,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'status'       => $request->status,
        ]);

        return response()->json(['success' => true, 'data' => $admin]);
    }

    public function adminUpdate(Request $request, $id)
    {
        $admin = Administrator::findOrFail($id);
        $data  = $request->only(['fullname', 'phone_number', 'role', 'status']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $admin->update($data);

        return response()->json(['success' => true]);
    }

    // ─── Settings (menu sidebar) ──────────────────────────────────────────────────

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        $admin = auth()->user();
        if (!Hash::check($request->current_password, $admin->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $admin->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['success' => true]);
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true, 'redirect' => route('admin.login')]);
    }
}
