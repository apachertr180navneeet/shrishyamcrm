@extends('admin.layouts.app')

@section('style')
<style>
    .quick-amount-btn {
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        color: #1e293b;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 14px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .quick-amount-btn:hover, .quick-amount-btn:active {
        background: #1B365D;
        color: #ffffff;
        border-color: #1B365D;
    }
    .quick-amount-btn.active-rate {
        background: #e8f5e9;
        border-color: #2e7d32;
        color: #1b5e20;
    }
    .mode-pill input[type="radio"] {
        display: none;
    }
    .mode-pill label {
        display: block;
        padding: 10px 14px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .mode-pill input[type="radio"]:checked + label {
        background: #e8f5e9;
        border-color: #2e7d32;
        color: #1b5e20;
        box-shadow: 0 2px 8px rgba(46, 125, 50, 0.15);
    }
    @media (max-width: 767.98px) {
        .mobile-form-card {
            padding: 16px !important;
            border-radius: 12px;
        }
        .form-control-lg, .form-select-lg {
            font-size: 16px !important;
            padding: 12px 14px !important;
        }
        .btn-submit-mobile {
            font-size: 16px !important;
            padding: 14px 20px !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-3 mb-md-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">
                        <i class="fas fa-cash-register text-success me-2"></i>रसीद प्रविष्टि (Receipt Entry / Cash Collection)
                    </h4>
                    <p class="text-muted small mb-0">फील्ड एजेंट नकद संग्रह एवं तुरंत रसीद प्रविष्टि (Mobile Optimized Cash Collection)</p>
                </div>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-history me-1"></i> {{ __('erp.receipt_history') }}
                </a>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Linked Event Contribution Banner if opened from Collect Cash -->
    @if(isset($selectedContribution) && $selectedContribution)
        <div class="alert alert-success d-flex align-items-center mb-4 py-3 px-4 shadow-sm border-0 rounded-3">
            <i class="fas fa-hand-holding-heart fs-2 me-3 text-success"></i>
            <div class="flex-grow-1">
                <div class="fw-bold fs-6 text-dark">
                    कार्यक्रम अंशदान संग्रह (Event Contribution Cash Collection)
                </div>
                <div class="small text-muted mt-1">
                    कार्यक्रम: <strong class="text-primary">{{ $selectedContribution->event ? $selectedContribution->event->title : $selectedContribution->event_name }}</strong> |
                    सदस्य: <strong>{{ $selectedContribution->member_name }}</strong> |
                    आयु स्लैब: <span class="badge bg-label-primary">{{ $selectedContribution->age_slab }}</span> |
                    सदस्य अंशदान राशि: <strong class="text-success fs-6">₹{{ number_format($selectedContribution->contribution_amount, 2) }}</strong>
                </div>
            </div>
        </div>
    @endif

    <!-- Form Container -->
    <div class="row justify-content-center">
        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-md-5 mobile-form-card">
                    <form action="{{ route('admin.payments.store') }}" method="POST" id="receiptEntryForm">
                        @csrf

                        @if(isset($selectedContribution) && $selectedContribution)
                            <input type="hidden" name="event_contribution_id" value="{{ $selectedContribution->id }}">
                            <input type="hidden" name="event_id" value="{{ $selectedContribution->event_id }}">
                        @endif

                        <!-- Member Selection -->
                        <div class="mb-3 mb-md-4">
                            <label class="form-label fw-bold fs-6">
                                <i class="fas fa-user-check text-primary me-1"></i> Select Member (सदस्य चुनें) <span class="text-danger">*</span>
                            </label>
                            <select name="member_id" id="memberSelect" class="form-select form-select-lg shadow-sm" required onchange="updateMemberInfo()">
                                <option value="">-- Choose Registered Member --</option>
                                @foreach($members as $m)
                                @php
                                    $rate = (float)($m->monthly_support_amount ?: ($m->ageSlab ? $m->ageSlab->support_amount : 200.0));
                                @endphp
                                <option value="{{ $m->id }}"
                                    data-name="{{ $m->full_name }}"
                                    data-monthly="{{ $rate }}"
                                    data-pending="{{ (float)$m->pending_amount }}"
                                    data-agent="{{ $m->agent_id }}"
                                    data-scheme="{{ $m->scheme ? $m->scheme->name_hindi : '' }}"
                                    data-ageslab="{{ $m->ageSlab ? $m->ageSlab->slab_name : '' }}"
                                    {{ $selectedMemberId == $m->id ? 'selected' : '' }}>
                                    {{ $m->membership_no }} - {{ $m->full_name }} ({{ $m->mobile }}) | दर: ₹{{ number_format($rate, 0) }}/कार्यक्रम
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Member Quick Summary Card -->
                        <div class="card border bg-light mb-3 mb-md-4 p-3 d-none rounded-3" id="memberSummaryCard">
                            <div class="row g-2 text-center text-md-start">
                                <div class="col-md-4 col-12 mb-1">
                                    <small class="text-muted d-block" style="font-size: 11px;">योजना (Scheme)</small>
                                    <strong id="displayScheme" class="small text-truncate d-block text-dark">-</strong>
                                </div>
                                <div class="col-md-4 col-6 mb-1">
                                    <small class="text-muted d-block" style="font-size: 11px;">सदस्य दर (प्रति कार्यक्रम)</small>
                                    <strong class="text-success small" id="displayMonthly">-</strong>
                                </div>
                                <div class="col-md-4 col-6 mb-1">
                                    <small class="text-muted d-block" style="font-size: 11px;">कुल बकाया (Pending Due)</small>
                                    <strong class="text-danger small" id="displayPending">-</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Amount Field + Quick Amount Chips -->
                        <div class="mb-3 mb-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold fs-6 mb-0">
                                    <i class="fas fa-rupee-sign text-success me-1"></i> Cash Amount (संग्रह राशि ₹) <span class="text-danger">*</span>
                                </label>
                                <span class="badge bg-success small" id="suggestedAmountBadge"></span>
                            </div>
                            <div class="input-group input-group-lg mb-2">
                                <span class="input-group-text bg-success text-white fw-bold">₹</span>
                                <input type="number" name="amount" id="paymentAmount" class="form-control form-control-lg fw-bold text-success fs-4" placeholder="Amount" min="1" step="0.01" required value="{{ isset($selectedContribution) && $selectedContribution ? $selectedContribution->contribution_amount : '' }}">
                            </div>

                            <!-- Dynamic Touch Quick Chips (Adjusts to Member's exact rate) -->
                            <div class="d-flex flex-wrap gap-2 mt-2" id="quickAmountContainer">
                                <!-- Generated dynamically via JavaScript -->
                            </div>
                        </div>

                        <!-- Payment Mode: Defaults to Cash (Agent Cash Collection) -->
                        <div class="mb-3 mb-md-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-wallet text-secondary me-1"></i> Payment Collection Mode (भुगतान माध्यम) <span class="text-danger">*</span>
                            </label>
                            <div class="row g-2">
                                <div class="col-4 mode-pill">
                                    <input type="radio" name="payment_mode" id="modeCash" value="Cash" checked onchange="toggleRefField(this.value)">
                                    <label for="modeCash">
                                        <i class="fas fa-money-bill-wave d-block mb-1 fs-5 text-success"></i> Cash (नकद)
                                    </label>
                                </div>
                                <div class="col-4 mode-pill">
                                    <input type="radio" name="payment_mode" id="modeUpi" value="UPI" onchange="toggleRefField(this.value)">
                                    <label for="modeUpi">
                                        <i class="fas fa-qrcode d-block mb-1 fs-5 text-primary"></i> UPI / QR
                                    </label>
                                </div>
                                <div class="col-4 mode-pill">
                                    <input type="radio" name="payment_mode" id="modeBank" value="Bank Transfer" onchange="toggleRefField(this.value)">
                                    <label for="modeBank">
                                        <i class="fas fa-university d-block mb-1 fs-5 text-info"></i> Bank
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Reference / UTR (Conditional) -->
                        <div class="mb-3 d-none" id="referenceFieldGroup">
                            <label class="form-label fw-semibold">UPI / Transaction Ref No (UTR)</label>
                            <input type="text" name="reference_no" id="referenceNoInput" class="form-control" placeholder="e.g. UPI-2026-9812938">
                        </div>

                        <!-- Payment Type & Collection Date -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Payment Type (प्रकार) <span class="text-danger">*</span></label>
                                <select name="payment_type" id="paymentTypeSelect" class="form-select form-select-lg" required onchange="onPaymentTypeChange()">
                                    <option value="Monthly Support" {{ isset($selectedContribution) && $selectedContribution ? '' : 'selected' }}>Event Support (सहयोग राशि - प्रति कार्यक्रम)</option>
                                    <option value="Event Contribution" {{ isset($selectedContribution) && $selectedContribution ? 'selected' : '' }}>Event Contribution (विवाह सहयोग)</option>
                                    <option value="Joining Fee">Joining Fee (प्रवेश शुल्क)</option>
                                    <option value="Special Donation">Special Donation (विशेष दान)</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Collection Date (संग्रह तिथि) <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control form-select-lg" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Event Dropdown if Event Contribution is selected and not locked by selectedContribution -->
                        <div class="mb-3 {{ isset($selectedContribution) && $selectedContribution ? 'd-none' : '' }}" id="eventSelectGroup" style="{{ isset($selectedContribution) && $selectedContribution ? '' : 'display: none;' }}">
                            <label class="form-label fw-semibold">Select Applicable Event (विवाह कार्यक्रम चुनें)</label>
                            <select name="event_id" id="eventSelect" class="form-select" {{ isset($selectedContribution) && $selectedContribution ? 'disabled' : '' }}>
                                <option value="">-- Direct Pool / Choose Event --</option>
                                @foreach($events as $ev)
                                <option value="{{ $ev->id }}" {{ (isset($selectedContribution) && $selectedContribution && $selectedContribution->event_id == $ev->id) ? 'selected' : '' }}>
                                    {{ $ev->event_code }} - {{ $ev->title }} ({{ $ev->girl_name }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Agent Identification -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Collecting Agent (संग्रहकर्ता प्रतिनिधि)</label>
                            @if(auth()->check() && auth()->user()->isAgent() && auth()->user()->agent_id)
                                @php $currentAgent = $agents->first(); @endphp
                                <input type="hidden" name="agent_id" value="{{ auth()->user()->agent_id }}">
                                <input type="text" class="form-control bg-light fw-bold text-dark" value="{{ $currentAgent ? $currentAgent->name . ' (' . $currentAgent->agent_code . ')' : 'Your Agent Profile' }}" readonly>
                            @else
                                <select name="agent_id" id="agentSelect" class="form-select">
                                    <option value="">-- Direct Society HQ Collection --</option>
                                    @foreach($agents as $a)
                                    <option value="{{ $a->id }}">{{ $a->name }} ({{ $a->agent_code }})</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Remarks (टिप्पणी - वैकल्पिक)</label>
                            <input type="text" name="remarks" class="form-control" placeholder="e.g. नकद प्राप्त हुआ / Received Cash">
                        </div>

                        <!-- Big Touch-Friendly Submit Button -->
                        <button type="submit" class="btn btn-success btn-lg w-100 shadow btn-submit-mobile fw-bold">
                            <i class="fas fa-receipt me-2"></i> नकद संग्रह करें और रसीद बनाएं (Collect Cash & Generate Receipt)
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
let currentMemberPending = 0;
let currentMemberRate = 200;
const hasInitialContribution = {{ isset($selectedContribution) && $selectedContribution ? 'true' : 'false' }};
const initialContributionAmount = {{ isset($selectedContribution) && $selectedContribution ? $selectedContribution->contribution_amount : 0 }};

function updateMemberInfo() {
    const select = document.getElementById('memberSelect');
    const option = select.options[select.selectedIndex];
    const card = document.getElementById('memberSummaryCard');
    const badge = document.getElementById('suggestedAmountBadge');
    const amountInput = document.getElementById('paymentAmount');

    if (!option.value) {
        card.classList.add('d-none');
        badge.innerText = '';
        currentMemberPending = 0;
        currentMemberRate = 200;
        renderQuickChips(200, 0);
        return;
    }

    card.classList.remove('d-none');
    const scheme = option.getAttribute('data-scheme') || 'N/A';
    const ageSlab = option.getAttribute('data-ageslab') || '';
    currentMemberRate = Number(option.getAttribute('data-monthly')) || 200;
    currentMemberPending = Number(option.getAttribute('data-pending')) || 0;

    document.getElementById('displayScheme').innerText = scheme + (ageSlab ? ` (${ageSlab})` : '');
    document.getElementById('displayMonthly').innerText = '₹' + currentMemberRate.toLocaleString('en-IN') + ' / कार्यक्रम';
    document.getElementById('displayPending').innerText = '₹' + currentMemberPending.toLocaleString('en-IN');

    // Auto-fill payment amount if not initialized with an event contribution
    if (hasInitialContribution && initialContributionAmount > 0 && amountInput.value == initialContributionAmount) {
        badge.innerText = `अंशदान राशि (₹${initialContributionAmount}) स्वतः दर्ज`;
    } else {
        amountInput.value = currentMemberRate;
        badge.innerText = `सदस्य दर (₹${currentMemberRate}) स्वतः दर्ज`;
    }

    renderQuickChips(currentMemberRate, currentMemberPending);

    const agentId = option.getAttribute('data-agent');
    const agentSelect = document.getElementById('agentSelect');
    if (agentId && agentSelect) {
        agentSelect.value = agentId;
    }
}

function renderQuickChips(rate, pending) {
    const container = document.getElementById('quickAmountContainer');
    let html = '';

    // Member Base Rate Chip
    html += `<button type="button" class="quick-amount-btn active-rate fw-bold" onclick="setAmount(${rate})">
        ⚡ 1× कार्यक्रम दर: ₹${rate}
    </button>`;

    // Multipliers (2x, 3x, 5x)
    html += `<button type="button" class="quick-amount-btn" onclick="setAmount(${rate * 2})">2× ₹${rate * 2}</button>`;
    html += `<button type="button" class="quick-amount-btn" onclick="setAmount(${rate * 3})">3× ₹${rate * 3}</button>`;
    html += `<button type="button" class="quick-amount-btn" onclick="setAmount(${rate * 5})">5× ₹${rate * 5}</button>`;

    // Full Due Chip if pending > 0
    if (pending > 0) {
        html += `<button type="button" class="quick-amount-btn text-danger fw-bold border-danger" onclick="setAmount(${pending})">
            ⚡ Full Due: ₹${pending}
        </button>`;
    }

    // Common standard chips
    if (rate !== 500 && rate * 2 !== 500) {
        html += `<button type="button" class="quick-amount-btn" onclick="setAmount(500)">₹500</button>`;
    }
    if (rate !== 1000 && rate * 2 !== 1000) {
        html += `<button type="button" class="quick-amount-btn" onclick="setAmount(1000)">₹1,000</button>`;
    }

    container.innerHTML = html;
}

function setAmount(amt) {
    document.getElementById('paymentAmount').value = amt;
}

function onPaymentTypeChange() {
    const type = document.getElementById('paymentTypeSelect').value;
    const eventGroup = document.getElementById('eventSelectGroup');
    if (eventGroup && !hasInitialContribution) {
        if (type === 'Event Contribution') {
            eventGroup.style.display = 'block';
        } else {
            eventGroup.style.display = 'none';
        }
    }
}

function toggleRefField(mode) {
    const refGroup = document.getElementById('referenceFieldGroup');
    if (mode === 'UPI' || mode === 'Bank Transfer' || mode === 'Cheque') {
        refGroup.classList.remove('d-none');
    } else {
        refGroup.classList.add('d-none');
    }
}

document.addEventListener("DOMContentLoaded", function () {
    updateMemberInfo();
    onPaymentTypeChange();
});
</script>
@endsection

