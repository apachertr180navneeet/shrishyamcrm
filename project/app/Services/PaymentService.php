<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Member;
use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\EventContribution;
use App\Models\MarriageEvent;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Record a member payment, post ledger credit, compute agent commission, and issue receipt.
     */
    public static function processPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $member = Member::lockForUpdate()->findOrFail($data['member_id']);
            $agentId = $data['agent_id'] ?? $member->agent_id;
            $agent = $agentId ? Agent::find($agentId) : null;
            $amount = (float)($data['amount'] ?? 0);

            $eventContributionId = $data['event_contribution_id'] ?? null;
            $eventId = $data['event_id'] ?? null;
            $eventContribution = null;

            if ($eventContributionId) {
                $eventContribution = EventContribution::lockForUpdate()->find($eventContributionId);
                if ($eventContribution) {
                    if ($eventContribution->payment_status === 'Paid') {
                        throw new \Exception("This event contribution has already been paid (Receipt No: {$eventContribution->receipt_no}).");
                    }
                    $eventId = $eventContribution->event_id;
                    $data['payment_type'] = 'Event Contribution';
                    if ($amount <= 0) {
                        $amount = (float)$eventContribution->contribution_amount;
                    }
                }
            }

            if ($amount <= 0) {
                throw new \InvalidArgumentException('Payment amount must be greater than zero.');
            }

            $paymentDate = $data['payment_date'] ?? now()->toDateString();
            $paymentType = $data['payment_type'] ?? 'Monthly Support';
            $paymentMode = $data['payment_mode'] ?? 'Cash';
            $referenceNo = $data['reference_no'] ?? ($paymentMode === 'Cash' ? 'CASH-' . rand(10000, 99999) : 'TXN' . random_int(100000, 999999));
            $remarks = $data['remarks'] ?? 'Payment received';

            // Generate official receipt number
            $receiptNo = NumberSeriesService::getNextNumber('REC', ['prefix' => 'REC-' . Carbon::parse($paymentDate)->format('Y') . '-', 'initial_value' => 5001, 'padding' => 4]);

            // San code
            $sanCode = 'SAN-LOH-' . str_pad($member->id, 3, '0', STR_PAD_LEFT);

            // 1. Create Payment record
            $payment = Payment::create([
                'receipt_no' => $receiptNo,
                'san_code' => $sanCode,
                'member_id' => $member->id,
                'event_id' => $eventId,
                'event_contribution_id' => $eventContributionId,
                'agent_id' => $agent ? $agent->id : null,
                'amount' => $amount,
                'payment_type' => $paymentType,
                'payment_mode' => $paymentMode,
                'reference_no' => $referenceNo,
                'month_year' => Carbon::parse($paymentDate)->format('M Y'),
                'payment_date' => $paymentDate,
                'status' => 'Verified',
                'collected_by' => $data['collected_by'] ?? (auth()->check() ? auth()->user()->full_name : ($agent ? $agent->name : 'Office Admin')),
                'remarks' => $remarks,
            ]);

            // 1b. Update EventContribution if linked
            if ($eventContribution) {
                $eventContribution->update([
                    'payment_status' => 'Paid',
                    'payment_date' => $paymentDate,
                    'payment_id' => $payment->id,
                    'receipt_no' => $receiptNo,
                    'collected_by' => auth()->check() ? auth()->id() : null,
                    'agent_id' => $agent ? $agent->id : $eventContribution->agent_id,
                ]);

                if ($eventContribution->event) {
                    $eventContribution->event->increment('collected_amount', $amount);
                }
            } elseif ($eventId) {
                $event = MarriageEvent::find($eventId);
                if ($event) {
                    $event->increment('collected_amount', $amount);
                }
            }

            // 2. Post Credit to Member Ledger
            $eventTitleSuffix = $eventContribution ? " [{$eventContribution->event_name}]" : '';
            LedgerService::postEntry(
                memberId: $member->id,
                entryType: 'Payment',
                description: "Payment received via {$paymentMode} ({$paymentType}{$eventTitleSuffix}) - Receipt #{$receiptNo}",
                debit: 0.0,
                credit: $amount,
                transactionDate: $paymentDate,
                paymentId: $payment->id,
                agentId: $agent ? $agent->id : null,
                referenceNo: $referenceNo
            );

            // 3. Update Member cumulative paid amount (atomic to avoid lost updates)
            $member->increment('total_paid', $amount);

            // 4. Record Agent Commission if applicable
            if ($agent && (float)$agent->commission_rate > 0) {
                $commissionAmount = round($amount * ((float)$agent->commission_rate / 100), 2);
                AgentCommission::create([
                    'agent_id' => $agent->id,
                    'payment_id' => $payment->id,
                    'member_id' => $member->id,
                    'collection_amount' => $amount,
                    'commission_rate' => $agent->commission_rate,
                    'commission_amount' => $commissionAmount,
                    'status' => 'Earned',
                ]);
            }

            // 5. Audit Log
            AuditService::log('create', 'payments', (string)$payment->id, null, [
                'receipt_no' => $receiptNo,
                'amount' => $amount,
                'member' => $member->full_name,
                'payment_mode' => $paymentMode
            ]);

            return $payment;
        });
    }
}
