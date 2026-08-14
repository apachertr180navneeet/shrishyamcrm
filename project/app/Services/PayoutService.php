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
            $count = Payout::count() + 1;
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
                'transaction_ref' => $data['transaction_ref'] ?? ('UTR' . rand(100000, 999999)),
                'status' => $data['status'] ?? 'Disbursed',
                'remarks' => $data['remarks'] ?? 'Beneficiary assistance disbursed',
            ]);

            // Update associated event status and payout amount if linked
            if (!empty($data['event_id'])) {
                $event = MarriageEvent::find($data['event_id']);
                if ($event) {
                    $event->status = 'Completed';
                    $event->save();
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
     * Advance approval status workflow (Eligible -> Pending Approval -> Approved -> Disbursed -> Rejected)
     */
    public static function updateStatus(int $payoutId, string $newStatus, ?string $remarks = null): Payout
    {
        return DB::transaction(function () use ($payoutId, $newStatus, $remarks) {
            $payout = Payout::findOrFail($payoutId);
            $oldStatus = $payout->status;
            $payout->status = $newStatus;

            if ($newStatus === 'Approved') {
                $payout->approved_by = auth()->check() ? auth()->user()->full_name : 'Super Admin';
            } elseif ($newStatus === 'Disbursed') {
                $payout->disbursed_by = auth()->check() ? auth()->user()->full_name : 'Super Admin';
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
