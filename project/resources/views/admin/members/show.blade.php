@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Member Header Profile Banner -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-xl bg-label-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas {{ $member->gender == 'Female' ? 'fa-female' : 'fa-user' }} fs-2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h3 class="fw-bold text-heading mb-0">{{ $member->full_name }}</h3>
                            <span class="badge {{ $member->status == 'Active' ? 'bg-success' : 'bg-danger' }}">{{ $member->status }}</span>
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
                    <a href="{{ route('admin.certificates.show', $member->id) }}" class="btn btn-warning text-dark" target="_blank">
                        <i class="fas fa-certificate me-1"></i> Membership Certificate
                    </a>
                    <a href="{{ route('admin.payments.create', ['member_id' => $member->id]) }}" class="btn btn-success">
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
                    <i class="fas fa-user-check me-1"></i> Personal & KYC Details
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-nominees">
                    <i class="fas fa-users-cog me-1"></i> Nominees (वारिसदार)
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-payments">
                    <i class="fas fa-history me-1"></i> Payment History ({{ $member->payments->count() }})
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
                            <small class="text-muted d-block">Monthly Support Amount</small>
                            <h4 class="fw-bold text-success mb-0">₹{{ number_format($member->monthly_support_amount) }}/mo</h4>
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
                                <td><strong class="text-success">₹{{ number_format($p->amount) }}</strong></td>
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
        </div>
    </div>
</div>
@endsection
