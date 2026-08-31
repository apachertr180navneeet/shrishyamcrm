<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\Ledger;
use App\Services\LedgerService;
use Carbon\Carbon;

class GenerateMonthlyBillingCommand extends Command
{
    protected $signature = 'society:generate-monthly-billing {--month= : Target month in YYYY-MM format}';
    protected $description = 'Generate monthly support contribution dues for all active members';

    public function handle(): int
    {
        $targetMonth = $this->option('month') ?: Carbon::now()->format('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $targetMonth)) {
            $this->error("Invalid month format: {$targetMonth}. Expected YYYY-MM.");
            return 1;
        }

        $monthDate = Carbon::createFromFormat('Y-m', $targetMonth);
        $monthName = $monthDate->format('F Y');
        // Use a copy so the original Carbon is not mutated by startOfMonth()
        $billingDate = $monthDate->copy()->startOfMonth()->toDateString();
        $dueReference = "DUE-{$targetMonth}";

        $this->info("Generating monthly support dues for {$monthName}...");

        $activeMembers = Member::with(['scheme', 'ageSlab'])->where('status', 'Active')->get();
        $count = 0;
        $skipped = 0;

        foreach ($activeMembers as $member) {
            $amount = (float)$member->monthly_support_amount;
            if ($amount <= 0) {
                continue;
            }

            // Duplicate-billing guard: skip members already billed for this month
            $alreadyBilled = Ledger::where('member_id', $member->id)
                ->where('entry_type', 'Monthly Due')
                ->where('reference_no', $dueReference)
                ->exists();

            if ($alreadyBilled) {
                $skipped++;
                continue;
            }

            // Post monthly due entry
            LedgerService::postEntry(
                memberId: $member->id,
                entryType: 'Monthly Due',
                description: "{$monthName} Monthly Scheme Contribution Due",
                debit: $amount,
                credit: 0.0,
                transactionDate: $billingDate,
                agentId: $member->agent_id,
                referenceNo: $dueReference
            );
            $count++;
        }

        $this->info("Successfully generated monthly dues for {$count} active members. Skipped {$skipped} already-billed members.");
        return 0;
    }
}
