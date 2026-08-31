@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">भुगतान प्रविष्टि (Record Payment Entry)</h4>
                    <p class="text-muted mb-0">Record monthly support, event contribution, partial payment, or donation with instant receipt generation.</p>
                </div>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Payment History
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row justify-content-center">
        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('admin.payments.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Select Society Member (सदस्य का चयन करें) <span class="text-danger">*</span></label>
                            <select name="member_id" id="memberSelect" class="form-select form-select-lg" required onchange="updateMemberInfo()">
                                <option value="">-- Choose Registered Member --</option>
                                @foreach($members as $m)
                                <option value="{{ $m->id }}"
                                    data-monthly="{{ $m->monthly_support_amount }}"
                                    data-pending="{{ $m->pending_amount }}"
                                    data-agent="{{ $m->agent_id }}"
                                    data-scheme="{{ $m->scheme ? $m->scheme->name_hindi : '' }}"
                                    {{ $selectedMemberId == $m->id ? 'selected' : '' }}>
                                    {{ $m->membership_no }} - {{ $m->full_name }} ({{ $m->mobile }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Member Quick Summary -->
                        <div class="card border bg-lighter mb-4 p-3 d-none" id="memberSummaryCard">
                            <div class="row g-2">
                                <div class="col-md-4 col-6">
                                    <small class="text-muted d-block">Enrolled Scheme</small>
                                    <strong id="displayScheme">-</strong>
                                </div>
                                <div class="col-md-4 col-6">
                                    <small class="text-muted d-block">Monthly Support</small>
                                    <strong class="text-success" id="displayMonthly">-</strong>
                                </div>
                                <div class="col-md-4 col-12">
                                    <small class="text-muted d-block">Overdue Pending</small>
                                    <strong class="text-danger" id="displayPending">-</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Payment Amount (राशि ₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" name="amount" id="paymentAmount" class="form-control form-control-lg fw-bold text-success" placeholder="e.g. 300" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Payment Type (भुगतान प्रकार) <span class="text-danger">*</span></label>
                                <select name="payment_type" class="form-select form-select-lg" required>
                                    <option value="Monthly Support">Monthly Support (मासिक सहयोग)</option>
                                    <option value="Event Contribution">Event Contribution (विवाह सहायता)</option>
                                    <option value="Joining Fee">Joining Fee (प्रवेश शुल्क)</option>
                                    <option value="Special Donation">Special Donation (विशेष दान)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                                <select name="payment_mode" class="form-select" required>
                                    <option value="UPI">UPI (GooglePay, PhonePe, Paytm)</option>
                                    <option value="Cash">Cash (नकद)</option>
                                    <option value="Bank Transfer">Bank Transfer (NEFT/IMPS)</option>
                                    <option value="Cheque">Cheque (चेक)</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Transaction Reference / UTR No</label>
                                <input type="text" name="reference_no" class="form-control" placeholder="e.g. UPI9812739281">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Collection Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Collecting Agent (संग्रहकर्ता एजेंट)</label>
                                @if(auth()->check() && auth()->user()->isAgent() && auth()->user()->agent_id)
                                    @php $currentAgent = $agents->first(); @endphp
                                    <input type="hidden" name="agent_id" value="{{ auth()->user()->agent_id }}">
                                    <input type="text" class="form-control bg-light fw-semibold" value="{{ $currentAgent ? $currentAgent->name . ' (' . $currentAgent->agent_code . ')' : 'Your Agent Profile' }}" readonly>
                                @else
                                    <select name="agent_id" id="agentSelect" class="form-select">
                                        <option value="">-- HQ Direct Collection --</option>
                                        @foreach($agents as $a)
                                        <option value="{{ $a->id }}">{{ $a->name }} ({{ $a->agent_code }})</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Remarks (टिप्पणी)</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Received monthly support via PhonePe..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow">
                            <i class="fas fa-check-circle me-2"></i> Save & Generate Official Receipt
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function updateMemberInfo() {
    const select = document.getElementById('memberSelect');
    const option = select.options[select.selectedIndex];
    const card = document.getElementById('memberSummaryCard');

    if (!option.value) {
        card.classList.add('d-none');
        return;
    }

    card.classList.remove('d-none');
    document.getElementById('displayScheme').innerText = option.getAttribute('data-scheme') || 'N/A';
    document.getElementById('displayMonthly').innerText = '₹' + Number(option.getAttribute('data-monthly')).toLocaleString('en-IN') + ' / mo';
    document.getElementById('displayPending').innerText = '₹' + Number(option.getAttribute('data-pending')).toLocaleString('en-IN');

    // Auto set suggested amount to monthly support or pending amount
    const monthly = option.getAttribute('data-monthly');
    const pending = option.getAttribute('data-pending');
    if (Number(pending) > 0) {
        document.getElementById('paymentAmount').value = pending;
    } else if (monthly) {
        document.getElementById('paymentAmount').value = monthly;
    }

    const agentId = option.getAttribute('data-agent');
    if (agentId) {
        document.getElementById('agentSelect').value = agentId;
    }
}

document.addEventListener("DOMContentLoaded", function () {
    updateMemberInfo();
});
</script>
@endsection
