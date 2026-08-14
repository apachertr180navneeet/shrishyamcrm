<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarriageEvent;
use App\Models\Member;
use App\Models\Payment;
use DB;

class MarriageEventController extends Controller
{
    public function index()
    {
        $events = MarriageEvent::with(['member', 'payouts'])->latest('event_date')->get();
        $members = Member::where('status', 'Active')->get();

        return view('admin.events.index', compact('events', 'members'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'girl_name' => 'required|string|max:100',
            'event_date' => 'required|date',
            'target_amount' => 'required|numeric|min:0',
        ]);

        $count = MarriageEvent::count() + 1;
        $eventCode = 'EVT-2026-' . str_pad($count, 2, '0', STR_PAD_LEFT);

        MarriageEvent::create([
            'event_code' => $eventCode,
            'title' => $request->title,
            'girl_name' => $request->girl_name,
            'father_name' => $request->father_name,
            'member_id' => $request->member_id,
            'event_date' => $request->event_date,
            'venue' => $request->venue,
            'target_amount' => $request->target_amount,
            'collected_amount' => 0,
            'beneficiary_payout_amount' => $request->target_amount,
            'status' => 'Upcoming',
            'description' => $request->description,
        ]);

        return back()->with('success', "Marriage Event {$eventCode} created successfully!");
    }

    public function billMembers(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:marriage_events,id',
            'contribution_amount' => 'required|numeric|min:50',
        ]);

        $event = MarriageEvent::findOrFail($request->event_id);
        $members = Member::where('status', 'Active')->get();
        $amount = (float)$request->contribution_amount;

        foreach ($members as $m) {
            $m->pending_amount += $amount;
            $m->save();
        }

        return back()->with('success', "Event billing of ₹{$amount} applied to all " . count($members) . " active members for event {$event->event_code}.");
    }
}
