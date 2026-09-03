@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;"><i class="fas fa-receipt text-primary me-2"></i>रसीद इतिहास (Receipt History)</h4>
                    <p class="text-muted mb-0">Total Verified Collections: <strong class="text-success fs-5">₹{{ number_format($totalCollected) }}</strong></p>
                </div>
                <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
                    <i class="fas fa-cash-register me-1"></i> Record Receipt Entry
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.payments.index') }}" method="GET" class="row g-3">
                <div class="col-lg-5 col-12">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by receipt no, member name, membership no, UTR..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <select name="payment_type" class="form-select" onchange="this.form.submit()">
                        <option value="">All Payment Types</option>
                        <option value="Monthly Support" {{ request('payment_type') == 'Monthly Support' ? 'selected' : '' }}>Monthly Support (मासिक सहयोग)</option>
                        <option value="Joining Fee" {{ request('payment_type') == 'Joining Fee' ? 'selected' : '' }}>Joining Fee (प्रवेश शुल्क)</option>
                        <option value="Event Contribution" {{ request('payment_type') == 'Event Contribution' ? 'selected' : '' }}>Event Contribution (विवाह सहयोग)</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 col-12">
                    <select name="payment_mode" class="form-select" onchange="this.form.submit()">
                        <option value="">All Modes</option>
                        <option value="UPI" {{ request('payment_mode') == 'UPI' ? 'selected' : '' }}>UPI</option>
                        <option value="Cash" {{ request('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="Bank Transfer" {{ request('payment_mode') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>
                <div class="col-lg-2 col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt No</th>
                        <th>Member Details</th>
                        <th>Amount</th>
                        <th>Payment Type</th>
                        <th>Mode / UTR</th>
                        <th>Date</th>
                        <th>Collected By</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                    <tr>
                        <td>
                            <a href="{{ route('admin.payments.receipt', $p->id) }}" class="fw-bold text-primary">
                                {{ $p->receipt_no }}
                            </a>
                            <small class="d-block text-muted">{{ $p->san_code }}</small>
                        </td>
                        <td>
                            <strong>{{ $p->member ? $p->member->full_name : 'N/A' }}</strong>
                            <small class="d-block text-muted">{{ $p->member ? $p->member->membership_no : '' }} | {{ $p->member && $p->member->scheme ? $p->member->scheme->name_hindi : '' }}</small>
                        </td>
                        <td><strong class="text-success fs-6">₹{{ number_format($p->amount) }}</strong></td>
                        <td><span class="badge bg-label-primary">{{ $p->payment_type }}</span></td>
                        <td>
                            <span class="badge bg-label-info">{{ $p->payment_mode }}</span>
                            @if($p->reference_no)
                                <small class="d-block text-muted">{{ $p->reference_no }}</small>
                            @endif
                        </td>
                        <td>{{ $p->payment_date ? $p->payment_date->format('d M Y') : '' }}</td>
                        <td><small>{{ $p->collected_by ?? 'HQ' }}</small></td>
                        <td class="text-center">
                            <a href="{{ route('admin.payments.receipt', $p->id) }}" class="btn btn-sm btn-outline-primary" title="View & Print Official Receipt">
                                <i class="fas fa-print me-1"></i> Receipt
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No payments recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
