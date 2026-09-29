<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarriageEvent;
use App\Models\Member;
use App\Models\Scheme;
use App\Models\Agent;
use App\Models\SocietySetting;
use App\Models\EventBilling;
use App\Services\EventBillingService;
use App\Services\NumberSeriesService;
use App\Services\AuditService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarriageEventController extends Controller
{
    public function index()
    {
        $events = MarriageEvent::with(['member', 'scheme'])
            ->withCount([
                'contributions',
                'contributions as paid_count' => function ($q) {
                    $q->where('payment_status', 'Paid');
                },
            ])
            ->withSum('contributions as expected_sum', 'contribution_amount')
            ->withSum(['contributions as collected_sum' => function ($q) {
                $q->where('payment_status', 'Paid');
            }], 'contribution_amount')
            ->latest('event_date')
            ->paginate(10);

        $schemes = Scheme::where('status', 'Active')->get();
        $billings = EventBilling::with(['event', 'creator'])->latest('billing_date')->take(10)->get();

        // Optimized lightweight member dropdown list
        $members = Member::where('status', 'Active')
            ->select('id', 'membership_no', 'full_name', 'gender', 'scheme_id', 'age', 'dob', 'father_spouse_name')
            ->orderBy('full_name')
            ->get();

        // Aggregate list of registered active members ONLY (excluding nominees/beneficiaries)
        $beneficiariesList = collect();

        foreach ($members as $mem) {
            $isFemale = strtolower($mem->gender ?? '') === 'female';
            $targetType = $isFemale ? 'daughter' : 'son';

            $beneficiariesList->push([
                'type' => 'member',
                'target_type' => $targetType,
                'beneficiary_name' => $mem->full_name,
                'father_name' => $mem->father_spouse_name ?: '',
                'member_id' => $mem->id,
                'scheme_id' => $mem->scheme_id,
                'member_name' => $mem->full_name,
                'membership_no' => $mem->membership_no,
                'label' => "{$mem->full_name} [Member: {$mem->membership_no}]",
            ]);
        }

        $girlsList = $beneficiariesList; // backwards compatibility

        return view('admin.events.index', compact('events', 'members', 'schemes', 'billings', 'beneficiariesList', 'girlsList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:200',
            'girl_name' => 'nullable|string|max:100',
            'beneficiary_name' => 'nullable|string|max:100',
            'event_type' => 'nullable|string|max:191',
            'event_date' => 'required|date',
            'scheme_id' => 'nullable|exists:schemes,id',
            'target_amount' => 'nullable|numeric|min:0',
            'rate_per_event' => 'nullable|numeric|min:0',
        ]);

        $beneficiaryName = trim($request->beneficiary_name ?: $request->girl_name);
        if (empty($beneficiaryName)) {
            return back()->with('error', 'कृपया सदस्य का नाम दर्ज करें (Member Name is required).');
        }

        $scheme = $request->scheme_id ? Scheme::find($request->scheme_id) : null;

        // Dynamic title
        if ($request->filled('title')) {
            $title = $request->title;
        } else {
            $title = "कल्याण सहायता कार्यक्रम - {$beneficiaryName}";
        }

        $eventType = $request->filled('event_type')
            ? $request->event_type
            : ($scheme ? ($scheme->name_hindi ?: $scheme->name) : 'Marriage Support');

        $eventCode = NumberSeriesService::getNextNumber('EVT', ['prefix' => 'EVT-' . date('Y') . '-', 'initial_value' => 1, 'padding' => 2]);

        $targetAmount = $request->filled('target_amount') ? (float)$request->target_amount : null;

        $event = MarriageEvent::create([
            'event_code' => $eventCode,
            'title' => $title,
            'event_type' => $eventType,
            'girl_name' => $beneficiaryName,
            'father_name' => $request->father_name,
            'member_id' => $request->member_id ?: null,
            'scheme_id' => $request->scheme_id ?: null,
            'event_date' => $request->event_date,
            'venue' => $request->venue ?: 'श्री श्याम धर्मशाला, लोहीकी',
            'target_amount' => $targetAmount ?: 0,
            'collected_amount' => 0,
            'beneficiary_payout_amount' => $targetAmount ?: 0,
            'rate_per_event' => $request->filled('rate_per_event') ? (float)$request->rate_per_event : 0.00,
            'status' => 'Upcoming',
            'description' => $request->description,
        ]);

        // Automatically identify members, calculate age-slabs, and generate EventContribution records
        $generatedCount = \App\Services\ContributionCalculationService::generateEventContributions($event);

        $totalContributionSum = (float)$event->contributions()->sum('contribution_amount');
        if ((!$targetAmount || $targetAmount <= 0) && $totalContributionSum > 0) {
            $event->update([
                'target_amount' => $totalContributionSum,
                'beneficiary_payout_amount' => $totalContributionSum,
            ]);
        }

        // When a person's marriage takes place, their membership is automatically closed (Inactive)
        if ($event->member_id) {
            $beneficiaryMember = Member::find($event->member_id);
            if ($beneficiaryMember && $beneficiaryMember->status === 'Active') {
                $beneficiaryMember->update(['status' => 'Inactive']);
                AuditService::log('membership_closed', 'members', (string)$beneficiaryMember->id, ['status' => 'Active'], [
                    'status' => 'Inactive',
                    'reason' => "Membership closed upon marriage event ({$eventCode} - {$beneficiaryName})"
                ]);
            }
        } elseif (!empty($beneficiaryName)) {
            $beneficiaryMember = Member::where('full_name', $beneficiaryName)->where('status', 'Active')->first();
            if ($beneficiaryMember) {
                $beneficiaryMember->update(['status' => 'Inactive']);
                AuditService::log('membership_closed', 'members', (string)$beneficiaryMember->id, ['status' => 'Active'], [
                    'status' => 'Inactive',
                    'reason' => "Membership closed upon marriage event ({$eventCode} - {$beneficiaryName})"
                ]);
            }
        }

        AuditService::log('create', 'events', (string)$event->id, null, [
            'code' => $eventCode,
            'title' => $event->title,
            'scheme_id' => $event->scheme_id,
            'contributions_generated' => $generatedCount
        ]);

        return redirect()->route('admin.events.contributions', $event->id)
            ->with('success', "कार्यक्रम {$eventCode} ({$beneficiaryName}) सफलतापूर्वक दर्ज किया गया! लाभार्थी सदस्य की सदस्यता विवाह संपन्न होने पर क्लोज (Inactive) कर दी गई है एवं अन्य सभी {$generatedCount} सक्रिय सदस्यों के खाते में अंशदान जुड़ गया है।");
    }

    public function update(Request $request, $id)
    {
        $event = MarriageEvent::findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:200',
            'girl_name' => 'nullable|string|max:100',
            'beneficiary_name' => 'nullable|string|max:100',
            'event_type' => 'nullable|string|max:191',
            'event_date' => 'required|date',
            'scheme_id' => 'nullable|exists:schemes,id',
            'target_amount' => 'nullable|numeric|min:0',
            'rate_per_event' => 'nullable|numeric|min:0',
            'venue' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:100',
            'member_id' => 'nullable|exists:members,id',
            'status' => 'nullable|string|in:Upcoming,Active,Completed,Cancelled',
            'description' => 'nullable|string',
        ]);

        $beneficiaryName = trim($request->beneficiary_name ?: $request->girl_name ?: $event->girl_name);
        $scheme = $request->scheme_id ? Scheme::find($request->scheme_id) : null;
        $targetAmount = $request->filled('target_amount') ? (float)$request->target_amount : $event->target_amount;

        $event->update([
            'title' => $request->filled('title') ? $request->title : $event->title,
            'event_type' => $request->filled('event_type') ? $request->event_type : ($scheme ? ($scheme->name_hindi ?: $scheme->name) : $event->event_type),
            'girl_name' => $beneficiaryName,
            'father_name' => $request->father_name,
            'member_id' => $request->member_id ?: null,
            'scheme_id' => $request->scheme_id ?: null,
            'event_date' => $request->event_date,
            'venue' => $request->venue ?: $event->venue,
            'target_amount' => $targetAmount,
            'beneficiary_payout_amount' => $targetAmount,
            'rate_per_event' => $request->rate_per_event ?? $event->rate_per_event,
            'status' => $request->status ?: $event->status,
            'description' => $request->description,
        ]);

        // When updated, close beneficiary member's membership if still active
        if ($event->member_id) {
            $beneficiaryMember = Member::find($event->member_id);
            if ($beneficiaryMember && $beneficiaryMember->status === 'Active') {
                $beneficiaryMember->update(['status' => 'Inactive']);
            }
        } elseif (!empty($beneficiaryName)) {
            $beneficiaryMember = Member::where('full_name', $beneficiaryName)->where('status', 'Active')->first();
            if ($beneficiaryMember) {
                $beneficiaryMember->update(['status' => 'Inactive']);
            }
        }

        // Exclude the beneficiary member from contributions
        if ($event->member_id) {
            \App\Models\EventContribution::where('event_id', $event->id)
                ->where('member_id', $event->member_id)
                ->delete();
        } elseif (!empty($event->girl_name)) {
            \App\Models\EventContribution::where('event_id', $event->id)
                ->where('member_name', trim($event->girl_name))
                ->delete();
        }

        $totalContributionSum = (float)$event->contributions()->sum('contribution_amount');
        if ((!$request->filled('target_amount') || (float)$request->target_amount <= 0) && $totalContributionSum > 0) {
            $event->update([
                'target_amount' => $totalContributionSum,
                'beneficiary_payout_amount' => $totalContributionSum,
            ]);
        }

        AuditService::log('update', 'events', (string)$event->id, null, [
            'code' => $event->event_code,
            'title' => $event->title,
            'scheme_id' => $event->scheme_id,
        ]);

        return redirect()->route('admin.events.index')
            ->with('success', "कार्यक्रम {$event->event_code} ({$beneficiaryName}) सफलतापूर्वक अपडेट किया गया।");
    }

    public function show(Request $request, $id)
    {
        return $this->edit($request, $id);
    }

    public function edit(Request $request, $id)
    {
        $event = MarriageEvent::with(['member', 'scheme', 'contributions'])->findOrFail($id);
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($event);
        }

        $events = MarriageEvent::with(['member', 'scheme'])
            ->withCount([
                'contributions',
                'contributions as paid_count' => function ($q) {
                    $q->where('payment_status', 'Paid');
                },
            ])
            ->withSum('contributions as expected_sum', 'contribution_amount')
            ->withSum(['contributions as collected_sum' => function ($q) {
                $q->where('payment_status', 'Paid');
            }], 'contribution_amount')
            ->latest('event_date')
            ->paginate(10);

        $schemes = Scheme::where('status', 'Active')->get();
        $billings = EventBilling::with(['event', 'creator'])->latest('billing_date')->take(10)->get();

        $members = Member::where('status', 'Active')
            ->select('id', 'membership_no', 'full_name', 'gender', 'scheme_id', 'age', 'dob', 'father_spouse_name')
            ->orderBy('full_name')
            ->get();

        $beneficiariesList = collect();
        foreach ($members as $mem) {
            $isFemale = strtolower($mem->gender ?? '') === 'female';
            $targetType = $isFemale ? 'daughter' : 'son';

            $beneficiariesList->push([
                'type' => 'member',
                'target_type' => $targetType,
                'beneficiary_name' => $mem->full_name,
                'father_name' => $mem->father_spouse_name ?: '',
                'member_id' => $mem->id,
                'scheme_id' => $mem->scheme_id,
                'member_name' => $mem->full_name,
                'membership_no' => $mem->membership_no,
                'label' => "{$mem->full_name} [Member: {$mem->membership_no}]",
            ]);
        }
        $girlsList = $beneficiariesList;
        $editEvent = $event;

        return view('admin.events.index', compact('events', 'members', 'schemes', 'billings', 'beneficiariesList', 'girlsList', 'editEvent'));
    }

    public function destroy($id)
    {
        $event = MarriageEvent::findOrFail($id);
        $event->contributions()->delete();
        $event->delete();

        AuditService::log('delete', 'events', (string)$id, null, [
            'code' => $event->event_code,
            'title' => $event->title,
        ]);

        return redirect()->route('admin.events.index')->with('success', "Event {$event->event_code} removed successfully.");
    }

    /**
     * Live Preview of Active Members, Age Slabs, and Contribution amounts.
     */
    public function previewSchemeMembers(Request $request)
    {
        $request->validate([
            'scheme_id' => 'nullable',
            'event_date' => 'nullable|date',
        ]);

        $schemeId = $request->filled('scheme_id') ? (int)$request->scheme_id : null;

        $preview = \App\Services\ContributionCalculationService::getPreviewForScheme(
            $schemeId,
            $request->event_date
        );

        return response()->json($preview);
    }

    /**
     * Dedicated Event Contributions List & Tracking page (Master List).
     */
    public function contributions($id, Request $request)
    {
        $event = MarriageEvent::with(['scheme', 'member'])->findOrFail($id);

        if ($request->get('export') === 'csv') {
            return $this->exportContributions($id, $request);
        }

        $user = auth()->user();
        $isAgent = $user && $user->isAgent() && $user->agent_id;
        $agents = $isAgent ? Agent::where('id', $user->agent_id)->get() : Agent::where('status', 'Active')->orderBy('name')->get();

        $query = $event->contributions()->with(['member.scheme', 'member.ageSlab', 'member.agent', 'payment', 'agent']);

        if ($isAgent) {
            $query->where(function ($q) use ($user) {
                $q->where('agent_id', $user->agent_id)
                  ->orWhereHas('member', function ($mq) use ($user) {
                      $mq->where('agent_id', $user->agent_id);
                  });
            });
        } elseif ($request->filled('agent_id')) {
            $agentId = $request->agent_id;
            $query->where(function ($q) use ($agentId) {
                $q->where('agent_id', $agentId)
                  ->orWhereHas('member', function ($mq) use ($agentId) {
                      $mq->where('agent_id', $agentId);
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = \App\Helpers\Helper::likeEscape($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('member_name', 'like', "%{$search}%")
                  ->orWhere('receipt_no', 'like', "%{$search}%")
                  ->orWhere('age_slab', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($mq) use ($search) {
                      $mq->where('membership_no', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%")
                         ->orWhere('father_spouse_name', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->get('per_page', 25);
        if ($perPage === 'all') {
            $contributions = $query->orderBy('payment_status', 'asc')->orderBy('member_name', 'asc')->paginate(2000)->withQueryString();
        } else {
            $contributions = $query->orderBy('payment_status', 'asc')->orderBy('member_name', 'asc')->paginate((int)$perPage)->withQueryString();
        }

        $statsQuery = $event->contributions();
        if ($isAgent) {
            $statsQuery->where(function ($q) use ($user) {
                $q->where('agent_id', $user->agent_id)
                  ->orWhereHas('member', function ($mq) use ($user) {
                      $mq->where('agent_id', $user->agent_id);
                  });
            });
        }

        $rawStats = $statsQuery
            ->selectRaw("
                COUNT(*) as total_members,
                COALESCE(SUM(contribution_amount), 0) as total_expected,
                COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN contribution_amount ELSE 0 END), 0) as total_collected,
                COALESCE(SUM(CASE WHEN payment_status = 'Pending' THEN contribution_amount ELSE 0 END), 0) as total_pending,
                COUNT(CASE WHEN payment_status = 'Paid' THEN 1 END) as paid_count,
                COUNT(CASE WHEN payment_status = 'Pending' THEN 1 END) as pending_count
            ")
            ->first();

        $totalExpected = (float)($rawStats->total_expected ?? 0);
        $totalCollected = (float)($rawStats->total_collected ?? 0);
        $totalPending = (float)($rawStats->total_pending ?? 0);
        $totalMembers = (int)($rawStats->total_members ?? 0);
        $paidCount = (int)($rawStats->paid_count ?? 0);
        $pendingCount = (int)($rawStats->pending_count ?? 0);
        $collectionPercentage = $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 1) : 0;

        $stats = [
            'total_members' => $totalMembers,
            'total_expected' => $totalExpected,
            'total_collected' => $totalCollected,
            'total_pending' => $totalPending,
            'paid_count' => $paidCount,
            'pending_count' => $pendingCount,
            'collection_percentage' => $collectionPercentage,
        ];

        return view('admin.events.contributions', compact('event', 'contributions', 'stats', 'agents'));
    }

    /**
     * Print View for Event Contributions Master List.
     */
    public function printContributions($id, Request $request)
    {
        $event = MarriageEvent::with(['scheme', 'member'])->findOrFail($id);
        $user = auth()->user();
        $isAgent = $user && $user->isAgent() && $user->agent_id;

        $query = $event->contributions()->with(['member.scheme', 'member.ageSlab', 'member.agent', 'payment', 'agent']);

        if ($isAgent) {
            $query->where(function ($q) use ($user) {
                $q->where('agent_id', $user->agent_id)
                  ->orWhereHas('member', function ($mq) use ($user) {
                      $mq->where('agent_id', $user->agent_id);
                  });
            });
        } elseif ($request->filled('agent_id')) {
            $agentId = $request->agent_id;
            $query->where(function ($q) use ($agentId) {
                $q->where('agent_id', $agentId)
                  ->orWhereHas('member', function ($mq) use ($agentId) {
                      $mq->where('agent_id', $agentId);
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = \App\Helpers\Helper::likeEscape($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('member_name', 'like', "%{$search}%")
                  ->orWhere('receipt_no', 'like', "%{$search}%")
                  ->orWhere('age_slab', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($mq) use ($search) {
                      $mq->where('membership_no', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%")
                         ->orWhere('father_spouse_name', 'like', "%{$search}%");
                  });
            });
        }

        $contributions = $query->orderBy('payment_status', 'asc')->orderBy('member_name', 'asc')->get();

        $statsQuery = $event->contributions();
        if ($isAgent) {
            $statsQuery->where(function ($q) use ($user) {
                $q->where('agent_id', $user->agent_id)
                  ->orWhereHas('member', function ($mq) use ($user) {
                      $mq->where('agent_id', $user->agent_id);
                  });
            });
        }

        $rawStats = $statsQuery
            ->selectRaw("
                COUNT(*) as total_members,
                COALESCE(SUM(contribution_amount), 0) as total_expected,
                COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN contribution_amount ELSE 0 END), 0) as total_collected,
                COALESCE(SUM(CASE WHEN payment_status = 'Pending' THEN contribution_amount ELSE 0 END), 0) as total_pending,
                COUNT(CASE WHEN payment_status = 'Paid' THEN 1 END) as paid_count,
                COUNT(CASE WHEN payment_status = 'Pending' THEN 1 END) as pending_count
            ")
            ->first();

        $totalExpected = (float)($rawStats->total_expected ?? 0);
        $totalCollected = (float)($rawStats->total_collected ?? 0);
        $totalPending = (float)($rawStats->total_pending ?? 0);
        $totalMembers = (int)($rawStats->total_members ?? 0);
        $paidCount = (int)($rawStats->paid_count ?? 0);
        $pendingCount = (int)($rawStats->pending_count ?? 0);
        $collectionPercentage = $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 1) : 0;

        $stats = [
            'total_members' => $totalMembers,
            'total_expected' => $totalExpected,
            'total_collected' => $totalCollected,
            'total_pending' => $totalPending,
            'paid_count' => $paidCount,
            'pending_count' => $pendingCount,
            'collection_percentage' => $collectionPercentage,
        ];

        return view('admin.events.contributions_print', compact('event', 'contributions', 'stats'));
    }

    /**
     * CSV Master List Export for Event Contributions.
     */
    public function exportContributions($id, Request $request)
    {
        $event = MarriageEvent::with(['scheme', 'member'])->findOrFail($id);
        $user = auth()->user();
        $isAgent = $user && $user->isAgent() && $user->agent_id;

        $query = $event->contributions()->with(['member.scheme', 'member.ageSlab', 'member.agent', 'payment', 'agent']);

        if ($isAgent) {
            $query->where(function ($q) use ($user) {
                $q->where('agent_id', $user->agent_id)
                  ->orWhereHas('member', function ($mq) use ($user) {
                      $mq->where('agent_id', $user->agent_id);
                  });
            });
        } elseif ($request->filled('agent_id')) {
            $agentId = $request->agent_id;
            $query->where(function ($q) use ($agentId) {
                $q->where('agent_id', $agentId)
                  ->orWhereHas('member', function ($mq) use ($agentId) {
                      $mq->where('agent_id', $agentId);
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = \App\Helpers\Helper::likeEscape($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('member_name', 'like', "%{$search}%")
                  ->orWhere('receipt_no', 'like', "%{$search}%")
                  ->orWhere('age_slab', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($mq) use ($search) {
                      $mq->where('membership_no', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%")
                         ->orWhere('father_spouse_name', 'like', "%{$search}%");
                  });
            });
        }

        $contributions = $query->orderBy('payment_status', 'asc')->orderBy('member_name', 'asc')->get();

        $fileName = "Event_MasterList_{$event->event_code}_" . date('Ymd_His') . ".csv";

        return new StreamedResponse(function () use ($event, $contributions) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['श्री श्याम वेलफेयर सोसायटी, लोहीकी - कार्यक्रम अंशदान मास्टर लिस्ट']);
            fputcsv($handle, ['कार्यक्रम कोड (Event Code)', $event->event_code, 'कार्यक्रम शीर्षक (Event Title)', $event->title]);
            fputcsv($handle, ['लाभार्थी / कन्या (Beneficiary)', $event->girl_name, 'दिनांक (Event Date)', $event->event_date ? $event->event_date->format('d/m/Y') : 'N/A']);
            fputcsv($handle, ['स्थल (Venue)', $event->venue, 'योजना (Scheme)', $event->scheme ? $event->scheme->name_hindi : 'All Schemes']);
            fputcsv($handle, []);

            fputcsv($handle, [
                'क्र. सं. (Sr No)',
                'सदस्यता क्र. (Membership No)',
                'सदस्य का नाम (Member Name)',
                'पिता / पति का नाम (Father/Spouse)',
                'मोबाइल नं. (Mobile)',
                'योजना (Scheme)',
                'आयु (Age)',
                'आयु वर्ग (Age Slab)',
                'अधिकृत कार्यकर्ता / एजेंट (Agent)',
                'अपेक्षित अंशदान ₹ (Expected Amount)',
                'कलेक्शन स्थिति (Payment Status)',
                'प्राप्त राशि ₹ (Paid Amount)',
                'रसीद क्र. (Receipt No)',
                'जमा दिनांक (Payment Date)',
            ]);

            $i = 1;
            foreach ($contributions as $c) {
                $paidAmt = $c->payment_status === 'Paid' ? $c->contribution_amount : 0;
                $agentName = $c->agent ? $c->agent->name : ($c->member && $c->member->agent ? $c->member->agent->name : 'HQ Direct');
                fputcsv($handle, [
                    $i++,
                    $c->member ? $c->member->membership_no : 'N/A',
                    $c->member_name,
                    $c->member ? $c->member->father_spouse_name : '',
                    $c->member ? $c->member->mobile : '',
                    $c->scheme ? $c->scheme->name_hindi : ($c->member && $c->member->scheme ? $c->member->scheme->name_hindi : ''),
                    $c->member_age ? $c->member_age . ' वर्ष' : '',
                    $c->age_slab ?? '',
                    $agentName,
                    $c->contribution_amount,
                    $c->payment_status,
                    $paidAmt,
                    $c->receipt_no ?? '',
                    $c->payment_date ? $c->payment_date->format('d/m/Y') : '',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function billMembers(Request $request)
    {
        $request->validate([
            'event_id' => 'nullable|exists:marriage_events,id',
            'billing_month' => 'required|string',
            'rate_type' => 'nullable|string|in:member_slab,fixed_rate',
            'events_count' => 'nullable|integer|min:1',
            'rate_per_event' => 'nullable|numeric|min:0',
            'scheme_id' => 'nullable|exists:schemes,id',
        ]);

        try {
            $billing = EventBillingService::processConsolidatedBilling($request->only([
                'event_id', 'billing_month', 'rate_type', 'events_count', 'rate_per_event', 'scheme_id', 'billing_date'
            ]));

            return back()->with('success', "Consolidated billing for {$billing->month_name} generated successfully for {$billing->billed_members_count} active members based on member rates. Total billed: ₹" . number_format($billing->total_billing_amount, 2));
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
                $msgLines[] = "   सहयोग दर: योजना/स्लैब नियमानुसार";
                $i++;
            }
            $msgLines[] = "------------------------------------";
            $msgLines[] = "कुल कार्यक्रम: {$eventsCount}";
            $msgLines[] = "सहयोग गणना: [इस माह का सहयोग: कुल कार्यक्रम × सदस्य दर] + [पिछला बकाया] = [कुल देय]";
        } else {
            $msgLines[] = "इस माह में अभी कोई पंजीकृत विवाह कार्यक्रम नहीं है।";
            $msgLines[] = "मासिक सहयोग दर: सदस्य स्लैब नियमानुसार";
        }

        $msgLines[] = "------------------------------------";
        $msgLines[] = "सभी सम्मानित सदस्यों से विनम्र निवेदन है कि अपनी सहयोग राशि समय पर अधिकृत प्रतिनिधि (एजेंट) के पास अथवा सीधे सोसायटी खाते में जमा करवाकर रसीद अवश्य प्राप्त करें।";
        $msgLines[] = "";
        $msgLines[] = "भवदीय,";
        $msgLines[] = "श्री श्याम वेलफेयर सोसायटी लोहीकी";
        $msgLines[] = "जय श्री श्याम 🙏";

        $defaultMessage = implode("\n", $msgLines);

        // Compute Per-Member breakdown for dispatch preview
        $activeMembers = Member::with(['scheme', 'ageSlab', 'agent'])->where('status', 'Active')->orderBy('full_name')->get();
        $membersPreview = [];
        $grandThisMonthTotal = 0;
        $grandPreviousDueTotal = 0;

        foreach ($activeMembers as $m) {
            $rate = (float)($m->monthly_support_amount ?: ($m->ageSlab ? $m->ageSlab->support_amount : 200.0));
            $thisMonthAmt = $eventsCount * $rate;
            $prevDue = (float)$m->pending_amount;
            $totalDue = $thisMonthAmt + $prevDue;

            $grandThisMonthTotal += $thisMonthAmt;
            $grandPreviousDueTotal += $prevDue;

            $personalLines = [];
            $personalLines[] = "जय श्री श्याम 🙏";
            $personalLines[] = "श्री श्याम वेलफेयर सोसायटी, लोहीकी";
            $personalLines[] = "प्रिय सदस्य: श्री " . $m->full_name . " (" . $m->membership_no . ")";
            $personalLines[] = "📢 माह " . $formattedMonth . " के कन्या विवाह कार्यक्रम";
            $personalLines[] = "------------------------------------";

            if ($eventsCount > 0) {
                $i = 1;
                foreach ($events as $ev) {
                    $evDate = $ev->event_date ? $ev->event_date->format('d/m/Y') : 'N/A';
                    $father = $ev->father_name ? " (पिता: {$ev->father_name})" : '';
                    $personalLines[] = "{$i}. कन्या: {$ev->girl_name}{$father}";
                    $personalLines[] = "   दिनांक: {$evDate} | स्थल: {$ev->venue}";
                    $i++;
                }
                $personalLines[] = "------------------------------------";
                $personalLines[] = "कुल कार्यक्रम: {$eventsCount}";
                $personalLines[] = "आपकी निर्धारित दर: ₹" . number_format($rate, 0) . "/कार्यक्रम";
            } else {
                $personalLines[] = "मासिक सहयोग दर: ₹" . number_format($rate, 0);
            }

            $personalLines[] = "------------------------------------";
            $personalLines[] = "📊 देय राशि विवरण:";
            $personalLines[] = "• इस माह का सहयोग: ₹" . number_format($thisMonthAmt, 0);
            $personalLines[] = "• पिछला बकाया (Due): ₹" . number_format($prevDue, 0);
            $personalLines[] = "💰 कुल देय राशि (Total Due): ₹" . number_format($totalDue, 0);
            $personalLines[] = "------------------------------------";
            if ($m->agent) {
                $personalLines[] = "अधिकृत कार्यकर्ता: " . $m->agent->name . " (मो. " . $m->agent->mobile . ")";
            }
            $personalLines[] = "कृपया अपनी सहयोग राशि समय पर जमा करवाकर रसीद प्राप्त करें।";
            $personalLines[] = "जय श्री श्याम 🙏";

            $personalMsg = implode("\n", $personalLines);
            $cleanMobile = preg_replace('/[^0-9]/', '', $m->mobile ?? '');
            if (strlen($cleanMobile) === 10) {
                $cleanMobile = '91' . $cleanMobile;
            }
            $waUrl = $cleanMobile ? "https://api.whatsapp.com/send?phone={$cleanMobile}&text=" . urlencode($personalMsg) : '#';

            $membersPreview[] = [
                'id' => $m->id,
                'name' => $m->full_name,
                'membership_no' => $m->membership_no,
                'mobile' => $m->mobile,
                'scheme_name' => $m->scheme ? $m->scheme->name_hindi : 'N/A',
                'rate' => $rate,
                'this_month' => $thisMonthAmt,
                'previous_due' => $prevDue,
                'total_due' => $totalDue,
                'agent_name' => $m->agent ? $m->agent->name : 'HQ Direct',
                'whatsapp_url' => $waUrl,
                'personal_message' => $personalMsg,
            ];
        }

        return response()->json([
            'month' => $monthStr,
            'month_name' => $formattedMonth,
            'events_count' => $eventsCount,
            'total_rate' => $totalRate,
            'events' => $events,
            'default_message' => $defaultMessage,
            'members_preview' => $membersPreview,
            'total_members_count' => count($membersPreview),
            'grand_this_month_total' => $grandThisMonthTotal,
            'grand_previous_due_total' => $grandPreviousDueTotal,
            'grand_total_due' => $grandThisMonthTotal + $grandPreviousDueTotal,
        ]);
    }

    public function sendBroadcast(Request $request)
    {
        $request->validate([
            'month' => 'required|string',
            'message' => 'nullable|string',
        ]);

        $monthStr = $request->month;
        [$year, $month] = explode('-', $monthStr);
        $events = MarriageEvent::whereYear('event_date', $year)->whereMonth('event_date', $month)->get();
        $eventsCount = $events->count();

        $members = Member::with(['scheme', 'ageSlab', 'agent'])->where('status', 'Active')->whereNotNull('mobile')->get();
        if ($members->isEmpty()) {
            return back()->with('error', 'No active members with phone numbers found.');
        }

        $sentCount = 0;
        foreach ($members as $m) {
            $rate = (float)($m->monthly_support_amount ?: ($m->ageSlab ? $m->ageSlab->support_amount : 200.0));
            $thisMonthAmt = $eventsCount * $rate;
            $prevDue = (float)$m->pending_amount;
            $totalDue = $thisMonthAmt + $prevDue;

            // If custom message text passed without placeholders, use it or personalize
            $body = $request->filled('message') ? $request->message : '';
            $body = str_replace(
                ['{{member_name}}', '{{membership_no}}', '{{this_month}}', '{{previous_due}}', '{{total_due}}', '{{rate}}'],
                [$m->full_name, $m->membership_no, '₹' . number_format($thisMonthAmt, 0), '₹' . number_format($prevDue, 0), '₹' . number_format($totalDue, 0), '₹' . number_format($rate, 0)],
                $body
            );

            \App\Models\WhatsAppLog::create([
                'member_id' => $m->id,
                'recipient_name' => $m->full_name,
                'mobile' => $m->mobile,
                'message_type' => 'Monthly Events Broadcast (' . $request->month . ')',
                'message_body' => $body ?: "माह {$monthStr} के {$eventsCount} कार्यक्रमों का देय: ₹" . number_format($thisMonthAmt, 0) . " + पिछला बकाया: ₹" . number_format($prevDue, 0) . " = कुल ₹" . number_format($totalDue, 0),
                'status' => 'Queued',
                'sent_at' => now(),
            ]);
            $sentCount++;
        }

        AuditService::log('create', 'whatsapp_broadcast', $request->month, null, [
            'members_count' => $sentCount,
            'month' => $request->month,
        ]);

        $encodedMsg = urlencode($request->message ?? "माह {$request->month} के कार्यक्रम सूचना");
        $whatsappUrl = "https://api.whatsapp.com/send?text={$encodedMsg}";

        return back()->with([
            'success' => "माह {$request->month} के सभी {$sentCount} सदस्यों का व्यक्तिगत बिल संदेश तैयार और लॉग कर दिया गया है!",
            'whatsapp_broadcast_url' => $whatsappUrl,
        ]);
    }
}
