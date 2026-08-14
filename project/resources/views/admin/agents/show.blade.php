@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Agent Header Profile -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-xl bg-label-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
                        <i class="fas fa-user-tie fs-2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h3 class="fw-bold mb-0">{{ $agent->name }}</h3>
                            <span class="badge bg-label-primary">{{ $agent->agent_code }}</span>
                            <span class="badge bg-success">{{ $agent->status }}</span>
                        </div>
                        <p class="text-muted mb-0">
                            <i class="fas fa-phone-alt me-1 text-primary"></i> {{ $agent->mobile }} &nbsp;|&nbsp;
                            <i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ $agent->district }} &nbsp;|&nbsp;
                            <i class="fas fa-percentage me-1 text-warning"></i> Commission Rate: {{ $agent->commission_rate }}%
                        </p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Agents
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-semibold">Assigned Members</small>
                <h3 class="fw-bold text-primary my-1">{{ $agent->members->count() }} Members</h3>
                <small class="text-muted">Active in {{ $agent->district }}</small>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-semibold">Total Collections</small>
                <h3 class="fw-bold text-success my-1">₹{{ number_format($agent->total_collection) }}</h3>
                <small class="text-success"><i class="fas fa-check-circle me-1"></i> Recorded in system</small>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-semibold">Commission Earned</small>
                <h3 class="fw-bold text-warning my-1">₹{{ number_format($agent->total_commission) }}</h3>
                <small class="text-muted">Calculated at {{ $agent->commission_rate }}%</small>
            </div>
        </div>
    </div>

    <!-- Assigned Members Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header border-bottom py-3">
            <h5 class="card-title mb-0 fw-semibold">Assigned Society Members ({{ $agent->members->count() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Membership No</th>
                        <th>Member Name</th>
                        <th>Mobile</th>
                        <th>Scheme</th>
                        <th>Monthly Support</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agent->members as $m)
                    <tr>
                        <td><strong class="text-primary">{{ $m->membership_no }}</strong></td>
                        <td><strong>{{ $m->full_name }}</strong></td>
                        <td>{{ $m->mobile }}</td>
                        <td><span class="badge bg-label-primary">{{ $m->scheme ? $m->scheme->name_hindi : 'N/A' }}</span></td>
                        <td><strong class="text-success">₹{{ number_format($m->monthly_support_amount) }}/mo</strong></td>
                        <td><span class="badge bg-success">{{ $m->status }}</span></td>
                        <td class="text-center">
                            <a href="{{ route('admin.members.show', $m->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No members assigned to this agent yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
