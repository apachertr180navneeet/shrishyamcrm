<?php

namespace App\Services;

use App\Models\Member;
use App\Models\SocietySetting;
use Barryvdh\DomPDF\Facade\Pdf;

class MemberLedgerCardService
{
    /**
     * Ensure Hindi/Devanagari TrueType font files and font storage directory exist.
     */
    public static function ensureFontsExist(): void
    {
        $storageFontsDir = storage_path('fonts');
        if (!is_dir($storageFontsDir)) {
            @mkdir($storageFontsDir, 0777, true);
        }

        $publicFontsDir = public_path('fonts');
        if (!is_dir($publicFontsDir)) {
            @mkdir($publicFontsDir, 0777, true);
        }

        $fontFiles = [
            'mangal.ttf' => 'C:/Windows/Fonts/mangal.ttf',
            'mangalb.ttf' => 'C:/Windows/Fonts/mangalb.ttf',
            'aparaj.ttf' => 'C:/Windows/Fonts/aparaj.ttf',
            'aparajb.ttf' => 'C:/Windows/Fonts/aparajb.ttf',
        ];

        foreach ($fontFiles as $filename => $sourcePath) {
            $destPath = $publicFontsDir . DIRECTORY_SEPARATOR . $filename;
            if (!file_exists($destPath) && file_exists($sourcePath)) {
                @copy($sourcePath, $destPath);
            }
        }
    }

