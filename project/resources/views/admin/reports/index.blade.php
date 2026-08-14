@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">रिपोर्ट केंद्र (Society Reports Center & Excel Export)</h4>
                    <p class="text-muted mb-0">Generate, audit, and export comprehensive society financial, member, and agent reports to Excel CSV.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reports.export', ['type' => $type]) }}" class="btn btn-success">
                        <i class="fas fa-file-excel me-1"></i> Export to Excel (CSV)
                    </a>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Type Switcher Pills -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.index', ['type' => 'collection']) }}" class="btn btn-sm {{ $type == 'collection' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-receipt me-1"></i> 1. Collections Report
                </a>
                <a href="{{ route('admin.reports.index', ['type' => 'agent']) }}" class="btn btn-sm {{ $type == 'agent' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-user-tie me-1"></i> 2. Agent Collections
                </a>
                <a href="{{ route('admin.reports.index', ['type' => 'pending']) }}" class="btn btn-sm {{ $type == 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-clock me-1"></i> 3. Overdue Pending Dues
                </a>
                <a href="{{ route('admin.reports.index', ['type' => 'commission']) }}" class="btn btn-sm {{ $type == 'commission' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-percentage me-1"></i> 4. Agent Commission
                </a>
                <a href="{{ route('admin.reports.index', ['type' => 'members']) }}" class="btn btn-sm {{ $type == 'members' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-users me-1"></i> 5. Member Directory
                </a>
                <a href="{{ route('admin.reports.index', ['type' => 'events']) }}" class="btn btn-sm {{ $type == 'events' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-calendar-alt me-1"></i> 6. Marriage Events
                </a>
                <a href="{{ route('admin.reports.index', ['type' => 'monthly']) }}" class="btn btn-sm {{ $type == 'monthly' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-chart-bar me-1"></i> 7. Monthly Summary
                </a>
            </div>
        </div>
    </div>

    <!-- Dynamic Report Content -->
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-semibold text-uppercase">
                @if($type == 'agent') Agent-wise Collection Performance Report
                @elseif($type == 'pending') Overdue / Pending Support Payments Report
                @elseif($type == 'commission') Agent Commission Statement Report
                @elseif($type == 'members') Comprehensive Member Enrolment Report
                @elseif($type == 'events') Marriage Welfare Events Assistance Report
                @elseif($type == 'monthly') Monthly Collection Aggregated Summary
                @else Overall Society Collections & Receipts Report
                @endif
            </h5>
            <span class="badge bg-label-primary">{{ count($data) }} Records</span>
        </div>

        <div class="table-responsive text-nowrap">
            @if($type == 'members')
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mem No</th>
                        <th>Member Name</th>
                        <th>Mobile</th>
                        <th>Scheme</th>
                        <th>Age</th>
                        <th>District</th>
                        <th>Agent</th>
                        <th>Monthly Support</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $m)
                    <tr>
                        <td><strong>{{ $m->membership_no }}</strong></td>
                        <td>{{ $m->full_name }}</td>
                        <td>{{ $m->mobile }}</td>
                        <td><span class="badge bg-label-primary">{{ $m->scheme ? $m->scheme->name_hindi : '' }}</span></td>
                        <td>{{ $m->age }} Yrs</td>
                        <td>{{ $m->district }}</td>
                        <td>{{ $m->agent ? $m->agent->name : 'HQ' }}</td>
                        <td><strong class="text-success">₹{{ number_format($m->monthly_support_amount) }}</strong></td>
                        <td><span class="badge bg-success">{{ $m->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @elseif($type == 'pending')
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Membership No</th>
                        <th>Member Name</th>
                        <th>Mobile</th>
                        <th>Scheme</th>
                        <th>Agent</th>
                        <th>Pending Overdue Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $m)
                    <tr>
                        <td><strong class="text-primary">{{ $m->membership_no }}</strong></td>
                        <td><strong>{{ $m->full_name }}</strong></td>
                        <td>{{ $m->mobile }}</td>
                        <td><span class="badge bg-label-primary">{{ $m->scheme ? $m->scheme->name_hindi : '' }}</span></td>
                        <td>{{ $m->agent ? $m->agent->name : 'HQ Direct' }}</td>
                        <td><strong class="text-danger fs-6">₹{{ number_format($m->pending_amount) }}</strong></td>
                        <td><span class="badge bg-danger">Pending</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @elseif($type == 'agent' || $type == 'commission')
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Agent Code</th>
                        <th>Agent Name</th>
                        <th>Mobile</th>
                        <th>District</th>
                        <th>Total Members</th>
                        <th>Total Collection</th>
                        <th>Rate (%)</th>
                        <th>Commission Due</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $a)
                    <tr>
                        <td><strong class="text-primary">{{ $a->agent_code }}</strong></td>
                        <td><strong>{{ $a->name }}</strong></td>
                        <td>{{ $a->mobile }}</td>
                        <td>{{ $a->district }}</td>
                        <td><span class="badge bg-label-primary">{{ $a->members->count() }} Members</span></td>
                        <td><strong class="text-success">₹{{ number_format($a->total_collection) }}</strong></td>
                        <td>{{ $a->commission_rate }}%</td>
                        <td><strong class="text-warning fs-6">₹{{ number_format($a->total_commission) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @elseif($type == 'monthly')
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Month & Year</th>
                        <th>Total Receipts</th>
                        <th>Total Collected (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $mo)
                    <tr>
                        <td><strong class="text-primary fs-6">{{ $mo->month_year }}</strong></td>
                        <td><span class="badge bg-label-info">{{ $mo->count }} Receipts</span></td>
                        <td><strong class="text-success fs-5">₹{{ number_format($mo->total) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @elseif($type == 'events')
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Event Code</th>
                        <th>Event Title</th>
                        <th>Girl Name</th>
                        <th>Date</th>
                        <th>Target Amount</th>
                        <th>Collected</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $ev)
                    <tr>
                        <td><strong class="text-primary">{{ $ev->event_code }}</strong></td>
                        <td><strong>{{ $ev->title }}</strong></td>
                        <td>{{ $ev->girl_name }}</td>
                        <td>{{ $ev->event_date ? $ev->event_date->format('d M Y') : '' }}</td>
                        <td><strong class="text-success">₹{{ number_format($ev->target_amount) }}</strong></td>
                        <td><strong class="text-primary">₹{{ number_format($ev->collected_amount) }}</strong></td>
                        <td><span class="badge bg-success">{{ $ev->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @else
            <!-- Collection & Payments Report -->
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt No</th>
                        <th>SAN Code</th>
                        <th>Member Details</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Mode</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $p)
                    <tr>
                        <td><strong class="text-primary">{{ $p->receipt_no }}</strong></td>
                        <td><span class="badge bg-label-secondary">{{ $p->san_code }}</span></td>
                        <td>
                            <strong>{{ $p->member ? $p->member->full_name : 'N/A' }}</strong>
                            <small class="d-block text-muted">{{ $p->member ? $p->member->membership_no : '' }}</small>
                        </td>
                        <td><strong class="text-success fs-6">₹{{ number_format($p->amount) }}</strong></td>
                        <td><span class="badge bg-label-primary">{{ $p->payment_type }}</span></td>
                        <td><span class="badge bg-label-info">{{ $p->payment_mode }}</span></td>
                        <td>{{ $p->payment_date ? $p->payment_date->format('d M Y') : '' }}</td>
                        <td><span class="badge bg-success">{{ $p->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection
