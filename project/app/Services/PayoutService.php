<?php

namespace App\Services;

use App\Models\Payout;
use App\Models\MarriageEvent;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class PayoutService
{
    /**
     * Create and disburse beneficiary assistance payout through approval workflow.
     */
    public static function createPayout(array $data): Payout
    {
        return DB::transaction(function () use ($data) {
            $payoutNo = NumberSeriesService::getNextNumber('PAY', ['prefix' => 'PAY-' . Carbon::now()->format('Y') . '-', 'initial_value' => 1, 'padding' => 3]);

            $payout = Payout::create([
                'payout_no' => $payoutNo,
                'event_id' => $data['event_id'] ?? null,
                'member_id' => $data['member_id'] ?? null,
                'scheme_id' => $data['scheme_id'] ?? null,
                'payout_type' => $data['payout_type'] ?? 'Marriage Assistance',
                'beneficiary_name' => $data['beneficiary_name'],
                'relation' => $data['relation'] ?? 'Beneficiary',
                'amount' => (float)$data['amount'],
                'payout_date' => $data['payout_date'] ?? now()->toDateString(),
                'approved_by' => auth()->check() ? auth()->user()->full_name : 'Super Admin',
                'disbursed_by' => auth()->check() ? auth()->user()->full_name : 'Super Admin',
                'payment_mode' => $data['payment_mode'] ?? 'Bank Transfer',
                'transaction_ref' => $data['transaction_ref'] ?? ('UTR' . random_int(100000, 999999)),
                'status' => ($data['status'] ?? 'Disbursed'),
                'remarks' => $data['remarks'] ?? 'Beneficiary assistance disbursed',
            ]);

            // Mark event Completed only when cumulative disbursed payouts reach the target amount
            if (!empty($data['event_id'])) {
                $event = MarriageEvent::find($data['event_id']);
                if ($event && $event->target_amount > 0) {
                    $totalDisbursed = Payout::where('event_id', $event->id)
                        ->where('status', 'Disbursed')
                        ->sum('amount');
                    if ($totalDisbursed >= (float)$event->target_amount) {
                        $event->status = 'Completed';
                        $event->save();
                    }
                }
            }

            // Audit log
            AuditService::log('create', 'payouts', (string)$payout->id, null, [
                'payout_no' => $payoutNo,
                'beneficiary' => $payout->beneficiary_name,
                'amount' => $payout->amount
            ]);

            return $payout;
        });
    }

    /**
     * Allowed status transitions (state machine).
     */
    private const ALLOWED_TRANSITIONS = [
        'Eligible'          => ['Pending Approval', 'Rejected'],
        'Pending Approval'  => ['Approved', 'Rejected'],
        'Approved'          => ['Disbursed', 'Rejected'],
        'Disbursed'         => [],
        'Rejected'          => [],
    ];

    public static function updateStatus(int $payoutId, string $newStatus, ?string $remarks = null): Payout
    {
        $newStatus = trim($newStatus);

        if (!array_key_exists($newStatus, self::ALLOWED_TRANSITIONS)) {
            throw new \InvalidArgumentException("Invalid payout status: {$newStatus}.");
        }

        return DB::transaction(function () use ($payoutId, $newStatus, $remarks) {
            $payout = Payout::lockForUpdate()->findOrFail($payoutId);
            $oldStatus = $payout->status;

            // Enforce state machine transitions
            $allowed = self::ALLOWED_TRANSITIONS[$oldStatus] ?? [];
            if (!in_array($newStatus, $allowed, true)) {
                throw new \InvalidArgumentException("Cannot transition payout {$payout->payout_no} from '{$oldStatus}' to '{$newStatus}'.");
            }

            $payout->status = $newStatus;

            if ($newStatus === 'Approved') {
                $payout->approved_by = auth()->check() ? auth()->user()->full_name : 'Super Admin';
            } elseif ($newStatus === 'Disbursed') {
                $payout->disbursed_by = auth()->check() ? auth()->user()->full_name : 'Super Admin';
                if (empty($payout->approved_by)) {
                    throw new \InvalidArgumentException('Payout must be Approved before it can be disbursed.');
                }
            }

            if ($remarks) {
                $payout->remarks = $remarks;
            }

            $payout->save();

            AuditService::log('status_change', 'payouts', (string)$payout->id, ['status' => $oldStatus], ['status' => $newStatus]);

            return $payout;
        });
    }
}
