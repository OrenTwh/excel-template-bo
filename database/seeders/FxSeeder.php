<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * FxSeeder
 *
 * Seeds dummy FX data following the relationship chain:
 *   FxCustomer → FxTransaction
 *   FxCustomer → FxArrangement
 *
 * Run with:
 *   php artisan db:seed --class=FxSeeder
 */
class FxSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // =====================================================================
        // 1. FX CUSTOMERS
        // =====================================================================
        $customerIds = [];
        $customers = [
            [ 'key' => 'alpha',   'name' => 'Alpha Trading Sdn Bhd',   'initial_balance' => 50000.00 ],
            [ 'key' => 'beta',    'name' => 'Beta Exchange Co',         'initial_balance' => 30000.00 ],
            [ 'key' => 'gamma',   'name' => 'Gamma Forex Enterprise',   'initial_balance' => 75000.00 ],
            [ 'key' => 'delta',   'name' => 'Delta Money Services',     'initial_balance' => 20000.00 ],
            [ 'key' => 'epsilon', 'name' => 'Epsilon Capital Group',    'initial_balance' => 100000.00 ],
        ];

        foreach ($customers as $customer) {
            $key = $customer['key'];
            unset($customer['key']);

            $customerIds[$key] = DB::table('fx_customers')->insertGetId(array_merge($customer, [
                'status'      => 'active',
                'check_today' => false,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]));
        }

        $this->command->info('✔ FX Customers seeded (' . count($customerIds) . ')');

        // =====================================================================
        // 2. FX TRANSACTIONS
        //    myr_converted = rate * (amount_in - amount_out)
        //    profit        = amount_in * (cost_rate - rate)
        // =====================================================================
        $transactions = [
            // Alpha Trading — buys SGD
            [
                'fx_customer_id' => $customerIds['alpha'],
                'date'           => '2026-04-03',
                'currency'       => 'SGD',
                'amount_in'      => 10000.00,
                'amount_out'     => 0.00,
                'rate'           => 3.4500,
                'cost_rate'      => 3.4200,
                'myr_out'        => 0.00,
                'myr_in'         => 34500.00,
                'remark'         => 'April opening buy',
            ],
            [
                'fx_customer_id' => $customerIds['alpha'],
                'date'           => '2026-04-10',
                'currency'       => 'SGD',
                'amount_in'      => 0.00,
                'amount_out'     => 5000.00,
                'rate'           => 3.4600,
                'cost_rate'      => 3.4200,
                'myr_out'        => 17300.00,
                'myr_in'         => 0.00,
                'remark'         => 'Partial sell-back',
            ],
            [
                'fx_customer_id' => $customerIds['alpha'],
                'date'           => '2026-05-05',
                'currency'       => 'USD',
                'amount_in'      => 8000.00,
                'amount_out'     => 0.00,
                'rate'           => 4.7200,
                'cost_rate'      => 4.6900,
                'myr_out'        => 0.00,
                'myr_in'         => 37760.00,
                'remark'         => 'USD purchase May',
            ],

            // Beta Exchange — THB transactions
            [
                'fx_customer_id' => $customerIds['beta'],
                'date'           => '2026-04-07',
                'currency'       => 'THB',
                'amount_in'      => 200000.00,
                'amount_out'     => 0.00,
                'rate'           => 0.1310,
                'cost_rate'      => 0.1290,
                'myr_out'        => 0.00,
                'myr_in'         => 26200.00,
                'remark'         => 'THB bulk buy',
            ],
            [
                'fx_customer_id' => $customerIds['beta'],
                'date'           => '2026-04-21',
                'currency'       => 'THB',
                'amount_in'      => 0.00,
                'amount_out'     => 100000.00,
                'rate'           => 0.1320,
                'cost_rate'      => 0.1290,
                'myr_out'        => 13200.00,
                'myr_in'         => 0.00,
                'remark'         => 'THB partial sell',
            ],
            [
                'fx_customer_id' => $customerIds['beta'],
                'date'           => '2026-05-12',
                'currency'       => 'USDT',
                'amount_in'      => 5000.00,
                'amount_out'     => 0.00,
                'rate'           => 4.7100,
                'cost_rate'      => 4.6800,
                'myr_out'        => 0.00,
                'myr_in'         => 23550.00,
                'remark'         => 'USDT purchase',
            ],

            // Gamma Forex — mixed currencies
            [
                'fx_customer_id' => $customerIds['gamma'],
                'date'           => '2026-04-02',
                'currency'       => 'AUD',
                'amount_in'      => 15000.00,
                'amount_out'     => 0.00,
                'rate'           => 3.0500,
                'cost_rate'      => 3.0100,
                'myr_out'        => 0.00,
                'myr_in'         => 45750.00,
                'remark'         => 'AUD fast rate',
            ],
            [
                'fx_customer_id' => $customerIds['gamma'],
                'date'           => '2026-04-15',
                'currency'       => 'USD',
                'amount_in'      => 12000.00,
                'amount_out'     => 2000.00,
                'rate'           => 4.7300,
                'cost_rate'      => 4.7000,
                'myr_out'        => 9460.00,
                'myr_in'         => 56760.00,
                'remark'         => 'USD net position',
            ],
            [
                'fx_customer_id' => $customerIds['gamma'],
                'date'           => '2026-05-08',
                'currency'       => 'SGD',
                'amount_in'      => 20000.00,
                'amount_out'     => 5000.00,
                'rate'           => 3.4700,
                'cost_rate'      => 3.4400,
                'myr_out'        => 17350.00,
                'myr_in'         => 69400.00,
                'remark'         => 'SGD net trade May',
            ],

            // Delta Money Services
            [
                'fx_customer_id' => $customerIds['delta'],
                'date'           => '2026-04-09',
                'currency'       => 'SGD',
                'amount_in'      => 5000.00,
                'amount_out'     => 0.00,
                'rate'           => 3.4400,
                'cost_rate'      => 3.4100,
                'myr_out'        => 0.00,
                'myr_in'         => 17200.00,
                'remark'         => 'Small SGD purchase',
            ],
            [
                'fx_customer_id' => $customerIds['delta'],
                'date'           => '2026-05-01',
                'currency'       => 'THB',
                'amount_in'      => 80000.00,
                'amount_out'     => 30000.00,
                'rate'           => 0.1300,
                'cost_rate'      => 0.1280,
                'myr_out'        => 3900.00,
                'myr_in'         => 10400.00,
                'remark'         => 'THB net trade',
            ],

            // Epsilon Capital — large volumes
            [
                'fx_customer_id' => $customerIds['epsilon'],
                'date'           => '2026-04-01',
                'currency'       => 'USD',
                'amount_in'      => 50000.00,
                'amount_out'     => 0.00,
                'rate'           => 4.7000,
                'cost_rate'      => 4.6700,
                'myr_out'        => 0.00,
                'myr_in'         => 235000.00,
                'remark'         => 'USD bulk April opening',
            ],
            [
                'fx_customer_id' => $customerIds['epsilon'],
                'date'           => '2026-04-18',
                'currency'       => 'SGD',
                'amount_in'      => 30000.00,
                'amount_out'     => 10000.00,
                'rate'           => 3.4600,
                'cost_rate'      => 3.4300,
                'myr_out'        => 34600.00,
                'myr_in'         => 103800.00,
                'remark'         => 'SGD net mid-April',
            ],
            [
                'fx_customer_id' => $customerIds['epsilon'],
                'date'           => '2026-05-03',
                'currency'       => 'USDT',
                'amount_in'      => 20000.00,
                'amount_out'     => 5000.00,
                'rate'           => 4.7200,
                'cost_rate'      => 4.6900,
                'myr_out'        => 23600.00,
                'myr_in'         => 94400.00,
                'remark'         => 'USDT net May',
            ],
            [
                'fx_customer_id' => $customerIds['epsilon'],
                'date'           => '2026-05-14',
                'currency'       => 'AUD',
                'amount_in'      => 25000.00,
                'amount_out'     => 0.00,
                'rate'           => 3.0600,
                'cost_rate'      => 3.0200,
                'myr_out'        => 0.00,
                'myr_in'         => 76500.00,
                'remark'         => 'AUD slow rate May',
            ],
        ];

        foreach ($transactions as $tx) {
            $amountIn  = (float) $tx['amount_in'];
            $amountOut = (float) $tx['amount_out'];
            $rate      = (float) $tx['rate'];
            $costRate  = (float) $tx['cost_rate'];

            DB::table('fx_transactions')->insert(array_merge($tx, [
                'myr_converted' => round($rate * ($amountIn - $amountOut), 2),
                'profit'        => round($amountIn * ($costRate - $rate), 2),
                'created_by'    => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]));
        }

        $this->command->info('✔ FX Transactions seeded (' . count($transactions) . ')');

        // =====================================================================
        // 3. FX ARRANGEMENTS
        // =====================================================================
        $arrangements = [
            // Alpha Trading
            [
                'fx_customer_id'   => $customerIds['alpha'],
                'date'             => '2026-04-03',
                'account_number'   => '1234-5678-9012',
                'beneficiary_name' => 'Alpha Trading Sdn Bhd',
                'bank'             => 'Maybank',
                'arranging_amount' => 34500.00,
                'done_amount'      => 34500.00,
                'is_done'          => true,
                'processed_by'     => 'Sarah',
            ],
            [
                'fx_customer_id'   => $customerIds['alpha'],
                'date'             => '2026-05-05',
                'account_number'   => '1234-5678-9012',
                'beneficiary_name' => 'Alpha Trading Sdn Bhd',
                'bank'             => 'Maybank',
                'arranging_amount' => 37760.00,
                'done_amount'      => 20000.00,
                'is_done'          => false,
                'processed_by'     => 'Sarah',
            ],

            // Beta Exchange
            [
                'fx_customer_id'   => $customerIds['beta'],
                'date'             => '2026-04-07',
                'account_number'   => '9876-5432-1098',
                'beneficiary_name' => 'Beta Exchange Co',
                'bank'             => 'CIMB',
                'arranging_amount' => 26200.00,
                'done_amount'      => 26200.00,
                'is_done'          => true,
                'processed_by'     => 'James',
            ],
            [
                'fx_customer_id'   => $customerIds['beta'],
                'date'             => '2026-05-12',
                'account_number'   => '9876-5432-1098',
                'beneficiary_name' => 'Beta Exchange Co',
                'bank'             => 'CIMB',
                'arranging_amount' => 23550.00,
                'done_amount'      => 0.00,
                'is_done'          => false,
                'processed_by'     => 'James',
            ],

            // Gamma Forex
            [
                'fx_customer_id'   => $customerIds['gamma'],
                'date'             => '2026-04-02',
                'account_number'   => '5566-7788-9900',
                'beneficiary_name' => 'Gamma Forex Enterprise',
                'bank'             => 'Public Bank',
                'arranging_amount' => 45750.00,
                'done_amount'      => 45750.00,
                'is_done'          => true,
                'processed_by'     => 'Lina',
            ],
            [
                'fx_customer_id'   => $customerIds['gamma'],
                'date'             => '2026-05-08',
                'account_number'   => '5566-7788-9901',
                'beneficiary_name' => 'Gamma Forex Enterprise',
                'bank'             => 'Public Bank',
                'arranging_amount' => 52050.00,
                'done_amount'      => 30000.00,
                'is_done'          => false,
                'processed_by'     => 'Lina',
            ],

            // Delta Money Services
            [
                'fx_customer_id'   => $customerIds['delta'],
                'date'             => '2026-04-09',
                'account_number'   => '1122-3344-5566',
                'beneficiary_name' => 'Delta Money Services',
                'bank'             => 'RHB Bank',
                'arranging_amount' => 17200.00,
                'done_amount'      => 17200.00,
                'is_done'          => true,
                'processed_by'     => 'Ahmad',
            ],

            // Epsilon Capital
            [
                'fx_customer_id'   => $customerIds['epsilon'],
                'date'             => '2026-04-01',
                'account_number'   => '7788-9900-1122',
                'beneficiary_name' => 'Epsilon Capital Group',
                'bank'             => 'Hong Leong Bank',
                'arranging_amount' => 235000.00,
                'done_amount'      => 235000.00,
                'is_done'          => true,
                'processed_by'     => 'Rachel',
            ],
            [
                'fx_customer_id'   => $customerIds['epsilon'],
                'date'             => '2026-05-03',
                'account_number'   => '7788-9900-1122',
                'beneficiary_name' => 'Epsilon Capital Group',
                'bank'             => 'Hong Leong Bank',
                'arranging_amount' => 70800.00,
                'done_amount'      => 50000.00,
                'is_done'          => false,
                'processed_by'     => 'Rachel',
            ],
            [
                'fx_customer_id'   => $customerIds['epsilon'],
                'date'             => '2026-05-14',
                'account_number'   => '7788-9900-3344',
                'beneficiary_name' => 'Epsilon Capital Group',
                'bank'             => 'AmBank',
                'arranging_amount' => 76500.00,
                'done_amount'      => 0.00,
                'is_done'          => false,
                'processed_by'     => 'Rachel',
            ],
        ];

        foreach ($arrangements as $arr) {
            DB::table('fx_arrangements')->insert(array_merge($arr, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $this->command->info('✔ FX Arrangements seeded (' . count($arrangements) . ')');

        $this->command->info('');
        $this->command->info('FxSeeder complete.');
        $this->command->info('  5 customers | ' . count($transactions) . ' transactions | ' . count($arrangements) . ' arrangements');
    }
}
