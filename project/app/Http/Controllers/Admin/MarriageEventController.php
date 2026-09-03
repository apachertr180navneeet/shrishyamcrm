<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarriageEvent;
use App\Models\Member;
use App\Models\Scheme;
use App\Models\EventBilling;
use App\Services\EventBillingService;
use App\Services\NumberSeriesService;
use App\Services\AuditService;

class MarriageEventController extends Controller
{
    public function index()
    {
        $events = MarriageEvent::with(['member', 'scheme', 'payouts', 'contributions'])->latest('event_date')->paginate(10);
        $members = Member::with(['scheme', 'nominees'])->where('status', 'Active')->get();
        $schemes = Scheme::where('status', 'Active')->get();
        $billings = EventBilling::with(['event', 'creator'])->latest('billing_date')->take(10)->get();

        // Aggregate list of daughters and female members for Bride / Girl Name dropdown
        $girlsList = collect();
        foreach ($members as $mem) {
            // 1. Daughters / Female nominees
            foreach ($mem->nominees as $nom) {
                if (strtolower($nom->relation ?? '') === 'daughter' || strtolower($nom->gender ?? '') === 'female') {
                    $girlsList->push([
                        'type' => 'nominee',
                        'girl_name' => $nom->name,
                        'father_name' => $mem->full_name,
                        'member_id' => $mem->id,
                        'scheme_id' => $mem->scheme_id,
                        'member_name' => $mem->full_name,
                        'membership_no' => $mem->membership_no,
                        'label' => "{$nom->name} (Daughter of {$mem->full_name} - {$mem->membership_no})",
                    ]);
                }
            }
            // 2. Female members themselves
            if (strtolower($mem->gender ?? '') === 'female') {
                $girlsList->push([
                    'type' => 'member',
                    'girl_name' => $mem->full_name,
                    'father_name' => $mem->father_spouse_name ?: '',
                    'member_id' => $mem->id,
                    'scheme_id' => $mem->scheme_id,
                    'member_name' => $mem->full_name,
                    'membership_no' => $mem->membership_no,
                    'label' => "{$mem->full_name} (Member: {$mem->membership_no})",
                ]);
            }
        }

        return view('admin.events.index', compact('events', 'members', 'schemes', 'billings', 'girlsList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:200',
            'girl_name' => 'required|string|max:100',
            'event_date' => 'required|date',
            'scheme_id' => 'required|exists:schemes,id',
            'target_amount' => 'nullable|numeric|min:0',
            'rate_per_event' => 'nullable|numeric|min:0',
        ]);

        $title = $request->title ?: "विवाह सहायता कार्यक्रम - सुपुत्री {$request->girl_name}";
        $eventCode = NumberSeriesService::getNextNumber('EVT', ['prefix' => 'EVT-' . date('Y') . '-', 'initial_value' => 1, 'padding' => 2]);

        $event = MarriageEvent::create([
            'event_code' => $eventCode,
            'title' => $title,
            'event_type' => $request->event_type ?? 'Marriage Support',
            'girl_name' => $request->girl_name,
            'father_name' => $request->father_name,
            'member_id' => $request->member_id ?: null,
            'scheme_id' => $request->scheme_id,
            'event_date' => $request->event_date,
            'venue' => $request->venue ?: 'श्री श्याम धर्मशाला, लोहीकी',
            'target_amount' => $request->target_amount ?: 51000.00,
            'collected_amount' => 0,
            'beneficiary_payout_amount' => $request->target_amount ?: 51000.00,
            'rate_per_event' => $request->rate_per_event ?? 200.00,
            'status' => 'Upcoming',
            'description' => $request->description,
        ]);

        // Automatically identify Scheme members, calculate age-slabs, and generate EventContribution records
        $generatedCount = \App\Services\ContributionCalculationService::generateEventContributions($event);

        AuditService::log('create', 'events', (string)$event->id, null, [
            'code' => $eventCode,
            'title' => $event->title,
            'scheme_id' => $event->scheme_id,
            'contributions_generated' => $generatedCount
        ]);

        return redirect()->route('admin.events.contributions', $event->id)
            ->with('success', "विवाह कार्यक्रम {$eventCode} ({$event->girl_name}) सफलतापूर्वक दर्ज किया गया! योजना के {$generatedCount} सदस्यों का आयु-वर्ग अनुसार अंशदान तैयार हो गया है।");
    }

