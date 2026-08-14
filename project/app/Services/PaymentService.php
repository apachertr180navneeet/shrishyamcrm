<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Member;
use App\Models\Agent;
use App\Models\AgentCommission;
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
            $member = Member::findOrFail($data['member_id']);
            $agentId = $data['agent_id'] ?? $member->agent_id;
            $agent = $agentId ? Agent::find($agentId) : null;
            $amount = (float)$data['amount'];
            $paymentDate = $data['payment_date'] ?? now()->toDateString();
            $paymentType = $data['payment_type'] ?? 'Monthly Support';
            $paymentMode = $data['payment_mode'] ?? 'Cash';
            $referenceNo = $data['reference_no'] ?? ($paymentMode === 'Cash' ? 'CASH-' . rand(10000, 99999) : 'TXN' . rand(100000, 999999));
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

            // 2. Post Credit to Member Ledger
            LedgerService::postEntry(
                memberId: $member->id,
                entryType: 'Payment',
                description: "Payment received via {$paymentMode} ({$paymentType}) - Receipt #{$receiptNo}",
                debit: 0.0,
                credit: $amount,
                transactionDate: $paymentDate,
                paymentId: $payment->id,
                agentId: $agent ? $agent->id : null,
                referenceNo: $referenceNo
            );

            // 3. Update Member cumulative paid amount
            $member->total_paid += $amount;
            $member->save();

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