    /**
     * Generate printable / downloadable Member Ledger & Event Receipt Card PDF
     */
    public static function generatePdf(int $memberId): \Barryvdh\DomPDF\PDF
    {
        static::ensureFontsExist();

        $member = Member::with([
            'scheme',
            'agent',
            'nominees',
            'ageSlab',
            'eventContributions.event',
            'eventContributions.agent',
            'eventContributions.payment.agent',
            'payments.agent',
            'payments.event',
        ])->findOrFail($memberId);

        $society = [
            'name' => SocietySetting::getVal('society_name', 'Shri Shyam Welfare Society'),
            'name_hindi' => SocietySetting::getVal('society_name_hindi', 'श्री श्याम वेलफेयर सोसायटी लोहिड़ी'),
            'reg_no' => SocietySetting::getVal('reg_no', 'COOP/2025/BALOTARA/500219'),
            'san_prefix' => SocietySetting::getVal('san_prefix', '0878920000000014'),
            'address' => SocietySetting::getVal('address', 'ग्रा.पं. लोहिड़ी, पं.स. सिणधरी, जिला बालोतरा (राज.)'),
            'phone' => SocietySetting::getVal('phone', '9664090906, 9549635631, 9783049650'),
            'president' => SocietySetting::getVal('president_name', 'Shri Navneet Sharma'),
            'secretary' => SocietySetting::getVal('secretary_name', 'Shri Mahesh Garg'),
        ];

        // 1. Gather all Event Contributions for this member
        $contributions = $member->eventContributions()
            ->with(['event', 'agent', 'payment.agent'])
            ->orderBy('event_date')
            ->orderBy('id')
            ->get();

        $tableRows = [];
        $totalPaid = 0.0;
        $totalPending = 0.0;
        $totalExpected = 0.0;

        $now = \Carbon\Carbon::now();
        $startOfCurrentMonth = $now->copy()->startOfMonth();
        $endOfCurrentMonth = $now->copy()->endOfMonth();

        $thisMonthAmount = 0.0;
        $thisMonthPaid = 0.0;
        $thisMonthPending = 0.0;
        $previousDue = 0.0;

        foreach ($contributions as $ec) {
            $isPaid = ($ec->payment_status === 'Paid');
            $amount = (float)$ec->contribution_amount;

            $totalExpected += $amount;
            if ($isPaid) {
                $totalPaid += $amount;
            } else {
                $totalPending += $amount;
            }

            $eventDate = $ec->event_date ?: ($ec->event && $ec->event->event_date ? $ec->event->event_date : null);
            $isCurrentMonth = $eventDate && $eventDate->greaterThanOrEqualTo($startOfCurrentMonth) && $eventDate->lessThanOrEqualTo($endOfCurrentMonth);

            if ($isCurrentMonth) {
                $thisMonthAmount += $amount;
                if ($isPaid) {
                    $thisMonthPaid += $amount;
                } else {
                    $thisMonthPending += $amount;
                }
            } else {
                if (!$isPaid) {
                    $previousDue += $amount;
                }
            }

            // Description: Girl Name / Father Name / Venue or Event Title
            $desc = '';
            if ($ec->event) {
                $ev = $ec->event;
                $desc = $ev->girl_name;
                if ($ev->father_name) {
                    $desc .= ' / ' . $ev->father_name;
                }
                if ($ev->venue) {
                    $desc .= ' ' . $ev->venue;
                }
            } else {
                $desc = $ec->event_name ?: 'कल्याण सहायता कार्यक्रम';
            }

            // Joining / Event Date
            $eventDateStr = $ec->event_date
                ? $ec->event_date->format('d.m.y')
                : ($ec->event && $ec->event->event_date ? $ec->event->event_date->format('d.m.y') : '-');

            // Payment Date or Pending Status
            if ($isPaid) {
                $payDateStr = $ec->payment_date
                    ? $ec->payment_date->format('d.m.y')
                    : ($ec->payment && $ec->payment->payment_date ? $ec->payment->payment_date->format('d.m.y') : 'जमा');
            } else {
                $payDateStr = 'बकाया (Pending)';
            }

            // Agent / Karyakarta Name
            $agentName = $ec->agent
                ? $ec->agent->name
                : ($ec->payment && $ec->payment->agent ? $ec->payment->agent->name : ($member->agent ? $member->agent->name : '-'));

            $tableRows[] = [
                'description' => $desc,
                'event_date' => $eventDateStr,
                'payment_date' => $payDateStr,
                'agent_name' => $agentName,
                'amount' => $amount,
                'is_paid' => $isPaid,
                'type' => 'event',
            ];
        }

        // 2. Also check direct payments not linked to event contributions (e.g. Joining Fee, Monthly Support)
        $unlinkedPayments = $member->payments()
            ->whereNull('event_contribution_id')
            ->where('status', 'Verified')
            ->get();

        foreach ($unlinkedPayments as $p) {
            $amt = (float)$p->amount;
            $totalPaid += $amt;
            $totalExpected += $amt;

            $tableRows[] = [
                'description' => $p->payment_type . ($p->remarks ? ' - ' . $p->remarks : ''),
                'event_date' => $p->payment_date ? $p->payment_date->format('d.m.y') : '-',
                'payment_date' => $p->payment_date ? $p->payment_date->format('d.m.y') : 'जमा',
                'agent_name' => $p->agent ? $p->agent->name : ($member->agent ? $member->agent->name : '-'),
                'amount' => $amt,
                'is_paid' => true,
                'type' => 'payment',
            ];
        }

        // If no events in current month but member has a monthly support rate or general pending amount
        if ($thisMonthAmount == 0 && $contributions->count() == 0 && $member->monthly_support_amount > 0) {
            $thisMonthAmount = (float)$member->monthly_support_amount;
        }

        // Previous due fallback if ledger has extra balance
        if ($member->pending_amount > 0 && ($thisMonthPending + $previousDue) < (float)$member->pending_amount) {
            $previousDue = max(0, (float)$member->pending_amount - $thisMonthPending);
        }

        $totalDue = $thisMonthAmount + $previousDue;

        // 3. Ensure minimum 20 rows for the grid to look authentic like the printed ledger card
        $minimumRows = 20;
        $blankRowsCount = max(0, $minimumRows - count($tableRows));

        // Nominee details
        $primaryNominee = $member->nominees->first();
        $nomineeName = $primaryNominee
            ? $primaryNominee->name . ($primaryNominee->relation ? ' (' . $primaryNominee->relation . ')' : '')
            : '-';

        // Kisht rate
        $kishtRate = $member->monthly_support_amount
            ?: ($member->ageSlab ? $member->ageSlab->amount : ($contributions->first() ? $contributions->first()->contribution_amount : 400));

        $sanCode = $member->san_code ?: $society['san_prefix'];

        $mangalPath = str_replace('\\', '/', public_path('fonts/mangal.ttf'));
        $mangalbPath = str_replace('\\', '/', public_path('fonts/mangalb.ttf'));
        $aparajPath = str_replace('\\', '/', public_path('fonts/aparaj.ttf'));
        $aparajbPath = str_replace('\\', '/', public_path('fonts/aparajb.ttf'));
        $logoPath = str_replace('\\', '/', public_path('assets/society_logo.jpg'));
        $rupeeIconPath = str_replace('\\', '/', public_path('assets/rupee_icon.png'));

        return Pdf::loadView('pdf.member_ledger', compact(
            'member',
            'society',
            'tableRows',
            'blankRowsCount',
            'totalPaid',
            'totalPending',
            'totalExpected',
            'thisMonthAmount',
            'previousDue',
            'totalDue',
            'nomineeName',
            'kishtRate',
            'sanCode',
            'mangalPath',
            'mangalbPath',
            'aparajPath',
            'aparajbPath',
            'logoPath',
            'rupeeIconPath'
        ))->setPaper('a4', 'portrait');
    }
}
