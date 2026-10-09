@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Member Header Profile Banner -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-xl bg-label-primary rounded-circle d-flex align-items-center justify-content-center overflow-hidden" style="width: 70px; height: 70px;">
                        @if(!empty($member->photo_src))
                            <img src="{{ $member->photo_src }}" alt="{{ $member->full_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <i class="fas {{ $member->gender == 'Female' ? 'fa-female' : 'fa-user' }} fs-2"></i>
                        @endif
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h3 class="fw-bold text-heading mb-0">{{ $member->full_name }}</h3>
                            @if($member->status === 'Active')
                                <span class="badge bg-success">Active (सक्रिय)</span>
                            @elseif($member->marriageEvents()->exists() || $member->payouts()->exists())
                                <span class="badge bg-secondary"><i class="fas fa-heart me-1"></i> Closed (विवाह संपन्न / सदस्यता समाप्त)</span>
                            @else
                                <span class="badge bg-danger">{{ $member->status }} (निष्क्रिय)</span>
                            @endif
                            <span class="badge bg-label-primary">{{ $member->membership_no }}</span>
                        </div>
                        <p class="text-muted mb-0">
                            <i class="fas fa-phone-alt me-1 text-primary"></i> {{ $member->mobile }} &nbsp;|&nbsp;
                            <i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ $member->district }}, {{ $member->state }} &nbsp;|&nbsp;
                            <i class="fas fa-hand-holding-heart me-1 text-warning"></i> {{ $member->scheme ? $member->scheme->name_hindi : 'N/A' }}
                        </p>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.members.edit', $member->id) }}" class="btn btn-primary">
                        <i class="fas fa-user-edit me-1"></i> Edit Profile
                    </a>
                    <a href="{{ route('admin.members.ledger.pdf', ['id' => $member->id, 'action' => 'download']) }}" class="btn btn-danger shadow-sm">
                        <i class="fas fa-file-download me-1"></i> स्टेटमेंट डाउनलोड (Download File)
                    </a>
                    <a href="{{ route('admin.members.ledger.pdf', ['id' => $member->id, 'action' => 'stream']) }}" class="btn btn-dark shadow-sm" target="_blank">
                        <i class="fas fa-print me-1"></i> स्टेटमेंट प्रिंट (Print Statement)
                    </a>
                    <a href="{{ route('admin.ledger.index', ['member_id' => $member->id]) }}" class="btn btn-outline-dark">
                        <i class="fas fa-file-invoice-dollar me-1"></i> लेजर कार्ड
                    </a>
                    @if($member->status === 'Active' && !($whatsappData['disabled'] ?? false))
                    <a href="{{ $whatsappData['url'] ?? '#' }}" target="_blank" class="btn btn-success">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp Due Alert
                    </a>
                    @else
                    <button type="button" class="btn btn-outline-secondary disabled" title="Inactive सदस्य को WhatsApp संदेश नहीं भेजा जा सकता" disabled>
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp (Inactive)
                    </button>
                    @endif
                    <a href="{{ route('admin.certificates.show', $member->id) }}" class="btn btn-warning text-dark" target="_blank">
                        <i class="fas fa-certificate me-1"></i> View Certificate
                    </a>
                    <a href="{{ route('admin.payments.create', ['member_id' => $member->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-rupee-sign me-1"></i> Record Payment
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Navigation Tabs -->
    <div class="nav-align-top mb-4">
        <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-overview">
                    <i class="fas fa-id-card me-1"></i> Overview
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-personal">
                    <i class="fas fa-user-check me-1"></i> Personal & KYC
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-nominees">
                    <i class="fas fa-users-cog me-1"></i> Nominees (वारिसदार)
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-payments">
                    <i class="fas fa-history me-1"></i> Payments ({{ $member->payments->count() }})
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-ledger">
                    <i class="fas fa-book-open me-1"></i> लेजर स्टेटमेंट / Ledger Statement ({{ $member->ledgers->count() }})
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-documents">
                    <i class="fas fa-file-upload me-1"></i> Documents ({{ $member->documents->count() }})
                </button>
            </li>
        </ul>
        <div class="tab-content border-0 p-4 shadow-sm bg-white rounded-bottom">
            <!-- Tab 1: Overview -->
            <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-4 col-12">
                        <div class="card bg-lighter border p-3">
                            <small class="text-muted d-block">Enrolled Scheme</small>
                            <h5 class="fw-bold text-primary mb-1">{{ $member->scheme ? $member->scheme->name_hindi : 'N/A' }}</h5>
                            <span class="badge bg-label-info">{{ $member->ageSlab ? ($member->ageSlab->min_age . '-' . $member->ageSlab->max_age . ' Yrs Slab') : '' }}</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="card bg-lighter border p-3">
                            <small class="text-muted d-block">अंशदान दर (प्रति कार्यक्रम) / Support Per Event</small>
                            <h4 class="fw-bold text-success mb-0">₹{{ number_format($member->monthly_support_amount) }}/कार्यक्रम</h4>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="card bg-lighter border p-3">
                            <small class="text-muted d-block">Assigned Agent</small>
                            <h5 class="fw-bold text-heading mb-0">{{ $member->agent ? $member->agent->name : 'HQ Direct' }}</h5>
                            <small class="text-muted">{{ $member->agent ? ($member->agent->agent_code . ' - ' . $member->agent->mobile) : '' }}</small>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12">
                        <div class="card border p-3">
                            <h6 class="fw-bold mb-2">Financial Status</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Total Amount Paid:</span>
                                    <strong class="text-success">₹{{ number_format($member->total_paid) }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Pending / Overdue Amount:</span>
                                    <strong class="{{ $member->pending_amount > 0 ? 'text-danger' : 'text-success' }}">₹{{ number_format($member->pending_amount) }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Enrolment Date:</span>
                                    <strong>{{ $member->joining_date ? $member->joining_date->format('d M Y') : 'N/A' }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12">
                        <div class="card border p-3">
                            <h6 class="fw-bold mb-2">Registered Address</h6>
                            <p class="text-muted mb-2">{{ $member->address }}</p>
                            <p class="text-muted mb-0"><strong>District:</strong> {{ $member->district }} | <strong>State:</strong> {{ $member->state }} - {{ $member->pincode }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Personal Details -->
            <div class="tab-pane fade" id="tab-personal" role="tabpanel">
                <div class="row g-3">
                    <div class="col-md-4 col-12">
                        <small class="text-muted d-block">Father / Spouse Name</small>
                        <strong class="fs-6">{{ $member->father_spouse_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 col-12">
                        <small class="text-muted d-block">Mother Name</small>
                        <strong class="fs-6">{{ $member->mother_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 col-12">
                        <small class="text-muted d-block">Gender</small>
                        <strong class="fs-6">{{ $member->gender }}</strong>
                    </div>
                    <div class="col-md-4 col-12">
                        <small class="text-muted d-block">Date of Birth</small>
                        <strong class="fs-6">{{ $member->dob ? $member->dob->format('d M Y') : 'N/A' }} ({{ $member->age }} Yrs)</strong>
                    </div>
                    <div class="col-md-4 col-12">
                        <small class="text-muted d-block">Gotra</small>
                        <strong class="fs-6">{{ $member->gotra ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 col-12">
                        <small class="text-muted d-block">Caste</small>
                        <strong class="fs-6">{{ $member->caste ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 col-12">
                        <small class="text-muted d-block">Aadhaar Card Number</small>
                        <strong class="fs-6 text-primary">{{ $member->aadhaar_no ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 col-12">
                        <small class="text-muted d-block">Initial Joining Fee</small>
                        <strong class="fs-6 text-success">₹{{ number_format($member->joining_amount) }}</strong>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Nominees -->
            <div class="tab-pane fade" id="tab-nominees" role="tabpanel">
                <div class="row g-4">
                    @forelse($member->nominees as $nominee)
                    <div class="col-md-6 col-12">
                        <div class="card border {{ $nominee->priority == 1 ? 'border-primary' : '' }} p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge {{ $nominee->priority == 1 ? 'bg-primary' : 'bg-secondary' }}">
                                    {{ $nominee->priority == 1 ? 'Primary Nominee 1 (वारिसदार)' : 'Secondary Nominee 2' }}
                                </span>
                            </div>
                            <h5 class="fw-bold mb-1">{{ $nominee->name }}</h5>
                            <p class="text-muted mb-1"><strong>Relation:</strong> {{ $nominee->relation }}</p>
                            <p class="text-muted mb-1"><strong>Mobile:</strong> {{ $nominee->mobile ?? 'N/A' }}</p>
                            <p class="text-muted mb-0"><strong>Aadhaar:</strong> {{ $nominee->aadhaar_no ?? 'N/A' }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-muted text-center py-4">
                        No nominees registered yet.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Tab 4: Payments -->
            <div class="tab-pane fade" id="tab-payments" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt No</th>
                                <th>Amount</th>
                                <th>Payment Type</th>
                                <th>Mode</th>
                                <th>Reference / UTR</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-center">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($member->payments as $p)
                            <tr>
                                <td><strong class="text-primary">{{ $p->receipt_no }}</strong></td>
                                <td><strong class="text-success">₹{{ number_format($p->amount, 2) }}</strong></td>
                                <td><span class="badge bg-label-primary">{{ $p->payment_type }}</span></td>
                                <td><span class="badge bg-label-info">{{ $p->payment_mode }}</span></td>
                                <td><small class="text-muted">{{ $p->reference_no }}</small></td>
                                <td>{{ $p->payment_date ? $p->payment_date->format('d M Y') : '' }}</td>
                                <td><span class="badge bg-success">{{ $p->status }}</span></td>
                                <td class="text-center">
                                    <a href="{{ route('admin.payments.receipt', $p->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="fas fa-receipt me-1"></i> View Receipt
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No payment records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 5: Financial Ledger Statement -->
            <div class="tab-pane fade" id="tab-ledger" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-3 border-bottom">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark" style="font-family: 'Hind', sans-serif;">
                            <i class="fas fa-file-invoice-dollar text-primary me-2"></i>सदस्य लेजर खाता स्टेटमेंट (Member Ledger Statement)
                        </h5>
                        <p class="text-muted small mb-0">
                            सदस्यता क्र.: <span class="badge bg-label-primary font-monospace">{{ $member->membership_no }}</span> &nbsp;|&nbsp;
                            नाम: <strong>{{ $member->full_name }}</strong> &nbsp;|&nbsp;
                            योजना: <strong>{{ $member->scheme ? $member->scheme->name_hindi : 'Welfare' }}</strong>
                        </p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.members.ledger.pdf', ['id' => $member->id, 'action' => 'download']) }}" class="btn btn-sm btn-danger shadow-xs">
                            <i class="fas fa-download me-1"></i> स्टेटमेंट डाउनलोड (Download File)
                        </a>
                        <a href="{{ route('admin.members.ledger.pdf', ['id' => $member->id, 'action' => 'stream']) }}" target="_blank" class="btn btn-sm btn-dark shadow-xs">
                            <i class="fas fa-print me-1"></i> प्रिंट निकालें (Print Statement)
                        </a>
                        <a href="{{ route('admin.ledger.index', ['member_id' => $member->id]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-expand me-1"></i> संपूर्ण लेजर कार्ड
                        </a>
                        <a href="{{ route('admin.payments.create', ['member_id' => $member->id]) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-cash-register me-1"></i> राशि जमा करें
                        </a>
                    </div>
                </div>

                <!-- Ledger KPI Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4 col-12">
                        <div class="card border-0 bg-lighter p-3 rounded">
                            <small class="text-muted d-block fw-semibold">कुल नामे / देय (Total Debit / Due)</small>
                            <h4 class="fw-bold text-danger mb-0">₹{{ number_format($member->ledgers->sum('debit'), 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="card border-0 bg-lighter p-3 rounded">
                            <small class="text-muted d-block fw-semibold">कुल जमा (Total Credit / Paid)</small>
                            <h4 class="fw-bold text-success mb-0">₹{{ number_format($member->ledgers->sum('credit'), 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="card border-0 p-3 rounded {{ $member->pending_amount > 0 ? 'bg-label-danger' : 'bg-label-success' }}">
                            <small class="d-block fw-semibold">वर्तमान शुद्ध बकाया (Net Outstanding Due)</small>
                            <h4 class="fw-bold mb-0 {{ $member->pending_amount > 0 ? 'text-danger' : 'text-success' }}">
                                ₹{{ number_format($member->pending_amount, 2) }}
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr style="font-size: 0.85rem;">
                                <th style="width: 45px;">#</th>
                                <th>दिनांक (Date)</th>
                                <th>वाउचर / संदर्भ क्र.</th>
                                <th>प्रविष्टि प्रकार</th>
                                <th>विवरण (Description / Particulars)</th>
                                <th class="text-end">नामे / देय (Debit ₹)</th>
                                <th class="text-end">जमा (Credit ₹)</th>
                                <th class="text-end">शेष बकाया (Balance ₹)</th>
                                <th class="text-center">रसीद / पावती</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($member->ledgers as $idx => $ledger)
                            <tr>
                                <td class="text-muted fw-semibold">{{ $idx + 1 }}</td>
                                <td>
                                    <strong>{{ $ledger->transaction_date ? $ledger->transaction_date->format('d/m/Y') : '-' }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary font-monospace">{{ $ledger->transaction_no }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary">{{ $ledger->entry_type }}</span>
                                </td>
                                <td>
                                    <span class="text-dark">{{ $ledger->description }}</span>
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    {{ $ledger->debit > 0 ? '₹' . number_format($ledger->debit, 2) : '-' }}
                                </td>
                                <td class="text-end fw-bold text-success">
                                    {{ $ledger->credit > 0 ? '₹' . number_format($ledger->credit, 2) : '-' }}
                                </td>
                                <td class="text-end fw-bold {{ $ledger->running_balance > 0 ? 'text-danger' : 'text-success' }}">
                                    ₹{{ number_format($ledger->running_balance, 2) }}
                                </td>
                                <td class="text-center">
                                    @if($ledger->payment_id)
                                        <a href="{{ route('admin.payments.receipt', $ledger->payment_id) }}" class="btn btn-xs btn-outline-primary py-1 px-2" target="_blank" title="View Official Receipt">
                                            <i class="fas fa-receipt me-1"></i> रसीद
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-file-invoice fs-1 text-secondary d-block mb-2"></i>
                                    कोई लेजर प्रविष्टि उपलब्ध नहीं है (No ledger transactions posted yet).
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($member->ledgers->count() > 0)
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="5" class="text-end">कुल योग (Grand Totals):</td>
                                <td class="text-end text-danger fs-6">₹{{ number_format($member->ledgers->sum('debit'), 2) }}</td>
                                <td class="text-end text-success fs-6">₹{{ number_format($member->ledgers->sum('credit'), 2) }}</td>
                                <td class="text-end {{ $member->pending_amount > 0 ? 'text-danger' : 'text-success' }} fs-6">
                                    ₹{{ number_format($member->pending_amount, 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Tab 6: Documents -->
            <div class="tab-pane fade" id="tab-documents" role="tabpanel">
                <div class="row g-3">
                    @forelse($member->documents as $doc)
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="card border p-3 text-center">
                            <i class="fas fa-file-pdf fs-1 text-danger mb-2"></i>
                            <h6 class="fw-bold mb-1">{{ $doc->title }}</h6>
                            <small class="text-muted d-block mb-2">{{ $doc->document_type }} ({{ round($doc->file_size / 1024) }} KB)</small>
                            <a href="{{ asset($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> View Document
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted py-4">
                        No additional KYC documents attached.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
