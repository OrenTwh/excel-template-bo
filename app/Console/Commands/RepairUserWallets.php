<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Wallet;

class RepairUserWallets extends Command
{
    protected $signature = 'user:repair-wallets
                            {--user= : Repair a specific user by ID}
                            {--dry-run : Preview what would be created without saving}';

    protected $description = 'Create missing wallets (type 1 and 2) for users who did not get them on registration';

    public function handle()
    {
        $walletTypes = [1, 2];
        $isDryRun    = $this->option('dry-run');
        $userId      = $this->option('user');

        if ($isDryRun) {
            $this->warn('Dry-run mode — no wallets will be created.');
        }

        $query = User::query();

        if ($userId) {
            $query->where('id', $userId);
        }

        $created = 0;
        $checked = 0;

        $query->chunk(200, function ($users) use ($walletTypes, $isDryRun, &$created, &$checked) {
            foreach ($users as $user) {
                $checked++;
                $existingTypes = Wallet::where('user_id', $user->id)->pluck('type')->toArray();

                foreach ($walletTypes as $type) {
                    if (!in_array($type, $existingTypes)) {
                        if (!$isDryRun) {
                            Wallet::create([
                                'user_id' => $user->id,
                                'type'    => $type,
                                'balance' => 0,
                            ]);
                        }
                        $this->line("  User #{$user->id} ({$user->email}) — created wallet type {$type}" . ($isDryRun ? ' [dry-run]' : ''));
                        $created++;
                    }
                }
            }
        });

        $this->info("Done. Checked {$checked} user(s), created {$created} missing wallet(s).");

        return 0;
    }
}
