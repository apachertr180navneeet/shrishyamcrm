<?php

namespace App\Services;

use App\Models\Ledger;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    /**
     * Post a financial transaction to member/agent ledger and update running balance.
     */
    public static function postEntry(
        int $memberId,
        string $entryType,
        string $description,
        float $debit = 0.0,
        float $credit = 0.0,
        ?string $transactionDate = null,
        ?int $paymentId = null,
        ?int $agentId = null,
        ?string $referenceNo = null,
        ?int $createdBy = null
    ): Ledger {
        return DB::transaction(function () use (
            $memberId,
            $entryType,
            $description,
            $debit,
            $credit,
            $transactionDate,
            $paymentId,
            $agentId,
            $referenceNo,
            $createdBy
        ) {
            $member = Member::lockForUpdate()->findOrFail($memberId);

            // Get last running balance
            $lastLedger = Ledger::where('member_id', $memberId)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $previousBalance = $lastLedger ? (float)$lastLedger->running_balance : 0.0;

            // Balance calculation: Charges (Debits) increase balance, Payments (Credits) decrease balance
            $newBalance = $previousBalance + $debit - $credit;

            $txnNo = NumberSeriesService::getNextNumber('TXN', ['prefix' => 'TXN-' . date('Y') . '-', 'initial_value' => 10001, 'padding' => 5]);

            $ledger = Ledger::create([
                'transaction_no' => $txnNo,
                'member_id' => $member->id,
                'agent_id' => $agentId ?: $member->agent_id,
                'payment_id' => $paymentId,
                'transaction_date' => $transactionDate ?: now()->toDateString(),
                'entry_type' => $entryType,
                'description' => $description,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $newBalance,
                'reference_no' => $referenceNo,
                'created_by' => $createdBy ?: (auth()->check() ? auth()->id() : null),
            ]);

            // Sync member pending amount to running balance
            $member->pending_amount = max(0, $newBalance);
            $member->save();

            return $ledger;
        });
    }

    /**
     * Recalculate and repair full ledger chain for a member
     */
    public static function recalculateMemberLedger(int $memberId): void
    {
        DB::transaction(function () use ($memberId) {
            $entries = Ledger::where('member_id', $memberId)
                ->orderBy('transaction_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $running = 0.0;
            foreach ($entries as $entry) {
                $running = $running + (float)$entry->debit - (float)$entry->credit;
                $entry->running_balance = $running;
                $entry->save();
            }

            $member = Member::find($memberId);
            if ($member) {
                $member->pending_amount = max(0, $running);
                $member->save();
            }
        });
    }
}
