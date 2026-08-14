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
        $events = MarriageEvent::with(['member', 'payouts', 'billings', 'scheme'])->latest('event_date')->get();
        $members = Member::where('status', 'Active')->get();
        $schemes = Scheme::where('status', 'Active')->get();
        $billings = EventBilling::with(['event', 'scheme'])->latest('billing_date')->take(10)->get();

        return view('admin.events.index', compact('events', 'members', 'schemes', 'billings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'girl_name' => 'required|string|max:100',
            'event_date' => 'required|date',
            'target_amount' => 'required|numeric|min:0',
        ]);

        $eventCode = NumberSeriesService::getNextNumber('EVT', ['prefix' => 'EVT-' . date('Y') . '-', 'initial_value' => 1, 'padding' => 2]);

        $event = MarriageEvent::create([
            'event_code' => $eventCode,
            'title' => $request->title,
            'event_type' => $request->event_type ?? 'Marriage Support',
            'girl_name' => $request->girl_name,
            'father_name' => $request->father_name,
            'member_id' => $request->member_id,
            'scheme_id' => $request->scheme_id,
            'event_date' => $request->event_date,
            'venue' => $request->venue,
            'target_amount' => $request->target_amount,
            'collected_amount' => 0,
            'beneficiary_payout_amount' => $request->target_amount,
            'rate_per_event' => $request->rate_per_event ?? 200.00,
            'status' => 'Upcoming',
            'description' => $request->description,
        ]);

        AuditService::log('create', 'events', (string)$event->id, null, ['code' => $eventCode, 'title' => $event->title]);

        return back()->with('success', "Society Event {$eventCode} created successfully!");
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
            $billing = EventBillingService::processConsolidatedBilling($request->all());

            return back()->with('success', "Consolidated billing for {$billing->month_name} applied successfully to {$billing->billed_members_count} active members. Total: ₹" . number_format($billing->total_billing_amount, 2));
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing consolidated event billing: ' . $e->getMessage());
        }
    }
}
