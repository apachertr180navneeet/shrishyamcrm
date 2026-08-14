<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payout;
use App\Models\MarriageEvent;
use App\Models\Member;
use App\Models\Scheme;
use App\Services\PayoutService;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = Payout::with(['event', 'member', 'scheme'])->latest('payout_date')->get();
        $events = MarriageEvent::where('status', '!=', 'Completed')->get();
        $members = Member::where('status', 'Active')->get();
        $schemes = Scheme::where('status', 'Active')->get();
        $totalDisbursed = Payout::where('status', 'Disbursed')->sum('amount');
        $totalPending = Payout::where('status', 'Pending Approval')->sum('amount');

        return view('admin.payouts.index', compact('payouts', 'events', 'members', 'schemes', 'totalDisbursed', 'totalPending'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'beneficiary_name' => 'required|string|max:150',
            'amount' => 'required|numeric|min:1',
            'payout_date' => 'required|date',
            'payment_mode' => 'required|string',
            'payout_type' => 'required|string',
        ]);

        try {
            $payout = PayoutService::createPayout($request->all());

            return back()->with('success', "Beneficiary payout {$payout->payout_no} of ₹{$payout->amount} recorded successfully!");
        } catch (\Exception $e) {
            return back()->with('error', 'Error recording payout: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending Approval,Approved,Disbursed,Rejected',
        ]);

        try {
            $payout = PayoutService::updateStatus($id, $request->status, $request->remarks);

            return back()->with('success', "Payout {$payout->payout_no} status updated to {$payout->status}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating payout status: ' . $e->getMessage());
        }
    }
}
