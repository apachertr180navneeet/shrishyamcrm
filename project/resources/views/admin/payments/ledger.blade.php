@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">वित्तीय लेजर खाता (Member Financial Ledgers)</h4>
                    <p class="text-muted mb-0">Search and audit complete chronological ledger of joining payments, event contributions, pending dues, and balances for any member.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Selection Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.ledger.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-lg-9 col-md-8 col-12">
                    <label class="form-label fw-semibold">Select Member (सदस्य चुनें)</label>
                    <select name="member_id" class="form-select form-select-lg" onchange="this.form.submit()">
                        <option value="">-- Choose Member to Open Ledger (सदस्य का चयन करें) --</option>
                        @foreach($members as $m)
                        <option value="{{ $m->id }}" {{ request('member_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->membership_no }} - {{ $m->full_name }} ({{ $m->mobile }}) [{{ $m->scheme ? $m->scheme->name_hindi : 'All Schemes' }}]
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-4 col-12 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-lg w-100" style="background: #1B365D;">
                        <i class="fas fa-book-open me-1"></i> Open Ledger
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedMember)
    <!-- Selected Member Profile & Summary Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 pb-3 border-bottom mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-lg bg-label-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                        <i class="fas {{ $selectedMember->gender == 'Female' ? 'fa-female' : 'fa-user' }} fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h4 class="fw-bold mb-0 text-heading">{{ $selectedMember->full_name }}</h4>
                            <span class="badge bg-label-primary">{{ $selectedMember->membership_no }}</span>
                            <span class="badge {{ $selectedMember->status == 'Active' ? 'bg-success' : 'bg-danger' }}">{{ $selectedMember->status }}</span>
                            <span class="badge bg-info text-white">{{ $selectedMember->scheme ? $selectedMember->scheme->name_hindi : 'All Schemes' }}</span>
                        </div>
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-user-shield me-1"></i> पिता/पति: <strong>{{ $selectedMember->father_spouse_name ?: '-' }}</strong> &nbsp;|&nbsp;
                            <i class="fas fa-phone-alt me-1 text-primary"></i> {{ $selectedMember->mobile }} &nbsp;|&nbsp;
                            <i class="fas fa-user-tie me-1 text-warning"></i> कार्यकर्ता: <strong>{{ $selectedMember->agent ? $selectedMember->agent->name : 'HQ Direct' }}</strong> &nbsp;|&nbsp;
                            <i class="fas fa-user-friends me-1 text-success"></i> वारिसदार: <strong>{{ $selectedMember->nominees->first() ? $selectedMember->nominees->first()->name : '-' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.members.ledger.pdf', $selectedMember->id) }}" class="btn btn-danger shadow-sm" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> लेजर रसीद कार्ड (Download PDF)
                    </a>
                    <a href="{{ route('admin.payments.create', ['member_id' => $selectedMember->id]) }}" class="btn btn-success">
                        <i class="fas fa-plus-circle me-1"></i> Record Payment
                    </a>
                    <a href="{{ route('admin.members.show', $selectedMember->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-user me-1"></i> Profile
                    </a>
                </div>
            </div>

            <!-- KPI Metric Summary Tiles -->
            <div class="row g-3">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="p-3 bg-light rounded border text-center">
                        <small class="text-muted d-block fw-semibold mb-1">कुल अपेक्षित सहयोग (Total Expected)</small>
                        <h4 class="text-primary fw-bold mb-0">₹{{ number_format($stats['total_expected'], 2) }}</h4>
                        <small class="text-muted">{{ $stats['events_total_count'] }} पंजीकृत कार्यक्रम</small>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="p-3 bg-light rounded border text-center">
                        <small class="text-muted d-block fw-semibold mb-1">कुल जमा राशि (Total Collected/Paid)</small>
                        <h4 class="text-success fw-bold mb-0">₹{{ number_format($stats['total_paid'], 2) }}</h4>
                        <small class="text-success fw-semibold">{{ $stats['events_paid_count'] }} कार्यक्रम पूर्ण</small>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="p-3 bg-light rounded border text-center">
                        <small class="text-muted d-block fw-semibold mb-1">शेष बकाया राशि (Pending Dues)</small>
                        <h4 class="{{ $stats['total_pending'] > 0 ? 'text-danger' : 'text-success' }} fw-bold mb-0">
                            ₹{{ number_format($stats['total_pending'], 2) }}
                        </h4>
                        <small class="{{ $stats['total_pending'] > 0 ? 'text-danger' : 'text-muted' }} fw-semibold">
                            {{ $stats['events_pending_count'] }} कार्यक्रम शेष
                        </small>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="p-3 bg-light rounded border text-center">
                        <small class="text-muted d-block fw-semibold mb-1">लेजर शेष (Running Balance)</small>
                        <h4 class="{{ $stats['running_balance'] > 0 ? 'text-danger' : 'text-success' }} fw-bold mb-0">
                            ₹{{ number_format($stats['running_balance'], 2) }}
                        </h4>
                        <small class="text-muted">Dr: ₹{{ number_format($stats['ledger_debit']) }} | Cr: ₹{{ number_format($stats['ledger_credit']) }}</small>
                    </div>
                </div>
            </div>

            <!-- Monthly Dues Breakdown Formula Widget -->
            <div class="mt-3 p-3 bg-white rounded border d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="fw-bold mb-1 text-heading"><i class="fas fa-calculator text-primary me-1"></i> चालू माह एवं बकाया गणना (Monthly Dues Calculation: {{ date('M Y') }})</h6>
                    <small class="text-muted">मासिक सहयोग/कार्यक्रम + पूर्व माह बकाया = कुल देय राशि</small>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="px-3 py-2 bg-light rounded text-center border">
                        <small class="text-muted d-block fw-semibold">इस माह (This m.)</small>
                        <strong class="text-primary fs-5">₹{{ number_format($stats['this_month_expected'] ?? 0) }}</strong>
                    </div>
                    <span class="fs-4 fw-bold text-muted">+</span>
                    <div class="px-3 py-2 bg-light rounded text-center border">
                        <small class="text-muted d-block fw-semibold">पिछला बकाया (Due)</small>
                        <strong class="text-danger fs-5">₹{{ number_format($stats['previous_month_due'] ?? 0) }}</strong>
                    </div>
                    <span class="fs-4 fw-bold text-muted">=</span>
                    <div class="px-4 py-2 bg-primary text-white rounded text-center shadow-sm">
                        <small class="text-white-50 d-block fw-semibold">कुल देय (Total)</small>
                        <strong class="fs-5 text-white">₹{{ number_format($stats['total_due'] ?? 0) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs for Detailed Records -->
    <div class="nav-align-top mb-4">
        <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active fw-semibold" role="tab" data-bs-toggle="tab" data-bs-target="#tab-events">
                    <i class="fas fa-list-alt me-1"></i> 1. कार्यक्रम सहयोग एवं स्थिति (Event Contributions)
                    <span class="badge rounded-pill bg-primary ms-1">{{ $eventContributions->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link fw-semibold" role="tab" data-bs-toggle="tab" data-bs-target="#tab-ledgers">
                    <i class="fas fa-book me-1"></i> 2. वित्तीय लेजर विवरणी (Debit / Credit Ledger)
                    <span class="badge rounded-pill bg-secondary ms-1">{{ $ledgerEntries->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link fw-semibold" role="tab" data-bs-toggle="tab" data-bs-target="#tab-receipts">
                    <i class="fas fa-receipt me-1"></i> 3. भुगतान रसीदें (Payment Receipts)
                    <span class="badge rounded-pill bg-success ms-1">{{ $payments->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content border-0 p-0 shadow-none">
            <!-- TAB 1: Event Contributions -->
            <div class="tab-pane fade show active" id="tab-events" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-calendar-check text-primary me-2"></i>सदस्य अंशदान सूची (Event-Wise Member Contributions)
                        </h5>
                        <a href="{{ route('admin.members.ledger.pdf', $selectedMember->id) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                            <i class="fas fa-print me-1"></i> Print / Download Physical Card Format
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>नाम / विवरण (Event & Beneficiary Details)</th>
                                    <th>जुड़ने की तिथि (Event Date)</th>
                                    <th class="text-end">अंशदान राशि (Amount)</th>
                                    <th class="text-center">भुगतान स्थिति (Status)</th>
                                    <th>भुगतान तिथि (Payment Date)</th>
                                    <th>रसीद नं (Receipt No)</th>
                                    <th>कार्यकर्ता (Agent)</th>
                                    <th class="text-center">कार्य (Action)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($eventContributions as $idx => $ec)
                                <tr>
                                    <td><strong>{{ $idx + 1 }}</strong></td>
                                    <td>
                                        @if($ec->event)
                                            <strong class="text-primary">{{ $ec->event->girl_name }}</strong>
                                            @if($ec->event->father_name)
                                                <small class="text-muted d-block">पिता/पति: {{ $ec->event->father_name }}</small>
                                            @endif
                                            @if($ec->event->venue)
                                                <small class="text-muted d-block"><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $ec->event->venue }}</small>
                                            @endif
                                        @else
                                            <strong class="text-dark">{{ $ec->event_name ?: 'कल्याण सहायता कार्यक्रम' }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $ec->event_date ? $ec->event_date->format('d M Y') : '-' }}
                                    </td>
                                    <td class="text-end">
                                        <strong class="fs-6 text-dark">₹{{ number_format($ec->contribution_amount, 2) }}</strong>
                                        @if($ec->age_slab)
                                            <small class="text-muted d-block">{{ $ec->age_slab }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($ec->payment_status === 'Paid')
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>PAID (जमा)</span>
                                        @else
                                            <span class="badge bg-danger"><i class="fas fa-clock me-1"></i>PENDING (बकाया)</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $ec->payment_date ? $ec->payment_date->format('d M Y') : ($ec->payment ? $ec->payment->payment_date->format('d M Y') : '-') }}
                                    </td>
                                    <td>
                                        @if($ec->receipt_no)
                                            <strong class="text-primary font-monospace">{{ $ec->receipt_no }}</strong>
                                        @elseif($ec->payment)
                                            <strong class="text-primary font-monospace">{{ $ec->payment->receipt_no }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $ec->agent ? $ec->agent->name : ($ec->payment && $ec->payment->agent ? $ec->payment->agent->name : ($selectedMember->agent ? $selectedMember->agent->name : '-')) }}
                                    </td>
                                    <td class="text-center">
                                        @if($ec->payment_status === 'Paid' && ($ec->payment_id || $ec->payment))
                                            <a href="{{ route('admin.payments.receipt', $ec->payment_id ?: $ec->payment->id) }}" class="btn btn-sm btn-outline-primary" target="_blank" title="View Receipt">
                                                <i class="fas fa-receipt me-1"></i> Receipt
                                            </a>
                                        @elseif($ec->payment_status !== 'Paid')
                                            <a href="{{ route('admin.payments.create', ['member_id' => $selectedMember->id, 'contribution_id' => $ec->id]) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-hand-holding-usd me-1"></i> Collect
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-times fs-2 d-block mb-2 text-warning"></i>
                                        इस सदस्य के लिए कोई कार्यक्रम अंशदान पंजीकृत नहीं है।
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Financial Ledger Transactions -->
            <div class="tab-pane fade" id="tab-ledgers" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light py-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-book text-primary me-2"></i>वित्तीय लेजर खाता विवरणी (Financial Ledger Statement)
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>दिनांक (Date)</th>
                                    <th>प्रकार (Entry Type)</th>
                                    <th>विवरण (Description)</th>
                                    <th>संदर्भ (Ref No)</th>
                                    <th class="text-end text-danger">डेबिट / Dr (₹)</th>
                                    <th class="text-end text-success">क्रेडिट / Cr (₹)</th>
                                    <th class="text-end">बैलेंस / Balance (₹)</th>
                                    <th>कार्यकर्ता (Agent)</th>
                                    <th class="text-center">रसीद</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ledgerEntries as $entry)
                                <tr>
                                    <td>{{ $entry->transaction_date ? $entry->transaction_date->format('d M Y') : '' }}</td>
                                    <td>
                                        <span class="badge {{ $entry->entry_type == 'Payment' ? 'bg-label-success' : 'bg-label-primary' }}">
                                            {{ $entry->entry_type }}
                                        </span>
                                    </td>
                                    <td>{{ $entry->description }}</td>
                                    <td><small class="font-monospace">{{ $entry->reference_no ?? '-' }}</small></td>
                                    <td class="text-end">
                                        @if($entry->debit > 0)
                                            <strong class="text-danger">₹{{ number_format($entry->debit, 2) }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($entry->credit > 0)
                                            <strong class="text-success">₹{{ number_format($entry->credit, 2) }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong class="{{ $entry->running_balance > 0 ? 'text-danger' : 'text-success' }}">
                                            ₹{{ number_format($entry->running_balance, 2) }}
                                        </strong>
                                    </td>
                                    <td>{{ $entry->agent ? $entry->agent->name : ($selectedMember->agent ? $selectedMember->agent->name : '-') }}</td>
                                    <td class="text-center">
                                        @if($entry->payment_id)
                                            <a href="{{ route('admin.payments.receipt', $entry->payment_id) }}" class="btn btn-xs btn-outline-primary" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fas fa-book-open fs-2 d-block mb-2 text-primary"></i>
                                        इस सदस्य के लिए कोई लेजर प्रविष्टि नहीं पाई गई।
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Payment Receipts -->
            <div class="tab-pane fade" id="tab-receipts" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light py-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-receipt text-success me-2"></i>भुगतान रसीदें (Payment Receipts History)
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>रसीद संख्या (Receipt No)</th>
                                    <th>दिनांक (Date)</th>
                                    <th>प्रकार (Payment Type)</th>
                                    <th>माध्यम (Mode)</th>
                                    <th>संदर्भ नं (Ref No)</th>
                                    <th class="text-end">जमा राशि (Amount ₹)</th>
                                    <th>कार्यकर्ता (Agent)</th>
                                    <th class="text-center">रसीद डाउनलोड</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $pm)
                                <tr>
                                    <td><strong class="text-primary font-monospace">{{ $pm->receipt_no }}</strong></td>
                                    <td>{{ $pm->payment_date ? $pm->payment_date->format('d M Y') : '' }}</td>
                                    <td><span class="badge bg-label-info">{{ $pm->payment_type }}</span></td>
                                    <td><span class="badge bg-light text-dark">{{ $pm->payment_mode }}</span></td>
                                    <td><small class="font-monospace">{{ $pm->reference_no ?: '-' }}</small></td>
                                    <td class="text-end"><strong class="text-success fs-6">₹{{ number_format($pm->amount, 2) }}</strong></td>
                                    <td>{{ $pm->agent ? $pm->agent->name : ($pm->collected_by ?: 'HQ Direct') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.payments.receipt', $pm->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                        <a href="{{ route('admin.payments.receipt.pdf', $pm->id) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-receipt fs-2 d-block mb-2 text-warning"></i>
                                        इस सदस्य द्वारा कोई भुगतान रसीद दर्ज नहीं है।
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm p-5 text-center text-muted">
        <i class="fas fa-book-open fs-1 text-primary mb-3"></i>
        <h5 class="fw-semibold">Please select a member above to inspect their official society financial ledger.</h5>
    </div>
    @endif
</div>
@endsection
