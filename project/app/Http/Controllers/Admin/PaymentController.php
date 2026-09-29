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
        $query = Payment::with(['member.scheme', 'agent', 'event']);

        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }

        if ($request->filled('search')) {
            $search = \App\Helpers\Helper::likeEscape($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($mq) use ($search) {
                      $mq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('membership_no', 'like', "%{$search}%");
                  })
                  ->orWhereHas('event', function ($eq) use ($search) {
                      $eq->where('title', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();
        $totalCollected = (clone $query)->where('status', 'Verified')->sum('amount');
        $events = \App\Models\MarriageEvent::orderBy('title')->get();

        return view('admin.payments.index', compact('payments', 'totalCollected', 'events'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $membersQuery = Member::with(['scheme', 'ageSlab', 'agent'])->where('status', 'Active');
        $isAgent = $user && $user->isAgent() && $user->agent_id;
        if ($isAgent) {
            $membersQuery->where('agent_id', $user->agent_id);
        }
        $members = $membersQuery->orderBy('full_name')->get();
        $agents = $isAgent ? Agent::where('id', $user->agent_id)->get() : Agent::where('status', 'Active')->orderBy('name')->get();
        $selectedMemberId = $request->member_id;

        $selectedContribution = null;
        if ($request->filled('contribution_id')) {
            $selectedContribution = \App\Models\EventContribution::with(['event', 'member.scheme', 'member.ageSlab'])->find($request->contribution_id);
            if ($selectedContribution) {
                $selectedMemberId = $selectedContribution->member_id;
            }
        }

        $events = \App\Models\MarriageEvent::with('scheme')->orderBy('title')->get();

        return view('admin.payments.create', compact('members', 'agents', 'selectedMemberId', 'selectedContribution', 'events'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:1',
            'payment_type' => 'required|string|in:Joining Fee,Monthly Support,Event Contribution,Special Donation',
            'payment_mode' => 'required|string|in:Cash,UPI,Bank Transfer,Cheque',
            'payment_date' => 'required|date',
            'event_id' => 'nullable|exists:marriage_events,id',
            'event_contribution_id' => 'nullable|exists:event_contributions,id',
        ]);

        try {
            // Agent scoping: agents can only record payments for their own members
            $user = auth()->user();
            $member = Member::findOrFail($request->member_id);
            if ($user && $user->isAgent() && $user->agent_id && $member->agent_id !== $user->agent_id) {
                return back()->withInput()->with('error', 'Unauthorized: You can only record collections for members assigned to your agency.');
            }

            $paymentData = $request->only([
                'member_id', 'payment_type', 'payment_mode', 'reference_no',
                'payment_date', 'remarks', 'amount', 'event_id', 'event_contribution_id',
            ]);
            if ($user && $user->isAgent() && $user->agent_id) {
                $paymentData['agent_id'] = $user->agent_id;
            }

            $payment = PaymentService::processPayment($paymentData);

            return redirect()->route('admin.payments.receipt', $payment->id)
                ->with('success', "Payment of ₹" . number_format($payment->amount, 2) . " recorded successfully. Receipt No: {$payment->receipt_no}");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    public function receipts(Request $request)
    {
        $user = auth()->user();
        $query = Payment::with(['member.scheme', 'agent', 'event'])->where('status', 'Verified');

        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }

        if ($request->filled('search')) {
            $search = \App\Helpers\Helper::likeEscape($request->search);
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

    public function show(Request $request, $id)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $user = auth()->user();
            $query = Payment::with(['member.scheme', 'member.agent', 'agent', 'event']);
            if ($user && $user->isAgent() && $user->agent_id) {
                $query->where('agent_id', $user->agent_id);
            }
            return response()->json($query->findOrFail($id));
        }
        return $this->receipt($id);
    }

    public function edit(Request $request, $id)
    {
        return $this->show($request, $id);
    }

    public function receipt($id)
    {
        $user = auth()->user();
        $query = Payment::with(['member.scheme', 'member.agent', 'agent', 'event']);
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $payment = $query->findOrFail($id);
        $whatsappData = WhatsAppService::getReceiptMessage($payment);

        return view('admin.payments.receipt', compact('payment', 'whatsappData'));
    }

    public function receiptPdf($id)
    {
        $user = auth()->user();
        $query = Payment::query();
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $query->findOrFail($id); // authorization check (404 if not scoped)
        $pdf = ReceiptService::generatePdf($id);
        return $pdf->download("SSWS_Receipt_{$id}.pdf");
    }

    public function ledger(Request $request)
    {
        $user = auth()->user();
        $membersQuery = Member::with(['scheme', 'agent'])->where('status', 'Active');
        if ($user && $user->isAgent() && $user->agent_id) {
            $membersQuery->where('agent_id', $user->agent_id);
        }
        $members = $membersQuery->orderBy('full_name')->get();

        $selectedMember = null;
        $ledgerEntries = collect();
        $eventContributions = collect();
        $payments = collect();
        $stats = [
            'total_expected' => 0.0,
            'total_paid' => 0.0,
            'total_pending' => 0.0,
            'ledger_debit' => 0.0,
            'ledger_credit' => 0.0,
            'running_balance' => 0.0,
            'events_total_count' => 0,
            'events_paid_count' => 0,
            'events_pending_count' => 0,
        ];

        if ($request->filled('member_id')) {
            $selectedQuery = Member::with([
                'scheme',
                'agent',
                'ageSlab',
                'nominees',
            ])->where('id', $request->member_id);

            if ($user && $user->isAgent() && $user->agent_id) {
                $selectedQuery->where('agent_id', $user->agent_id);
            }
            $selectedMember = $selectedQuery->first();

            if ($selectedMember) {
                $ledgerEntries = $selectedMember->ledgers()
                    ->with(['agent', 'payment', 'creator'])
                    ->orderBy('transaction_date')
                    ->orderBy('id')
                    ->get();

                $eventContributions = $selectedMember->eventContributions()
                    ->with(['event', 'agent', 'payment.agent'])
                    ->orderBy('event_date')
                    ->orderBy('id')
                    ->get();

                $payments = $selectedMember->payments()
                    ->with(['agent', 'event'])
                    ->latest('payment_date')
                    ->get();

                $now = \Carbon\Carbon::now();
                $startOfCurrentMonth = $now->copy()->startOfMonth();
                $endOfCurrentMonth = $now->copy()->endOfMonth();

                $thisMonthExpected = 0.0;
                $thisMonthPaid = 0.0;
                $thisMonthPending = 0.0;
                $previousDue = 0.0;

                foreach ($eventContributions as $ec) {
                    $amt = (float)$ec->contribution_amount;
                    $isPaid = ($ec->payment_status === 'Paid');
                    $eventDate = $ec->event_date ?: ($ec->event && $ec->event->event_date ? $ec->event->event_date : null);
                    $isCurrentMonth = $eventDate && $eventDate->greaterThanOrEqualTo($startOfCurrentMonth) && $eventDate->lessThanOrEqualTo($endOfCurrentMonth);

                    if ($isCurrentMonth) {
                        $thisMonthExpected += $amt;
                        if ($isPaid) { $thisMonthPaid += $amt; } else { $thisMonthPending += $amt; }
                    } else {
                        if (!$isPaid) { $previousDue += $amt; }
                    }
                }

                if ($thisMonthExpected == 0 && $eventContributions->count() == 0) {
                    $thisMonthExpected = (float)($selectedMember->monthly_support_amount ?: ($selectedMember->ageSlab ? $selectedMember->ageSlab->support_amount : 0.0));
                }
                if ($selectedMember->pending_amount > 0 && ($thisMonthPending + $previousDue) < (float)$selectedMember->pending_amount) {
                    $previousDue = max(0, (float)$selectedMember->pending_amount - $thisMonthPending);
                }
                $totalDue = $thisMonthExpected + $previousDue;

                $totalExpectedAll = (float)$selectedMember->total_paid + (float)$selectedMember->pending_amount;
                $totalPaidAll = (float)$selectedMember->total_paid;
                $totalPendingAll = (float)$selectedMember->pending_amount;

                $stats = [
                    'total_expected' => $totalExpectedAll,
                    'total_paid' => $totalPaidAll,
                    'total_pending' => $totalPendingAll,
                    'this_month_expected' => $thisMonthExpected,
                    'previous_month_due' => $previousDue,
                    'total_due' => $totalDue,
                    'ledger_debit' => (float)$ledgerEntries->sum('debit'),
                    'ledger_credit' => (float)$ledgerEntries->sum('credit'),
                    'running_balance' => $selectedMember->calculateCurrentBalance(),
                    'events_total_count' => $eventContributions->count(),
                    'events_paid_count' => $eventContributions->where('payment_status', 'Paid')->count(),
                    'events_pending_count' => $eventContributions->where('payment_status', 'Pending')->count(),
                ];
            }
        }

        return view('admin.payments.ledger', compact('members', 'selectedMember', 'ledgerEntries', 'eventContributions', 'payments', 'stats'));
    }

    public function memberLedgerPdf($id)
    {
        $user = auth()->user();
        $query = Member::query();
        if ($user && $user->isAgent() && $user->agent_id) {
            $query->where('agent_id', $user->agent_id);
        }
        $member = $query->findOrFail($id);
        $pdf = \App\Services\MemberLedgerCardService::generatePdf($member->id);
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $member->membership_no . '_' . $member->full_name);
        return $pdf->download("Ledger_Card_{$cleanName}.pdf");
    }
}