    /**
     * Live Preview of Scheme Members, Age Slabs, and Contribution amounts.
     */
    public function previewSchemeMembers(Request $request)
    {
        $request->validate([
            'scheme_id' => 'required|exists:schemes,id',
            'event_date' => 'nullable|date',
        ]);

        $preview = \App\Services\ContributionCalculationService::getPreviewForScheme(
            (int)$request->scheme_id,
            $request->event_date
        );

        return response()->json($preview);
    }

    /**
     * Dedicated Event Contributions List & Tracking page.
     */
    public function contributions($id, Request $request)
    {
        $event = MarriageEvent::with(['scheme', 'member'])->findOrFail($id);
        
        $query = $event->contributions()->with(['member.scheme', 'member.agent', 'payment']);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = \App\Helpers\Helper::likeEscape($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('member_name', 'like', "%{$search}%")
                  ->orWhere('receipt_no', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($mq) use ($search) {
                      $mq->where('membership_no', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        $contributions = $query->paginate(20)->withQueryString();

        $stats = [
            'total_members' => $event->contributions()->count(),
            'total_expected' => (float)$event->contributions()->sum('contribution_amount'),
            'total_collected' => (float)$event->contributions()->where('payment_status', 'Paid')->sum('contribution_amount'),
            'total_pending' => (float)$event->contributions()->where('payment_status', 'Pending')->sum('contribution_amount'),
            'paid_count' => $event->contributions()->where('payment_status', 'Paid')->count(),
            'pending_count' => $event->contributions()->where('payment_status', 'Pending')->count(),
        ];

        return view('admin.events.contributions', compact('event', 'contributions', 'stats'));
    }

    public function billMembers(Request $request)
    {
        $request->validate([
            'event_id' => 'nullable|exists:marriage_events,id',
            'billing_month' => 'required|string',
            'events_count' => 'required|integer|min:1',
            'rate_per_event' => 'required|numeric|min:1',
        ]);

        try {
            $billing = EventBillingService::processConsolidatedBilling($request->only([
                'event_id', 'billing_month', 'events_count', 'rate_per_event',
            ]));

            return back()->with('success', "Consolidated billing for {$billing->month_name} applied successfully to {$billing->billed_members_count} active members. Total: ₹" . number_format($billing->total_billing_amount, 2));
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing consolidated event billing: ' . $e->getMessage());
        }
    }

    public function eventsByMonth(Request $request)
    {
        $monthStr = $request->query('month', date('Y-m')); // e.g. 2026-09
        if (!preg_match('/^\d{4}-\d{2}$/', $monthStr)) {
            return response()->json(['error' => 'Invalid month format (expected YYYY-MM)'], 422);
        }

        [$year, $month] = explode('-', $monthStr);
        $dateObj = \Carbon\Carbon::createFromDate($year, $month, 1);
        $monthNameHindi = [
            'January' => 'जनवरी', 'February' => 'फरवरी', 'March' => 'मार्च',
            'April' => 'अप्रैल', 'May' => 'मई', 'June' => 'जून',
            'July' => 'जुलाई', 'August' => 'अगस्त', 'September' => 'सितंबर',
            'October' => 'अक्टूबर', 'November' => 'नवंबर', 'December' => 'दिसंबर'
        ][$dateObj->format('F')] ?? $dateObj->format('F');
        $formattedMonth = $monthNameHindi . ' ' . $year;

        $events = MarriageEvent::whereYear('event_date', $year)
            ->whereMonth('event_date', $month)
            ->orderBy('event_date')
            ->get();

        $eventsCount = $events->count();
        $totalRate = $events->sum('rate_per_event') ?: ($eventsCount * 200);

        // Build default common message
        $msgLines = [];
        $msgLines[] = "जय श्री श्याम 🙏";
        $msgLines[] = "श्री श्याम वेलफेयर सोसायटी, लोहीकी";
        $msgLines[] = "📢 आवश्यक सूचना: माह {$formattedMonth} के कन्या विवाह कार्यक्रम";
        $msgLines[] = "------------------------------------";

        if ($eventsCount > 0) {
            $i = 1;
            foreach ($events as $ev) {
                $evDate = $ev->event_date ? $ev->event_date->format('d/m/Y') : 'N/A';
                $father = $ev->father_name ? " (पिता: {$ev->father_name})" : '';
                $msgLines[] = "{$i}. कन्या: {$ev->girl_name}{$father}";
                $msgLines[] = "   दिनांक: {$evDate} | स्थल: {$ev->venue}";
                $msgLines[] = "   सहयोग: ₹" . number_format($ev->rate_per_event, 0);
                $i++;
            }
            $msgLines[] = "------------------------------------";
            $msgLines[] = "कुल कार्यक्रम: {$eventsCount}";
            $msgLines[] = "प्रति सदस्य कुल सहयोग राशि: ₹" . number_format($totalRate, 0);
        } else {
            $msgLines[] = "इस माह में अभी कोई पंजीकृत विवाह कार्यक्रम नहीं है।";
            $msgLines[] = "मासिक सहयोग राशि: ₹200";
        }

        $msgLines[] = "";
        $msgLines[] = "सभी सम्मानित सदस्यों से विनम्र निवेदन है कि अपनी सहयोग राशि समय पर अधिकृत प्रतिनिधि (एजेंट) के पास अथवा सीधे सोसायटी खाते में जमा करवाकर रसीद अवश्य प्राप्त करें।";
        $msgLines[] = "";
        $msgLines[] = "भवदीय,";
        $msgLines[] = "श्री श्याम वेलफेयर सोसायटी लोहीकी";
        $msgLines[] = "जय श्री श्याम 🙏";

        $defaultMessage = implode("\n", $msgLines);

        return response()->json([
            'month' => $monthStr,
            'month_name' => $formattedMonth,
            'events_count' => $eventsCount,
            'total_rate' => $totalRate,
            'events' => $events,
            'default_message' => $defaultMessage,
        ]);
    }

    public function sendBroadcast(Request $request)
    {
        $request->validate([
            'month' => 'required|string',
            'message' => 'required|string|min:5',
        ]);

        $members = Member::where('status', 'Active')->whereNotNull('mobile')->get();
        if ($members->isEmpty()) {
            return back()->with('error', 'No active members with phone numbers found.');
        }

        $sentCount = 0;
        foreach ($members as $m) {
            \App\Models\WhatsAppLog::create([
                'member_id' => $m->id,
                'recipient_name' => $m->full_name,
                'mobile' => $m->mobile,
                'message_type' => 'Monthly Events Broadcast (' . $request->month . ')',
                'message_body' => $request->message,
                'status' => 'Queued',
                'sent_at' => now(),
            ]);
            $sentCount++;
        }

        AuditService::log('create', 'whatsapp_broadcast', $request->month, null, [
            'members_count' => $sentCount,
            'month' => $request->month,
        ]);

        // Provide direct WhatsApp web link for first member or general share
        $encodedMsg = urlencode($request->message);
        $firstMember = $members->first();
        $cleanPhone = preg_replace('/[^0-9]/', '', $firstMember->mobile);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }
        $whatsappUrl = "https://api.whatsapp.com/send?text={$encodedMsg}";

        return back()->with([
            'success' => "माह {$request->month} का साझा संदेश {$sentCount} सक्रिय सदस्यों के लिए सफलतापूर्वक तैयार और लॉग कर दिया गया है!",
            'whatsapp_broadcast_url' => $whatsappUrl,
        ]);
    }
}
