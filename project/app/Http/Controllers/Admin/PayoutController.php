<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payout;
use App\Models\MarriageEvent;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = Payout::with('event')->latest('payout_date')->get();
        $events = MarriageEvent::where('status', '!=', 'Completed')->get();
        $totalDisbursed = Payout::where('status', 'Disbursed')->sum('amount');

        return view('admin.payouts.index', compact('payouts', 'events', 'totalDisbursed'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:marriage_events,id',
            'beneficiary_name' => 'required|string|max:150',
            'amount' => 'required|numeric|min:1',
            'payout_date' => 'required|date',
            'payment_mode' => 'required|string',
        ]);

        $count = Payout::count() + 1;
        $payoutNo = 'PAY-2026-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $payout = Payout::create([
            'payout_no' => $payoutNo,
            'event_id' => $request->event_id,
            'beneficiary_name' => $request->beneficiary_name,
            'relation' => $request->relation ?? 'Beneficiary',
            'amount' => $request->amount,
            'payout_date' => $request->payout_date,
            'approved_by' => auth()->user()->full_name ?? 'Super Admin',
            'payment_mode' => $request->payment_mode,
            'transaction_ref' => $request->transaction_ref ?? ('UTR' . rand(100000, 999999)),
            'status' => 'Disbursed',
            'remarks' => $request->remarks,
        ]);

        // Update event status if fully paid out
        $event = MarriageEvent::find($request->event_id);
        if ($event) {
            $event->status = 'Completed';
            $event->save();
        }

        return back()->with('success', "Beneficiary payout {$payoutNo} of ₹{$request->amount} disbursed successfully!");
    }
}
