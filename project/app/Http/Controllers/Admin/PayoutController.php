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
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if ($user && $user->isAgent() && !$user->hasRole(['admin', 'super_admin'])) {
                abort(403, 'Unauthorized. Beneficiary payouts can only be accessed by Admin.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $payouts = Payout::with(['event', 'member', 'scheme'])->latest('id')->get();
        $events = MarriageEvent::where('status', '!=', 'Completed')->get();
        $members = Member::where('status', 'Active')->get();
        $schemes = Scheme::where('status', 'Active')->get();
        $totalDisbursed = Payout::where('status', 'Disbursed')->sum('amount');
        $totalPending = Payout::where('status', 'Pending Approval')->sum('amount');
        $nextPayoutNo = \App\Services\NumberSeriesService::peekNextNumber('PAY', ['prefix' => 'PAY-' . date('Y') . '-', 'initial_value' => 1, 'padding' => 3]);

        return view('admin.payouts.index', compact('payouts', 'events', 'members', 'schemes', 'totalDisbursed', 'totalPending', 'nextPayoutNo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'beneficiary_name' => 'required|string|max:150',
            'amount' => 'required|numeric|min:1',
            'payout_date' => 'required|date',
            'payment_mode' => 'required|string',
            'payout_type' => 'nullable|string',
            'event_id' => 'nullable|exists:marriage_events,id',
            'member_id' => 'nullable|exists:members,id',
            'scheme_id' => 'nullable|exists:schemes,id',
            'relation' => 'nullable|string|max:100',
            'transaction_ref' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:Disbursed,Pending Approval,Approved',
        ]);

        try {
            $data = $request->only([
                'event_id', 'member_id', 'scheme_id', 'payout_type', 'beneficiary_name',
                'relation', 'amount', 'payout_date', 'payment_mode', 'transaction_ref', 'remarks', 'status'
            ]);
            $data['payout_type'] = $request->payout_type ?: 'Marriage Assistance';
            $data['status'] = $request->status ?: 'Disbursed';

            $payout = PayoutService::createPayout($data);

            return back()->with('success', "Beneficiary payout {$payout->payout_no} of ₹" . number_format($payout->amount) . " recorded successfully!");
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
