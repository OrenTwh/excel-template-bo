<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Models\{FxCustomer, FxTransaction, FxArrangement};

/**
 * FxSpreadsheetService
 *
 * Direct conversion of Google Sheets formulas to Laravel/PHP.
 *
 * ─── User sheet column reference ─────────────────────────────────────────────
 *   A  date            B  currency
 *   C  amount_in       D  amount_out     E  rate
 *   F  myr_converted   G  myr_out        H  myr_in       I  remark
 *   K  cost_rate       L  profit
 *   N  (arrangement date)  O account_number  P beneficiary_name  Q bank
 *   R  arranging_amount    S done_amount      T is_done           U processed_by
 * ─────────────────────────────────────────────────────────────────────────────
 */
class FxSpreadsheetService
{
    // ═══════════════════════════════════════════════════════════════════════════
    // PER-ROW CALCULATIONS  (per transaction formulas)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Sheet:  F7 = E7 * (C7 - D7)
     * Label:  CONVERT TO MYR
     * Logic:  exchange_rate × (buy_in − sell_out)
     */
    public function myrConverted(float $rate, float $amountIn, float $amountOut): float
    {
        return $rate * ($amountIn - $amountOut);
    }

    /**
     * Sheet:  L7 = C7 * (K7 - E7)
     * Label:  PROFIT
     * Logic:  buy_amount × (cost_rate − exchange_rate)
     */
    public function profit(float $amountIn, float $costRate, float $rate): float
    {
        return $amountIn * ($costRate - $rate);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // USER SHEET SUMMARY  (single customer)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Sheet:  B2 = SUM(F7:F1001, H7:H1001) - SUM(G7:G1001) + I1
     * Label:  BALANCE (current MYR balance)
     * Logic:  total myr_converted + total myr_in − total myr_out + initial_balance
     */
    public function customerBalance(FxCustomer $customer): float
    {
        $totals = FxTransaction::where('fx_customer_id', $customer->id)
            ->selectRaw('
                SUM(myr_converted) as total_converted,
                SUM(myr_in)        as total_myr_in,
                SUM(myr_out)       as total_myr_out
            ')
            ->first();

        return (float) $totals->total_converted
             + (float) $totals->total_myr_in
             - (float) $totals->total_myr_out
             + (float) $customer->initial_balance;
    }

    /**
     * Sheet:  C6 = IF(L1, SUMIF(A:A, TODAY(), C:C), SUM(C7:C9998))
     *         D6 = IF(L1, SUMIF(A:A, TODAY(), D:D), SUM(D7:D9998))
     *         F6 = IF(L1, SUMIF(A:A, TODAY(), F:F), SUM(F7:F9998))
     *         G6 = IF(L1, SUMIF(A:A, TODAY(), G:G), SUM(G7:G9998))
     *         H6 = IF(L1, SUMIF(A:A, TODAY(), H:H), SUM(H7:H9998))
     * Label:  TOTAL row — switches between today-only and all-time
     * Logic:  if check_today=true → SUM where date = today, else → SUM all rows
     *
     * Returns array matching the TOTAL row:
     *   amount_in, amount_out, myr_converted, myr_out, myr_in
     */
    public function customerTotals(FxCustomer $customer): array
    {
        $query = FxTransaction::where('fx_customer_id', $customer->id);

        if ($customer->check_today) {
            // SUMIF(A:A, TODAY(), …)
            $query->whereDate('date', Carbon::today());
        }

        $row = $query->selectRaw('
            SUM(amount_in)     as amount_in,
            SUM(amount_out)    as amount_out,
            SUM(myr_converted) as myr_converted,
            SUM(myr_out)       as myr_out,
            SUM(myr_in)        as myr_in
        ')->first();

        return [
            'amount_in'     => (float) $row->amount_in,
            'amount_out'    => (float) $row->amount_out,
            'myr_converted' => (float) $row->myr_converted,
            'myr_out'       => (float) $row->myr_out,
            'myr_in'        => (float) $row->myr_in,
        ];
    }

    /**
     * Sheet:  L6 = SUM(L7:L9998)
     * Label:  TOTAL PROFIT (always all-time, ignores check_today)
     */
    public function customerTotalProfit(FxCustomer $customer): float
    {
        return (float) FxTransaction::where('fx_customer_id', $customer->id)
            ->sum('profit');
    }

    /**
     * Sheet:  O4 = R5 - S5
     *         R5 = SUM(R6:R11001)   — total arranging amount
     *         S5 = SUM(S6:S11019)   — total done amount
     * Label:  PENDING MYR
     * Logic:  total_arranging − total_done
     */
    public function customerPendingMyr(FxCustomer $customer): float
    {
        $row = FxArrangement::where('fx_customer_id', $customer->id)
            ->selectRaw('
                SUM(arranging_amount) as total_arranging,
                SUM(done_amount)      as total_done
            ')
            ->first();

        return (float) $row->total_arranging - (float) $row->total_done;
    }

    /**
     * Full user-sheet summary for one customer.
     * Combines balance, totals, profit, pending into one payload.
     */
    public function customerSummary(FxCustomer $customer): array
    {
        return [
            'name'           => $customer->name,
            'balance'        => $this->customerBalance($customer),       // B2
            'pending_myr'    => $this->customerPendingMyr($customer),   // O4
            'total_profit'   => $this->customerTotalProfit($customer),  // L6
            'totals'         => $this->customerTotals($customer),        // row 6
            'check_today'    => $customer->check_today,                  // L1
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // MASTER SHEET — LEFT SECTION  (daily rows, 总表 cols B–T)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Sheet:  C3:L3 = SUM(C4:C34)  (monthly currency totals)
     * Logic:  group transactions by currency for the given year+month,
     *         return sum of amount_in per currency.
     */
    public function monthlyCurrencyTotals(int $year, int $month): Collection
    {
        return FxTransaction::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('currency, SUM(amount_in) as total_in, SUM(amount_out) as total_out')
            ->groupBy('currency')
            ->get();
    }

    /**
     * Sheet:  S4 = R4 - S2   (day 1 profit vs base)
     *         S5 = R5 - R4   (subsequent days: daily change)
     * Label:  PROFIT (daily MYR profit delta)
     * Logic:  returns daily MYR balance (myr_converted + myr_in - myr_out) per day
     *         for the given month, so the caller can compute day-over-day delta.
     */
    public function dailyMyrBalance(int $year, int $month): Collection
    {
        return FxTransaction::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw("
                date,
                SUM(myr_converted) + SUM(myr_in) - SUM(myr_out) as daily_myr,
                SUM(myr_converted) as myr_converted,
                SUM(myr_in)        as myr_in,
                SUM(myr_out)       as myr_out
            ")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($row, $index, $collection = null) {
                return $row; // caller computes rolling delta: S_n = R_n - R_{n-1}
            });
    }

    /**
     * Sheet:  T4 = IFERROR(S4 / SUM(C4:L4), 0)
     * Label:  PROFIT RATE
     * Logic:  daily_profit / total_currency_volume_for_that_day
     *         Returns 0 if volume is 0.
     */
    public function profitRate(float $dailyProfit, float $totalVolume): float
    {
        if ($totalVolume == 0) return 0.0;
        return $dailyProfit / $totalVolume;
    }

    /**
     * Full daily rows for the master sheet left section.
     * Equivalent to rows 4–34 of 总表 (B, C:L totals, S profit, T profit rate).
     */
    public function masterDailyRows(int $year, int $month): array
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $rows = [];

        // Sum per day per currency
        $txByDay = FxTransaction::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw("
                DAY(date) as day,
                currency,
                SUM(amount_in)                                    as amount_in,
                SUM(myr_converted) + SUM(myr_in) - SUM(myr_out)  as myr_balance,
                SUM(myr_converted)                                as myr_converted,
                SUM(amount_in) + SUM(amount_out)                  as volume
            ")
            ->groupBy(DB::raw('DAY(date)'), 'currency')
            ->get()
            ->groupBy('day');

        $prevBalance = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayData     = $txByDay->get($day, collect());
            $myrBalance  = $dayData->sum('myr_balance');
            $totalVolume = $dayData->sum('volume');

            // S col: day 1 = myrBalance (vs 0 base), subsequent = delta from previous day
            $profit = $day === 1 ? $myrBalance : ($myrBalance - $prevBalance);

            $rows[] = [
                'day'         => $day,
                'date'        => Carbon::create($year, $month, $day)->toDateString(),
                'currencies'  => $dayData->pluck('amount_in', 'currency'),  // C:L equivalent
                'myr_balance' => $myrBalance,                                // R col
                'profit'      => $profit,                                    // S col
                'profit_rate' => $this->profitRate($profit, $totalVolume),   // T col
                'volume'      => $totalVolume,
            ];

            $prevBalance = $myrBalance;
        }

        return $rows;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // MASTER SHEET — RIGHT SECTION  (customer rows, 总表 cols X–AI)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Sheet:  Z4  = INDIRECT("'"&Y4&"'!B2")          → customer balance
     *         AB4 = INDIRECT("'"&Y4&"'!C6")          → BUY(IN) total
     *         AC4 = INDIRECT("'"&Y4&"'!D6")          → SELL(OUT) total
     *         AD4 = COUNTIFS(C7:C10000, ">0")        → count of BUY IN transactions
     *         AE4 = COUNTIFS(D7:D10000, ">0")        → count of SELL OUT transactions
     *         AG4 = SUMIFS(L:L, A:A, specific_date)  → profit on a specific date
     *         AH4 = COUNTIFS(A:A, date, C:C, ">0")   → BUY IN count on date
     *         AI4 = IF(any tx on date in C/D/G/H, 1, 0) → ACTIVE flag on date
     *
     * Returns the full customer row for the master sheet right section.
     * Pass $date for the AG/AH/AI "DATE#" columns, or null to skip.
     */
    public function masterCustomerRow(FxCustomer $customer, ?Carbon $date = null): array
    {
        $base = FxTransaction::where('fx_customer_id', $customer->id);

        // Z: balance (B2)
        $balance = $this->customerBalance($customer);

        // AB / AC: total buy_in / sell_out (C6 / D6 — uses check_today toggle)
        $totals = $this->customerTotals($customer);

        // AD: COUNTIFS(C7:C10000, ">0") — total BUY IN transaction count
        $countBuyIn = (clone $base)->where('amount_in', '>', 0)->count();

        // AE: COUNTIFS(D7:D10000, ">0") — total SELL OUT transaction count
        $countSellOut = (clone $base)->where('amount_out', '>', 0)->count();

        $row = [
            'customer_id'   => $customer->id,
            'name'          => $customer->name,             // Y col
            'balance'       => $balance,                    // Z col
            'buy_in'        => $totals['amount_in'],        // AB col
            'sell_out'      => $totals['amount_out'],       // AC col
            'count_buy_in'  => $countBuyIn,                 // AD col
            'count_sell_out'=> $countSellOut,               // AE col
        ];

        if ($date !== null) {
            $onDate = (clone $base)->whereDate('date', $date);

            // AG: SUMIFS(L:L, A:A, date) — profit on specific date
            $row['profit_on_date'] = (float) (clone $onDate)->sum('profit');

            // AH: COUNTIFS(A:A, date, C:C, ">0") — buy_in count on date
            $row['buy_in_on_date'] = (clone $onDate)->where('amount_in', '>', 0)->count();

            // AI: IF(any of amount_in/amount_out/myr_out/myr_in > 0 on date, 1, 0)
            $row['active_on_date'] = (clone $onDate)
                ->where(function ($q) {
                    $q->where('amount_in',  '>', 0)
                      ->orWhere('amount_out', '>', 0)
                      ->orWhere('myr_out',    '>', 0)
                      ->orWhere('myr_in',     '>', 0);
                })
                ->exists() ? 1 : 0;
        }

        return $row;
    }

    /**
     * All customer rows for the master sheet right section.
     */
    public function masterCustomerRows(?Carbon $date = null): array
    {
        return FxCustomer::where('status', 'active')
            ->orderBy('id')
            ->get()
            ->map(fn($c) => $this->masterCustomerRow($c, $date))
            ->values()
            ->toArray();
    }

    /**
     * Sheet:  U1 = SUM(Z4:Z29)
     * Label:  NEEDPAY — sum of all customer balances
     */
    public function totalNeedPay(): float
    {
        return (float) FxCustomer::where('status', 'active')
            ->get()
            ->sum(fn($c) => $this->customerBalance($c));
    }

    /**
     * Sheet:  AB1 = AB3 - AC3  (net BUY: total in - total out)
     *         AD1 = AD3 - AE3  (net TRX count: in count - out count)
     */
    public function masterHeaderTotals(): array
    {
        $totals = FxTransaction::selectRaw('
            SUM(amount_in)                       as total_buy_in,
            SUM(amount_out)                      as total_sell_out,
            SUM(CASE WHEN amount_in  > 0 THEN 1 ELSE 0 END) as count_buy_in,
            SUM(CASE WHEN amount_out > 0 THEN 1 ELSE 0 END) as count_sell_out
        ')->first();

        return [
            'net_buy'       => (float)$totals->total_buy_in - (float)$totals->total_sell_out,  // AB1
            'net_trx_count' => (int)$totals->count_buy_in  - (int)$totals->count_sell_out,    // AD1
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DETAIL SHEET  (总表详情 — customer × date matrix)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Sheet:  F4 = COUNTIFS(INDIRECT("'"&$C4&"'!A:A"), F$2, INDIRECT(…C:C), ">0")
     * Label:  Buy-in transaction count per customer per date
     *
     * Sheet:  AL4 = IF(any tx on date in C/D/G/H > 0, 1, 0)
     * Label:  ACTIVE on date per customer
     *
     * Sheet:  BR4 = SUMIFS(INDIRECT(…L:L), INDIRECT(…A:A), BR$2)
     * Label:  Profit per customer per date
     *
     * Returns a matrix: customers × dates with buy_in_count, active, profit.
     */
    public function detailMatrix(int $year, int $month): array
    {
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to   = $from->copy()->endOfMonth();

        // Aggregate all rows in one query
        $rows = FxTransaction::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw("
                fx_customer_id,
                DATE(date) as date,
                SUM(CASE WHEN amount_in > 0 THEN 1 ELSE 0 END) as buy_in_count,
                SUM(profit)                                      as profit,
                MAX(CASE WHEN amount_in > 0 OR amount_out > 0 OR myr_out > 0 OR myr_in > 0 THEN 1 ELSE 0 END) as active
            ")
            ->groupBy('fx_customer_id', 'date')
            ->get()
            ->groupBy('fx_customer_id');

        $customers = FxCustomer::where('status', 'active')->orderBy('id')->get();
        $dates     = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $dates[] = $d->toDateString();
        }

        // Build matrix
        $matrix = [];
        foreach ($customers as $customer) {

            $customerRows = $rows->get($customer->id, collect())
                ->keyBy(function ($item) {
                    return \Carbon\Carbon::parse($item->date)->toDateString();
                });
            $dateData     = [];

            foreach ($dates as $date) {
                $tx = $customerRows->get($date);
                $dateData[$date] = [
                    'buy_in_count' => $tx ? (int) $tx->buy_in_count : 0,  // F col
                    'active'       => $tx ? (int) $tx->active       : 0,  // AL col
                    'profit'       => $tx ? (float) $tx->profit     : 0,  // BR col
                ];
            }

            $matrix[] = [
                'customer_id'   => $customer->id,
                'customer_name' => $customer->name,
                'dates'         => $dateData,
            ];
        }

        return [
            'dates'    => $dates,
            'customers'=> $matrix,
        ];
    }
}
