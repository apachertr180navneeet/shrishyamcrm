<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MarriageEvent;
use App\Models\EventContribution;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ContributionCalculationService
{
    /**
     * Determine Age Slab and Contribution Amount based on Member's Age.
     *
     * 0–5 years   = ₹100
     * 6–9 years   = ₹200
     * 10–13 years = ₹300
     * 14–17 years = ₹400
     * 17+ years   = ₹500
     */
    public static function getSlabDetails(int $age): array
    {
        $age = max(0, $age);

        if ($age <= 5) {
            return [
                'slab' => '0–5 years',
                'amount' => 100.00,
            ];
        } elseif ($age <= 9) {
            return [
                'slab' => '6–9 years',
                'amount' => 200.00,
            ];
        } elseif ($age <= 13) {
            return [
                'slab' => '10–13 years',
                'amount' => 300.00,
            ];
        } elseif ($age <= 17) {
            return [
                'slab' => '14–17 years',
                'amount' => 400.00,
            ];
        } else {
            return [
                'slab' => '17+ years',
                'amount' => 500.00,
            ];
        }
    }

    /**
     * Calculate Member's age on a given Event Date and return their contribution details.
     */
    public static function calculateForMember(Member $member, $eventDate = null): array
    {
        $targetDate = $eventDate ? Carbon::parse($eventDate) : Carbon::today();

        if ($member->dob) {
            $dob = Carbon::parse($member->dob);
            $age = (int)$dob->diffInYears($targetDate);
        } else {
            $age = (int)($member->age ?: 25);
        }

        $age = max(0, $age);

        // 1. If member has explicit monthly support amount
        if ($member->monthly_support_amount && (float)$member->monthly_support_amount > 0) {
            $amount = (float)$member->monthly_support_amount;
            $slab = $member->ageSlab ? $member->ageSlab->slab_name : ($age . ' वर्ष');
        } elseif ($member->relationLoaded('ageSlab') ? $member->ageSlab : $member->ageSlab()->first()) {
            $ageSlab = $member->relationLoaded('ageSlab') ? $member->ageSlab : $member->ageSlab()->first();
            $amount = (float)($ageSlab->support_amount ?: 200.0);
            $slab = $ageSlab->slab_name;
        } else {
            // Fallback to age-based helper
            $slabDetails = static::getSlabDetails($age);
            $amount = (float)$slabDetails['amount'];
            $slab = $slabDetails['slab'];
        }

        return [
            'age' => $age,
            'slab' => $slab,
            'amount' => $amount,
        ];
    }

    /**
     * Preview members for a given Scheme (or all active members) and Event Date.
     */
    public static function getPreviewForScheme($schemeId = null, $eventDate = null, $excludeMemberId = null, $excludeMemberName = null): array
    {
        $query = Member::where('status', 'Active');
        if ($schemeId) {
            $query->where('scheme_id', $schemeId);
        }
        if ($excludeMemberId) {
            $query->where('id', '!=', $excludeMemberId);
        } elseif ($excludeMemberName) {
            $query->where('full_name', '!=', trim($excludeMemberName));
        }
        $members = $query->orderBy('full_name')->get();

        $rows = [];
        $totalAmount = 0.0;

        foreach ($members as $member) {
            $calc = static::calculateForMember($member, $eventDate);
            $totalAmount += $calc['amount'];

            $rows[] = [
                'member_id' => $member->id,
                'membership_no' => $member->membership_no,
                'full_name' => $member->full_name,
                'mobile' => $member->mobile,
                'age' => $calc['age'],
                'age_slab' => $calc['slab'],
                'amount' => $calc['amount'],
                'status' => 'Pending',
            ];
        }

        return [
            'members_count' => count($rows),
            'total_contribution' => $totalAmount,
            'members' => $rows,
        ];
    }

    /**
     * Generate individual EventContribution records for all active members belonging to the Event's Scheme.
     * The member whose event it is does NOT pay contribution for their own event.
     * Prevents accidental duplicate records for the same member + same event.
     */
    public static function generateEventContributions(MarriageEvent $event): int
    {
        $schemeId = $event->scheme_id;
        $eventDate = $event->event_date ?: Carbon::today();

        $query = Member::where('status', 'Active');
        if ($schemeId) {
            $query->where('scheme_id', $schemeId);
        }

        // Exclude the member whose event it is (the beneficiary member does not pay for their own event)
        if ($event->member_id) {
            $query->where('id', '!=', $event->member_id);
        } elseif (!empty($event->girl_name)) {
            $query->where('full_name', '!=', trim($event->girl_name));
        }

        // Remove any existing contribution record for the beneficiary member if present
        if ($event->member_id) {
            EventContribution::where('event_id', $event->id)
                ->where('member_id', $event->member_id)
                ->delete();
        } elseif (!empty($event->girl_name)) {
            EventContribution::where('event_id', $event->id)
                ->where('member_name', trim($event->girl_name))
                ->delete();
        }

        $members = $query->get();
        $createdCount = 0;

        foreach ($members as $member) {
            $calc = static::calculateForMember($member, $eventDate);

            // Using firstOrCreate with unique constraint to prevent duplicates
            $contribution = EventContribution::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'member_id' => $member->id,
                ],
                [
                    'scheme_id' => $schemeId ?: $member->scheme_id,
                    'event_name' => $event->title,
                    'event_date' => $eventDate,
                    'member_name' => $member->full_name,
                    'member_age' => $calc['age'],
                    'age_slab' => $calc['slab'],
                    'contribution_amount' => $calc['amount'],
                    'payment_status' => 'Pending',
                    'agent_id' => $member->agent_id,
                ]
            );

            if ($contribution->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        return $createdCount;
    }
}
