<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * FxSeeder
 *
 * Exact data extracted from public/wallet_accounting.xlsx.
 *
 * Customers:   用户1, 用户2, 用户（3）
 * Period:      May 2026
 *
 * Run:  php artisan db:seed --class=FxSeeder
 * Safe to re-run — truncates all FX tables first.
 */
class FxSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('fx_arrangements')->truncate();
        DB::table('fx_transactions')->truncate();
        DB::table('fx_master_daily')->truncate();
        DB::table('fx_customers')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = now();

        // =====================================================================
        // 1. CUSTOMERS  — exact names and opening balances (I1) from Excel
        // =====================================================================
        $ids = [];
        foreach ([
            ['name' => '用户1',    'initial_balance' => 1586.00,  'check_today' => false],
            ['name' => '用户2',    'initial_balance' => 5782.00,  'check_today' => false],
            ['name' => '用户（3）', 'initial_balance' => 10954.00, 'check_today' => false],
        ] as $c) {
            $ids[$c['name']] = DB::table('fx_customers')->insertGetId([
                'name'            => $c['name'],
                'initial_balance' => $c['initial_balance'],
                'status'          => 'active',
                'check_today'     => $c['check_today'],
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        $this->command->info('✔ Customers: ' . implode(', ', array_keys($ids)));

        // =====================================================================
        // 2. TRANSACTIONS  — exact rows from each user sheet (rows 7+)
        //    Formulas:  myr_converted = rate * (amount_in - amount_out)
        //               profit        = amount_in * (cost_rate - rate)
        // =====================================================================
        $txRows = [

            // ── 用户1 (rows 7–11, all dated 2026-05-20) ───────────────────────
            ['cust' => '用户1',    'date' => '2026-05-20', 'currency' => 'AUD.S',   'amount_in' => 1000,   'amount_out' => 0,    'rate' => 2.63,  'cost_rate' => 2.645, 'myr_out' => 0,    'myr_in' => 0,    'remark' => ''],
            ['cust' => '用户1',    'date' => '2026-05-20', 'currency' => 'PGK',     'amount_in' => 1500,   'amount_out' => 0,    'rate' => 0.80,  'cost_rate' => 0.82,  'myr_out' => 0,    'myr_in' => 0,    'remark' => ''],
            ['cust' => '用户1',    'date' => '2026-05-20', 'currency' => 'SGD',     'amount_in' => 1295,   'amount_out' => 0,    'rate' => 3.05,  'cost_rate' => 3.07,  'myr_out' => 0,    'myr_in' => 0,    'remark' => ''],
            ['cust' => '用户1',    'date' => '2026-05-20', 'currency' => 'AUD.F',   'amount_in' => 0,      'amount_out' => 300,  'rate' => 2.76,  'cost_rate' => 0,     'myr_out' => 0,    'myr_in' => 0,    'remark' => ''],
            ['cust' => '用户1',    'date' => '2026-05-20', 'currency' => 'AUD.F',   'amount_in' => 0,      'amount_out' => 500,  'rate' => 2.76,  'cost_rate' => 0,     'myr_out' => 0,    'myr_in' => 0,    'remark' => ''],

            // ── 用户2 (rows 7–11, all dated 2026-05-20) ───────────────────────
            // MYR row (all zeros) is skipped — blank row in Excel
            ['cust' => '用户2',    'date' => '2026-05-20', 'currency' => 'AUD.S',   'amount_in' => 1050,   'amount_out' => 0,    'rate' => 2.62,  'cost_rate' => 2.65,  'myr_out' => 0,    'myr_in' => 0,    'remark' => ''],
            ['cust' => '用户2',    'date' => '2026-05-20', 'currency' => 'USDT',    'amount_in' => 1800,   'amount_out' => 0,    'rate' => 3.965, 'cost_rate' => 3.975, 'myr_out' => 0,    'myr_in' => 0,    'remark' => ''],
            ['cust' => '用户2',    'date' => '2026-05-20', 'currency' => 'ABA USD', 'amount_in' => 3451.2, 'amount_out' => 0,    'rate' => 3.98,  'cost_rate' => 4.005, 'myr_out' => 0,    'myr_in' => 0,    'remark' => ''],
            ['cust' => '用户2',    'date' => '2026-05-20', 'currency' => 'AUD.F',   'amount_in' => 0,      'amount_out' => 2000, 'rate' => 2.78,  'cost_rate' => 0,     'myr_out' => 0,    'myr_in' => 0,    'remark' => ''],

            // ── 用户（3） (rows 7–11, all dated 2026-05-20) ─────────────────────
            ['cust' => '用户（3）', 'date' => '2026-05-20', 'currency' => 'AUD.F',   'amount_in' => 0,      'amount_out' => 3000, 'rate' => 2.76,  'cost_rate' => 0,     'myr_out' => 0,    'myr_in' => 0,    'remark' => ''],
            ['cust' => '用户（3）', 'date' => '2026-05-20', 'currency' => 'PGK',     'amount_in' => 1500,   'amount_out' => 0,    'rate' => 0.805, 'cost_rate' => 0.82,  'myr_out' => 0,    'myr_in' => 0,    'remark' => ''],
            ['cust' => '用户（3）', 'date' => '2026-05-20', 'currency' => 'USDT',    'amount_in' => 5000,   'amount_out' => 0,    'rate' => 3.97,  'cost_rate' => 4.00,  'myr_out' => 0,    'myr_in' => 0,    'remark' => ''],
            ['cust' => '用户（3）', 'date' => '2026-05-20', 'currency' => 'MYR',     'amount_in' => 0,      'amount_out' => 0,    'rate' => 0,     'cost_rate' => 0,     'myr_out' => 0,    'myr_in' => 7493, 'remark' => ''],
            ['cust' => '用户（3）', 'date' => '2026-05-20', 'currency' => 'MYR',     'amount_in' => 0,      'amount_out' => 0,    'rate' => 0,     'cost_rate' => 0,     'myr_out' => 5793, 'myr_in' => 0,    'remark' => ''],
        ];

        foreach ($txRows as $row) {
            $ai = (float) $row['amount_in'];
            $ao = (float) $row['amount_out'];
            $r  = (float) $row['rate'];
            $cr = (float) $row['cost_rate'];

            DB::table('fx_transactions')->insert([
                'fx_customer_id' => $ids[$row['cust']],
                'date'           => $row['date'],
                'currency'       => $row['currency'],
                'amount_in'      => $ai,
                'amount_out'     => $ao,
                'rate'           => $r,
                'myr_converted'  => round($r * ($ai - $ao), 2),
                'myr_out'        => (float) $row['myr_out'],
                'myr_in'         => (float) $row['myr_in'],
                'remark'         => $row['remark'],
                'cost_rate'      => $cr,
                'profit'         => round($ai * ($cr - $r), 2),
                'created_by'     => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        $this->command->info('✔ Transactions: ' . count($txRows));

        // No arrangements exist in the Excel (arrangement section was empty for all sheets)
        $this->command->info('✔ Arrangements: 0 (none in Excel)');

        // =====================================================================
        // 3. FX MASTER DAILY  — exact rows from 总表 (cols C–L, U=need_pay, V=balance_myr)
        //    Only rows with data are stored; the rest are left as null/0.
        // =====================================================================
        //  date       | sgd | thb | pgk | usdt | aud_slow | aud_fast | need_pay | balance_myr
        $masterRows = [
            ['date' => '2026-05-01', 'need_pay' => 10000, 'balance_myr' => 56435.03],
            ['date' => '2026-05-02', 'need_pay' => 1000,  'balance_myr' => null],
            ['date' => '2026-05-04', 'need_pay' => 10,    'balance_myr' => null],
            ['date' => '2026-05-20', 'aud_slow'  => 2050, 'balance_myr' => null],
        ];

        foreach ($masterRows as $row) {
            [$y, $m, $d] = explode('-', $row['date']);
            DB::table('fx_master_daily')->insert([
                'year'        => (int) $y,
                'month'       => (int) $m,
                'day'         => (int) $d,
                'sgd'         => $row['sgd']      ?? 0,
                'thb'         => $row['thb']      ?? 0,
                'pgk'         => $row['pgk']      ?? 0,
                'usdt'        => $row['usdt']     ?? 0,
                'aud_slow'    => $row['aud_slow'] ?? 0,
                'aud_fast'    => $row['aud_fast'] ?? 0,
                'usd1'        => 0,
                'usd2'        => 0,
                'usd3'        => 0,
                'usd4'        => 0,
                'balance_myr' => $row['balance_myr'] ?? null,
                'need_pay'    => $row['need_pay']    ?? null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        $this->command->info('✔ Master Daily: ' . count($masterRows) . ' rows (May 2026)');
        $this->command->info('');
        $this->command->info('FxSeeder complete — exact data from wallet_accounting.xlsx');
    }
}
