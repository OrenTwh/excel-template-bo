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
        $query = BoTransaction::query();

        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', 'like', '%' . $request->transaction_id . '%');
        }
        if ($request->filled('customer')) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_id', 'like', '%' . $request->customer . '%')
                  ->orWhere('customer_phone', 'like', '%' . $request->customer . '%');
            });
        }
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('transacted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transacted_at', '<=', $request->date_to);
        }
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('agent')) {
            $query->where('agent_username', 'like', '%' . $request->agent . '%');
        }
        if ($request->filled('bo_bank_id') && $request->bo_bank_id !== 'all') {
            $query->where('bo_bank_id', $request->bo_bank_id);
        }
        if ($request->filled('other_info')) {
            $query->where('other_info', 'like', '%' . $request->other_info . '%');
        }

        $orderMap = [
            'pending_new' => ['transacted_at', 'desc'],
            'pending_old' => ['transacted_at', 'asc'],
        ];
        [$col, $dir] = $orderMap[$request->status_order ?? 'pending_new'] ?? ['transacted_at', 'desc'];
        $query->orderBy($col, $dir);

        $records = $query->with('bank')->paginate(50);
        $total   = $query->sum('amount');

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
        // placeholder — wire to a CSV/Excel export job as needed
        return response()->json(['message' => 'Export queued.']);
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
        $bank->update(['balance' => $request->balance]);

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

        $admins = $query->select('id', 'name', 'fullname', 'email', 'role', 'status', 'created_at', 'updated_at')
            ->orderBy('id')
            ->get()
            ->map(function ($a) {
                return [
                    'id'         => $a->id,
                    'username'   => $a->name,
                    'name'       => $a->fullname,
                    'created_by' => '-',
                    'last_login' => $a->updated_at ? $a->updated_at->format('Y-m-d H:i') : '-',
                    'role'       => $a->role,
                    'status'     => $a->status,
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
            'name'     => $request->username,
            'fullname' => $request->fullname,
            'email'    => $request->email ?? ($request->username . '@bo.local'),
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'status'   => 'active',
        ]);

        return response()->json(['success' => true, 'data' => $admin]);
    }

    public function adminUpdate(Request $request, $id)
    {
        $admin = Administrator::findOrFail($id);
        $data  = $request->only(['fullname', 'role', 'status']);
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
