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
        $slabDetails = static::getSlabDetails($age);

        return [
            'age' => $age,
            'slab' => $slabDetails['slab'],
            'amount' => $slabDetails['amount'],
        ];
    }

    /**
     * Preview members for a given Scheme and Event Date.
     */
    public static function getPreviewForScheme(int $schemeId, $eventDate = null): array
    {
        $members = Member::where('scheme_id', $schemeId)
            ->where('status', 'Active')
            ->orderBy('full_name')
            ->get();

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
