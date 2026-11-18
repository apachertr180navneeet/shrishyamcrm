<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\NumberSeriesService;
use Carbon\Carbon;

class ResetAgentAndMemberDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:reset-agents-members';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up all Agent and Member data, reset series to 1 and fix relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database cleanup for Members and Agents...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // 1. Unlink agent_id from users table (preserves user accounts)
            DB::table('users')->update(['agent_id' => null]);
            $this->info('Cleared agent_id references from users table.');

            // 2. Clear transactional and related data
            $tablesToClear = [
                'nominees',
                'certificates',
                'ledgers',
                'payments',
                'payouts',
                'event_contributions',
                'event_billings',
                'marriage_events',
                'member_documents',
                'agent_commissions',
                'whatsapp_logs',
                'audit_logs',
                'members',
                'agents',
            ];

            foreach ($tablesToClear as $table) {
                $countBefore = DB::table($table)->count();
                DB::table($table)->truncate();
                $this->info("Truncated table '{$table}' (cleared {$countBefore} records).");
            }

            // 3. Reset auto-increment to 1
            $autoIncrementTables = [
                'agents',
                'members',
                'nominees',
                'certificates',
                'ledgers',
                'payments',
                'payouts',
                'marriage_events',
                'event_contributions',
                'event_billings',
                'agent_commissions',
                'member_documents',
            ];

            foreach ($autoIncrementTables as $table) {
                DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = 1;");
            }
            $this->info('Reset AUTO_INCREMENT series to 1 on all target tables.');

            // 4. Reset Number Series
            $currentYear = Carbon::now()->format('Y');
            $standardSeries = [
                ['code' => 'AGT', 'prefix' => 'AGT-', 'current_value' => 1, 'padding' => 3, 'desc' => 'Sequence for AGT'],
                ['code' => 'MEM', 'prefix' => "MEM-{$currentYear}-", 'current_value' => 1001, 'padding' => 4, 'desc' => 'Sequence for MEM'],
                ['code' => 'REC', 'prefix' => "REC-{$currentYear}-", 'current_value' => 5001, 'padding' => 4, 'desc' => 'Sequence for REC'],
                ['code' => 'CRT', 'prefix' => "CRT-{$currentYear}-", 'current_value' => 8001, 'padding' => 4, 'desc' => 'Sequence for CRT'],
                ['code' => 'EVT', 'prefix' => "EVT-{$currentYear}-", 'current_value' => 1, 'padding' => 2, 'desc' => 'Sequence for EVT'],
                ['code' => 'PAY', 'prefix' => "PAY-{$currentYear}-", 'current_value' => 1, 'padding' => 3, 'desc' => 'Sequence for PAY'],
                ['code' => 'TXN', 'prefix' => "TXN-{$currentYear}-", 'current_value' => 10001, 'padding' => 5, 'desc' => 'Sequence for TXN'],
            ];

            foreach ($standardSeries as $s) {
                DB::table('number_series')->updateOrInsert(
                    ['code' => $s['code']],
                    [
                        'prefix' => $s['prefix'],
                        'current_value' => $s['current_value'],
                        'padding' => $s['padding'],
                        'year_format' => 'YYYY',
                        'description' => $s['desc'],
                        'updated_at' => now(),
                    ]
                );
            }
            $this->info('Reset number_series table: AGT starts at 1, MEM at 1001.');

            $this->info('Successfully completed Agent and Member data cleanup.');
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        return 0;
    }
}
