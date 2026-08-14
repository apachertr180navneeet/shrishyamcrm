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
                        <td><strong class="text-primary">{{ $m->membership_no }}</strong></td>
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
