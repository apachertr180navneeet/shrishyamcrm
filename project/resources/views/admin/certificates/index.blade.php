@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">सदस्यता प्रमाण-पत्र (Membership Registration Certificates)</h4>
                    <p class="text-muted mb-0">Generate and print official gold-bordered membership certificates for enrolled society members.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.certificates.index') }}" method="GET" class="row g-3">
                <div class="col-lg-5 col-md-6 col-12">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, memberID, membership no, mobile..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-12">
                    <select name="scheme_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Schemes (सभी योजनाएं) --</option>
                        @foreach($schemes as $sch)
                            <option value="{{ $sch->id }}" {{ request('scheme_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name_hindi ?: $sch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-2 col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Search</button>
                    @if(request('search') || request('scheme_id'))
                        <a href="{{ route('admin.certificates.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Membership No</th>
                        <th>Member Name</th>
                        <th>Mobile</th>
                        <th>Enrolled Scheme</th>
                        <th>Joining Date</th>
                        <th>Status</th>
                        <th class="text-center">Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $m)
                    <tr>
                        <td>
                            <strong class="text-primary">{{ $m->membership_no }}</strong>
                            @if($m->memberID)
                                <div><span class="badge bg-label-info font-monospace py-0 px-1" style="font-size: 11px;">Party ID: {{ $m->memberID }}</span></div>
                            @endif
                        </td>
                        <td><strong>{{ $m->full_name }}</strong></td>
                        <td>{{ $m->mobile }}</td>
                        <td><span class="badge bg-label-primary">{{ $m->scheme ? $m->scheme->name_hindi : 'N/A' }}</span></td>
                        <td>{{ $m->joining_date ? $m->joining_date->format('d M Y') : '' }}</td>
                        <td><span class="badge bg-success">{{ $m->status }}</span></td>
                        <td class="text-center">
                            <a href="{{ route('admin.certificates.show', $m->id) }}" class="btn btn-sm btn-warning text-dark fw-semibold" target="_blank">
                                <i class="fas fa-certificate me-1"></i> Print Certificate
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3">
            {{ $members->links() }}
        </div>
    </div>
</div>
@endsection
