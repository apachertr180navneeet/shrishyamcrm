<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Services\LedgerService;
use Carbon\Carbon;

class GenerateMonthlyBillingCommand extends Command
{
    protected $signature = 'society:generate-monthly-billing {--month= : Target month in YYYY-MM format}';
    protected $description = 'Generate monthly support contribution dues for all active members';

    public function handle(): int
    {
        $targetMonth = $this->option('month') ?: Carbon::now()->format('Y-m');
        $monthDate = Carbon::createFromFormat('Y-m', $targetMonth);
        $monthName = $monthDate->format('F Y');
        $billingDate = $monthDate->startOfMonth()->toDateString();

        $this->info("Generating monthly support dues for {$monthName}...");

        $activeMembers = Member::with(['scheme', 'ageSlab'])->where('status', 'Active')->get();
        $count = 0;

        foreach ($activeMembers as $member) {
            $amount = (float)$member->monthly_support_amount;
            if ($amount > 0) {
                // Post monthly due entry
                LedgerService::postEntry(
                    memberId: $member->id,
                    entryType: 'Monthly Due',
                    description: "{$monthName} Monthly Scheme Contribution Due",
                    debit: $amount,
                    credit: 0.0,
                    transactionDate: $billingDate,
                    agentId: $member->agent_id,
                    referenceNo: "DUE-{$targetMonth}"
                );
                $count++;
            }
        }

        $this->info("Successfully generated monthly dues for {$count} active members.");
        return 0;
    }
}
