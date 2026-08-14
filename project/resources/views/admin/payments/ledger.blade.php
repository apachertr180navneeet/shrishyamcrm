@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">वित्तीय लेजर खाता (Member Financial Ledgers)</h4>
                    <p class="text-muted mb-0">Search and audit complete chronological ledger of joining payments, recurring monthly support, and balances for any member.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Selection Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.ledger.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-lg-9 col-md-8 col-12">
                    <select name="member_id" class="form-select form-select-lg" onchange="this.form.submit()">
                        <option value="">-- Select Member to View Ledger --</option>
                        @foreach($members as $m)
                        <option value="{{ $m->id }}" {{ request('member_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->membership_no }} - {{ $m->full_name }} ({{ $m->mobile }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-4 col-12">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-book-open me-1"></i> Open Ledger
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedMember)
    <!-- Selected Member Ledger Statement -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-heading">{{ $selectedMember->full_name }} ({{ $selectedMember->membership_no }})</h5>
                <small class="text-muted">{{ $selectedMember->scheme ? $selectedMember->scheme->name_hindi : 'N/A' }} | Agent: {{ $selectedMember->agent ? $selectedMember->agent->name : 'HQ Direct' }}</small>
            </div>
            <div class="d-flex gap-3">
                <div>
                    <small class="text-muted d-block">Total Paid</small>
                    <strong class="text-success fs-5">₹{{ number_format($selectedMember->total_paid) }}</strong>
                </div>
                <div>
                    <small class="text-muted d-block">Pending Dues</small>
                    <strong class="{{ $selectedMember->pending_amount > 0 ? 'text-danger' : 'text-success' }} fs-5">₹{{ number_format($selectedMember->pending_amount) }}</strong>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Receipt No</th>
                        <th>Description / Type</th>
                        <th>Payment Mode</th>
                        <th>Reference No</th>
                        <th class="text-end">Credit Amount (₹)</th>
                        <th class="text-center">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgerEntries as $entry)
                    <tr>
                        <td>{{ $entry->payment_date ? $entry->payment_date->format('d M Y') : '' }}</td>
                        <td><strong class="text-primary">{{ $entry->receipt_no }}</strong></td>
                        <td>
                            <span class="badge bg-label-primary me-1">{{ $entry->payment_type }}</span>
                            <small class="text-muted">{{ $entry->month_year }}</small>
                        </td>
                        <td><span class="badge bg-label-info">{{ $entry->payment_mode }}</span></td>
                        <td><small>{{ $entry->reference_no ?? '-' }}</small></td>
                        <td class="text-end"><strong class="text-success">₹{{ number_format($entry->amount) }}</strong></td>
                        <td class="text-center">
                            <a href="{{ route('admin.payments.receipt', $entry->id) }}" class="btn btn-xs btn-outline-primary" target="_blank">
                                <i class="fas fa-print"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No transactions found for this member.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
