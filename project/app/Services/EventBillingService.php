<?php

namespace App\Services;

use App\Models\EventBilling;
use App\Models\MarriageEvent;
use App\Models\Member;
use App\Models\Scheme;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class EventBillingService
{
    /**
     * Run consolidated event billing for all active members belonging to a scheme or all active members.
     * Prevents duplicate billing for the same event and month combination.
     */
    public static function processConsolidatedBilling(array $data): EventBilling
    {
        return DB::transaction(function () use ($data) {
            $eventId = $data['event_id'] ?? null;
            $billingMonth = $data['billing_month'] ?? Carbon::now()->format('Y-m'); // e.g. 2026-07
            $monthName = Carbon::createFromFormat('Y-m', $billingMonth)->format('F Y');
            $schemeId = $data['scheme_id'] ?? null;
            $eventsCount = (int)($data['events_count'] ?? 1);
            $ratePerEvent = (float)($data['rate_per_event'] ?? 200.0);
            $totalPerMember = $eventsCount * $ratePerEvent;

            // 1. Check for Duplicate Billing
            $existing = EventBilling::where('billing_month', $billingMonth)
                ->when($eventId, fn($q) => $q->where('event_id', $eventId))
                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
                ->first();

            if ($existing) {
                throw new Exception("Consolidated billing has already been generated for month {$monthName} and selected event/scheme.");
            }

            // 2. Resolve Target Active Members
            $membersQuery = Member::where('status', 'Active');
            if ($schemeId) {
                $membersQuery->where('scheme_id', $schemeId);
            }
            $members = $membersQuery->get();

            if ($members->isEmpty()) {
                throw new Exception("No active members found for the selected billing criteria.");
            }

            $event = $eventId ? MarriageEvent::find($eventId) : null;
            $scheme = $schemeId ? Scheme::find($schemeId) : null;

            $description = "Consolidated Event Billing ({$monthName}): {$eventsCount} Event(s) @ ₹{$ratePerEvent}" . ($event ? " [{$event->title}]" : '');

            // 3. Post Debits to Each Member's Ledger
            $billingDate = $data['billing_date'] ?? now()->toDateString();
            foreach ($members as $member) {
                LedgerService::postEntry(
                    memberId: $member->id,
                    entryType: 'Event Billing',
                    description: $description,
                    debit: $totalPerMember,
                    credit: 0.0,
                    transactionDate: $billingDate,
                    agentId: $member->agent_id,
                    referenceNo: $event ? $event->event_code : 'EVT-BILL-' . $billingMonth
                );
            }

            // 4. Create Event Billing record
            $totalBillingAmount = $totalPerMember * $members->count();
            $eventBilling = EventBilling::create([
                'event_id' => $eventId,
                'billing_month' => $billingMonth,
                'month_name' => $monthName,
                'scheme_id' => $schemeId,
                'events_count' => $eventsCount,
                'rate_per_event' => $ratePerEvent,
                'total_per_member' => $totalPerMember,
                'billed_members_count' => $members->count(),
                'total_billing_amount' => $totalBillingAmount,
                'billing_date' => $billingDate,
                'created_by' => auth()->check() ? auth()->id() : null,
            ]);

            // 5. Audit Log
            AuditService::log('create', 'event_billings', (string)$eventBilling->id, null, [
                'month' => $monthName,
                'members_count' => $members->count(),
                'total_amount' => $totalBillingAmount
            ]);

            return $eventBilling;
        });
    }
}
