@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">सदस्य निर्देशिका (Society Members Directory)</h4>
                    <p class="text-muted mb-0">Total {{ $members->total() }} registered society members across all schemes and agent districts.</p>
                </div>
                <a href="{{ route('admin.members.create') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus me-1"></i> Add New Member
                </a>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.members.index') }}" method="GET" class="row g-3">
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, member no, mobile, aadhaar..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <select name="scheme_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Schemes (सभी योजनाएं)</option>
                        @foreach($schemes as $sch)
                        <option value="{{ $sch->id }}" {{ request('scheme_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name_hindi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <select name="agent_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Agents (सभी एजेंट)</option>
                        @foreach($agents as $agt)
                        <option value="{{ $agt->id }}" {{ request('agent_id') == $agt->id ? 'selected' : '' }}>{{ $agt->name }} ({{ $agt->agent_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Members Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Member No</th>
                        <th>Member Name & Details</th>
                        <th>Scheme</th>
                        <th>Age / DOB</th>
                        <th>Agent</th>
                        <th>Monthly Support</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $m)
                    <tr>
                        <td>
                            <a href="{{ route('admin.members.show', $m->id) }}" class="fw-bold text-primary">
                                {{ $m->membership_no }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fas {{ $m->gender == 'Female' ? 'fa-female' : 'fa-user' }}"></i>
                                </div>
                                <div>
                                    <strong class="d-block text-heading">{{ $m->full_name }}</strong>
                                    <small class="text-muted"><i class="fas fa-phone-alt me-1" style="font-size: 10px;"></i>{{ $m->mobile }} | Gotra: {{ $m->gotra ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $m->scheme && $m->scheme->code == 'SENIOR' ? 'bg-label-primary' : 'bg-label-warning' }}">
                                {{ $m->scheme ? $m->scheme->name_hindi : 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $m->age }} Yrs</span>
                            <small class="d-block text-muted">{{ $m->dob ? $m->dob->format('d M Y') : '' }}</small>
                        </td>
                        <td>
                            <span>{{ $m->agent ? $m->agent->name : 'HQ Direct' }}</span>
                            <small class="d-block text-muted">{{ $m->agent ? $m->agent->district : '' }}</small>
                        </td>
                        <td>
                            <strong class="text-success">₹{{ number_format($m->monthly_support_amount) }}/mo</strong>
                            @if($m->pending_amount > 0)
                                <small class="d-block text-danger"><i class="fas fa-exclamation-circle me-1"></i>Due: ₹{{ number_format($m->pending_amount) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($m->status == 'Active')
                                <span class="badge bg-success">Active</span>
                            @elseif($m->status == 'Inactive')
                                <span class="badge bg-danger">Inactive</span>
                            @else
                                <span class="badge bg-secondary">{{ $m->status }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.members.show', $m->id) }}" class="btn btn-outline-primary" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.certificates.show', $m->id) }}" class="btn btn-outline-warning" title="Certificate" target="_blank">
                                    <i class="fas fa-certificate"></i>
                                </a>
                                <a href="{{ route('admin.payments.create', ['member_id' => $m->id]) }}" class="btn btn-outline-success" title="Record Payment">
                                    <i class="fas fa-rupee-sign"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-users-slash fs-1 d-block mb-2"></i>
                            No members found matching your search filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3">
            {{ $members->links() }}
        </div>
    </div>
</div>
@endsection
