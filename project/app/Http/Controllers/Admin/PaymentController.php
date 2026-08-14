<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Member;
use App\Models\Agent;
use App\Services\PaymentService;
use App\Services\ReceiptService;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Payment::with(['member.scheme', 'agent']);

        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }

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
        $totalCollected = (clone $query)->where('status', 'Verified')->sum('amount');

        return view('admin.payments.index', compact('payments', 'totalCollected'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $membersQuery = Member::with(['scheme', 'agent'])->where('status', 'Active');
        if ($user && $user->isAgent() && $user->agent_id) {
            $membersQuery->where('agent_id', $user->agent_id);
        }
        $members = $membersQuery->get();
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
            $payment = PaymentService::processPayment($request->all());

            return redirect()->route('admin.payments.receipt', $payment->id)
                ->with('success', "Payment of ₹{$request->amount} recorded successfully. Receipt No: {$payment->receipt_no}");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    public function receipts(Request $request)
    {
        $user = auth()->user();
        $query = Payment::with(['member.scheme', 'agent'])->where('status', 'Verified');

        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }

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
        $user = auth()->user();
        $query = Payment::with(['member.scheme', 'member.agent', 'agent']);
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $payment = $query->findOrFail($id);
        $whatsappData = WhatsAppService::getReceiptMessage($payment);

        return view('admin.payments.receipt', compact('payment', 'whatsappData'));
    }

    public function receiptPdf($id)
    {
        $pdf = ReceiptService::generatePdf($id);
        return $pdf->download("SSWS_Receipt_{$id}.pdf");
    }

    public function ledger(Request $request)
    {
        $user = auth()->user();
        $membersQuery = Member::where('status', 'Active');
        if ($user && $user->isAgent() && $user->agent_id) {
            $membersQuery->where('agent_id', $user->agent_id);
        }
        $members = $membersQuery->get();

        $selectedMember = null;
        $ledgerEntries = collect();

        if ($request->filled('member_id')) {
            $selectedMember = Member::with(['scheme', 'agent', 'ledgers.creator', 'payments'])->find($request->member_id);
            if ($selectedMember) {
                $ledgerEntries = $selectedMember->ledgers()->orderBy('transaction_date')->orderBy('id')->get();
            }
        }

        return view('admin.payments.ledger', compact('members', 'selectedMember', 'ledgerEntries'));
    }
}
