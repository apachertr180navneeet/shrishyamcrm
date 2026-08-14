<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Member;
use App\Models\Agent;
use Carbon\Carbon;
use DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['member.scheme', 'agent']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($mq) use ($search) {
                      $mq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('membership_no', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();
        $totalCollected = Payment::where('status', 'Verified')->sum('amount');

        return view('admin.payments.index', compact('payments', 'totalCollected'));
    }

    public function create(Request $request)
    {
        $members = Member::with(['scheme', 'agent'])->where('status', 'Active')->get();
        $agents = Agent::where('status', 'Active')->get();
        $selectedMemberId = $request->member_id;

        return view('admin.payments.create', compact('members', 'agents', 'selectedMemberId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:1',
            'payment_type' => 'required|string',
            'payment_mode' => 'required|string',
            'payment_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $member = Member::findOrFail($request->member_id);
            $receiptNo = 'REC-2026-' . (5000 + Payment::count() + 1);

            $payment = Payment::create([
                'receipt_no' => $receiptNo,
                'san_code' => 'SAN-LOH-' . str_pad($member->id, 3, '0', STR_PAD_LEFT),
                'member_id' => $member->id,
                'agent_id' => $request->agent_id ?: $member->agent_id,
                'amount' => $request->amount,
                'payment_type' => $request->payment_type,
                'payment_mode' => $request->payment_mode,
                'reference_no' => $request->reference_no ?? ('TXN' . rand(100000, 999999)),
                'month_year' => Carbon::parse($request->payment_date)->format('M Y'),
                'payment_date' => $request->payment_date,
                'status' => 'Verified',
                'collected_by' => $request->collected_by ?? (auth()->user()->full_name ?? 'Admin'),
                'remarks' => $request->remarks,
            ]);

            // Update member total paid and adjust pending
            $member->total_paid += $request->amount;
            if ($member->pending_amount > 0) {
                $member->pending_amount = max(0, $member->pending_amount - $request->amount);
            }
            $member->save();

            DB::commit();

            return redirect()->route('admin.payments.receipt', $payment->id)->with('success', "Payment of ₹{$request->amount} recorded successfully. Receipt No: {$receiptNo}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    public function receipts(Request $request)
    {
        $query = Payment::with(['member.scheme', 'agent'])->where('status', 'Verified');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($mq) use ($search) {
                      $mq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('membership_no', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();

        return view('admin.payments.receipts', compact('payments'));
    }

    public function receipt($id)
    {
        $payment = Payment::with(['member.scheme', 'member.agent', 'agent'])->findOrFail($id);
        return view('admin.payments.receipt', compact('payment'));
    }

    public function ledger(Request $request)
    {
        $members = Member::where('status', 'Active')->get();
        $selectedMember = null;
        $ledgerEntries = collect();

        if ($request->filled('member_id')) {
            $selectedMember = Member::with(['scheme', 'agent', 'payments'])->find($request->member_id);
            if ($selectedMember) {
                $ledgerEntries = $selectedMember->payments;
            }
        }

        return view('admin.payments.ledger', compact('members', 'selectedMember', 'ledgerEntries'));
    }
}
