@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary px-3 py-1">{{ $event->event_code }}</span>
                        <span class="badge bg-label-info">{{ $event->scheme ? $event->scheme->name_hindi : 'Welfare Scheme' }}</span>
                        <span class="badge bg-{{ $event->status === 'Completed' ? 'success' : 'warning' }}">{{ $event->status }}</span>
                    </div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">
                        <i class="fas fa-heart text-danger me-2"></i>{{ $event->title }}
                    </h4>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-calendar-alt me-1 text-primary"></i> Event Date: <strong>{{ $event->event_date ? $event->event_date->format('d M Y') : 'N/A' }}</strong> &nbsp;|&nbsp;
                        <i class="fas fa-map-marker-alt me-1 text-danger"></i> Venue: <strong>{{ $event->venue }}</strong> &nbsp;|&nbsp;
                        <i class="fas fa-female me-1 text-warning"></i> कन्या: <strong>{{ $event->girl_name }}</strong> (पिता: {{ $event->father_name ?? 'N/A' }})
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Events
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted d-block fw-semibold">कुल योजना सदस्य (Eligible)</small>
                            <h4 class="fw-bold mb-0 text-primary">{{ $stats['total_members'] }}</h4>
                        </div>
                        <div class="avatar avatar-md bg-label-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                            <i class="fas fa-users fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted d-block fw-semibold">अपेक्षित अंशदान (Expected Total)</small>
                            <h4 class="fw-bold mb-0 text-dark">₹{{ number_format($stats['total_expected'], 2) }}</h4>
                        </div>
                        <div class="avatar avatar-md bg-label-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                            <i class="fas fa-calculator fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted d-block fw-semibold">प्राप्त राशि (Collected)</small>
                            <h4 class="fw-bold mb-0 text-success">₹{{ number_format($stats['total_collected'], 2) }}</h4>
                            <small class="text-success">{{ $stats['paid_count'] }} सदस्य जमा</small>
                        </div>
                        <div class="avatar avatar-md bg-label-success rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                            <i class="fas fa-check-double fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted d-block fw-semibold">शेष बकाया (Pending Due)</small>
                            <h4 class="fw-bold mb-0 text-danger">₹{{ number_format($stats['total_pending'], 2) }}</h4>
                            <small class="text-danger">{{ $stats['pending_count'] }} सदस्य शेष</small>
                        </div>
                        <div class="avatar avatar-md bg-label-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                            <i class="fas fa-clock fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.events.contributions', $event->id) }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6 col-12">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by member name, membership no, mobile, receipt no..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 col-8">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status (सभी स्थिति)</option>
                        <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending (बकाया)</option>
                        <option value="Paid" {{ request('status') === 'Paid' ? 'selected' : '' }}>Paid (जमा / रसीद जारी)</option>
                    </select>
                </div>
                <div class="col-md-2 col-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Contributions Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-list-alt text-primary me-2"></i>सदस्य अंशदान सूची (Event-Wise Member Contributions)
            </h5>
            <span class="badge bg-label-primary fs-6">
                कुल: {{ $contributions->total() }} सदस्य
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Member Name (सदस्य)</th>
                        <th>Age (आयु)</th>
                        <th>Age Slab (आयु वर्ग)</th>
                        <th class="text-end">Contribution (अंशदान)</th>
                        <th class="text-center">Status</th>
                        <th>Receipt No</th>
                        <th>Payment Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contributions as $index => $c)
                    <tr>
                        <td>{{ $contributions->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $c->member_name }}</strong>
                            <small class="text-muted d-block">
                                <span class="badge bg-label-secondary font-monospace">{{ $c->member ? $c->member->membership_no : 'N/A' }}</span>
                                {{ $c->member ? $c->member->mobile : '' }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark fw-bold">{{ $c->member_age }} वर्ष</span>
                        </td>
                        <td>
                            <span class="badge bg-label-primary px-2 py-1">{{ $c->age_slab }}</span>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-success fs-6">₹{{ number_format($c->contribution_amount, 2) }}</span>
                        </td>
                        <td class="text-center">
                            @if($c->payment_status === 'Paid')
                                <span class="badge bg-success px-3 py-1">
                                    <i class="fas fa-check-circle me-1"></i> Paid
                                </span>
                            @else
                                <span class="badge bg-warning text-dark px-3 py-1">
                                    <i class="fas fa-hourglass-half me-1"></i> Pending
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($c->receipt_no)
                                <strong class="text-primary font-monospace">{{ $c->receipt_no }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            {{ $c->payment_date ? $c->payment_date->format('d/m/Y') : '-' }}
                        </td>
                        <td class="text-end">
                            @if($c->payment_status === 'Pending')
                                <a href="{{ route('admin.payments.create', ['contribution_id' => $c->id]) }}" class="btn btn-sm btn-success fw-semibold shadow-sm">
                                    <i class="fas fa-cash-register me-1"></i> Collect Cash
                                </a>
                            @elseif($c->payment_id)
                                <a href="{{ route('admin.payments.receipt', $c->payment_id) }}" class="btn btn-sm btn-outline-primary" target="_blank" title="View Official Receipt">
                                    <i class="fas fa-receipt me-1"></i> Receipt
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-users-slash fs-1 text-secondary d-block mb-2"></i>
                            No member contribution records found matching your filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contributions->hasPages())
        <div class="card-footer py-3 border-top">
            <div class="d-flex justify-content-end">
                {{ $contributions->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
