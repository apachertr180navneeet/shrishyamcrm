<?php

namespace App\Services;

use App\Models\EventBilling;
use App\Models\MarriageEvent;
use App\Models\EventContribution;
use App\Models\Member;
use App\Models\Scheme;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class EventBillingService
{
    /**
     * Run consolidated event billing for all active members based on each member's applicable age-slab / monthly rate.
     * Prevents duplicate billing for the same event and month combination.
     */
    public static function processConsolidatedBilling(array $data): EventBilling
    {
        return DB::transaction(function () use ($data) {
            $eventId = $data['event_id'] ?? null;
            $billingMonth = $data['billing_month'] ?? Carbon::now()->format('Y-m'); // e.g. 2026-09

            if (!preg_match('/^\d{4}-\d{2}$/', $billingMonth)) {
                throw new Exception("Invalid billing month format: {$billingMonth}.");
            }
            [$year, $month] = explode('-', $billingMonth);
            $monthName = Carbon::createFromFormat('Y-m', $billingMonth)->format('F Y');
            $schemeId = $data['scheme_id'] ?? null;
            $rateType = $data['rate_type'] ?? 'member_slab'; // 'member_slab' or 'fixed_rate'
            $fallbackRate = (float)($data['rate_per_event'] ?? 200.0);

            // 1. Resolve Events for the Month
            if ($eventId) {
                $monthEvents = MarriageEvent::where('id', $eventId)->get();
            } else {
                $monthEvents = MarriageEvent::whereYear('event_date', $year)
                    ->whereMonth('event_date', $month)
                    ->orderBy('event_date')
                    ->get();
            }
            $eventsCount = $monthEvents->isNotEmpty() ? $monthEvents->count() : (int)($data['events_count'] ?? 1);

            // 2. Check for Duplicate Billing (row-locked to prevent concurrent duplicates)
            $existing = EventBilling::where('billing_month', $billingMonth)
                ->when($eventId, fn($q) => $q->where('event_id', $eventId))
                ->when($schemeId, fn($q) => $q->where('scheme_id', $schemeId))
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw new Exception("Consolidated billing has already been generated for month {$monthName}" . ($schemeId ? " and selected scheme." : "."));
            }

            $event = $eventId ? MarriageEvent::find($eventId) : null;
            $scheme = $schemeId ? Scheme::find($schemeId) : null;

            // 3. Resolve Target Active Members
            $membersQuery = Member::with(['scheme', 'ageSlab'])->where('status', 'Active');
            if ($schemeId) {
                $membersQuery->where('scheme_id', $schemeId);
            }
            if ($event && $event->member_id) {
                $membersQuery->where('id', '!=', $event->member_id);
            } elseif ($event && !empty($event->girl_name)) {
                $membersQuery->where('full_name', '!=', trim($event->girl_name));
            }
            $members = $membersQuery->get();

            if ($members->isEmpty()) {
                throw new Exception("No active members found for the selected billing criteria.");
            }

            $billingDate = $data['billing_date'] ?? now()->toDateString();
            $totalBillingAmount = 0.0;
            $avgRate = 0.0;

            // 4. Calculate Bill per Member Rate and Create Contributions + Ledger Entries
            foreach ($members as $member) {
                if ($rateType === 'fixed_rate') {
                    $memberRate = $fallbackRate;
                } else {
                    $memberRate = (float)($member->monthly_support_amount 
                        ?: ($member->ageSlab ? $member->ageSlab->support_amount : $fallbackRate));
                }

                $totalForThisMember = $eventsCount * $memberRate;
                $totalBillingAmount += $totalForThisMember;
                $avgRate += $memberRate;

                // Create Event Contribution records for each event in this month
                if ($monthEvents->isNotEmpty()) {
                    foreach ($monthEvents as $ev) {
                        // Skip if member is beneficiary of this event
                        if ($ev->member_id && (int)$ev->member_id === (int)$member->id) {
                            continue;
                        }

                        $memberAge = (int)($member->age ?: ($member->dob ? Carbon::parse($member->dob)->diffInYears($ev->event_date ?: now()) : 25));
                        $slabName = $member->ageSlab ? $member->ageSlab->slab_name : ($memberAge . ' वर्ष');

                        EventContribution::firstOrCreate(
                            [
                                'event_id' => $ev->id,
                                'member_id' => $member->id,
                            ],
                            [
                                'scheme_id' => $member->scheme_id ?? $ev->scheme_id,
                                'agent_id' => $member->agent_id,
                                'event_name' => $ev->title ?: "विवाह कार्यक्रम: {$ev->girl_name}",
                                'event_date' => $ev->event_date ?: $billingDate,
                                'member_name' => $member->full_name,
                                'member_age' => $memberAge,
                                'age_slab' => $slabName,
                                'contribution_amount' => $memberRate,
                                'payment_status' => 'Pending',
                            ]
                        );
                    }
                }

                // Post Debit in Ledger
                $desc = "Consolidated Event Billing ({$monthName}): {$eventsCount} Event(s) @ ₹" . number_format($memberRate, 0) . "/event" . ($event ? " [{$event->title}]" : '');
                LedgerService::postEntry(
                    memberId: $member->id,
                    entryType: 'Event Billing',
                    description: $desc,
                    debit: $totalForThisMember,
                    credit: 0.0,
                    transactionDate: $billingDate,
                    agentId: $member->agent_id,
                    referenceNo: $event ? $event->event_code : 'BILL-' . $billingMonth . '-' . $member->membership_no
                );
            }

            $effectiveRatePerEvent = $members->count() > 0 ? round($avgRate / $members->count(), 2) : $fallbackRate;
            $avgTotalPerMember = $members->count() > 0 ? round($totalBillingAmount / $members->count(), 2) : 0;

            // 5. Create Event Billing record
            $eventBilling = EventBilling::create([
                'event_id' => $eventId,
                'billing_month' => $billingMonth,
                'month_name' => $monthName,
                'scheme_id' => $schemeId,
                'events_count' => $eventsCount,
                'rate_per_event' => $effectiveRatePerEvent,
                'total_per_member' => $avgTotalPerMember,
                'billed_members_count' => $members->count(),
                'total_billing_amount' => $totalBillingAmount,
                'billing_date' => $billingDate,
                'created_by' => auth()->check() ? auth()->id() : null,
            ]);

            // 6. Audit Log
            AuditService::log('create', 'event_billings', (string)$eventBilling->id, null, [
                'month' => $monthName,
                'members_count' => $members->count(),
                'total_amount' => $totalBillingAmount,
                'rate_type' => $rateType,
            ]);

            return $eventBilling;
        });
    }
}

