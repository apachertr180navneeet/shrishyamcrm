@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">योजनाएं (Schemes Master)</h4>
                    <p class="text-muted mb-0">Manage welfare assistance schemes, age slab configurations, and policy amounts.</p>
                </div>
                <a href="{{ route('admin.schemes.age-slabs') }}" class="btn btn-primary">
                    <i class="fas fa-sliders-h me-1"></i> Configure Age Slabs
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach($schemes as $scheme)
        <div class="col-lg-6 col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between bg-light">
                    <div>
                        <span class="badge bg-primary mb-1">{{ $scheme->code }}</span>
                        <h5 class="card-title mb-0 fw-bold" style="font-family: 'Hind', sans-serif;">{{ $scheme->name_hindi }}</h5>
                        <small class="text-muted">{{ $scheme->name }}</small>
                    </div>
                    <span class="badge bg-success">{{ $scheme->status }}</span>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-3">{{ $scheme->description }}</p>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 bg-lighter rounded">
                                <small class="text-muted d-block">Enrolled Members</small>
                                <strong class="fs-5 text-primary">{{ $scheme->members->count() }} Members</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-lighter rounded">
                                <small class="text-muted d-block">Configured Slabs</small>
                                <strong class="fs-5 text-warning">{{ $scheme->ageSlabs->count() }} Slabs</strong>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-semibold mb-2">Age Slabs & Amount Breakdown:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Age Range</th>
                                    <th>Joining Amount</th>
                                    <th>Monthly Support</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scheme->ageSlabs as $slab)
                                <tr>
                                    <td><strong>{{ $slab->min_age }} – {{ $slab->max_age }} Years</strong></td>
                                    <td class="text-success fw-semibold">₹{{ number_format($slab->joining_amount) }}</td>
                                    <td class="text-primary fw-semibold">₹{{ number_format($slab->support_amount) }}/mo</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
