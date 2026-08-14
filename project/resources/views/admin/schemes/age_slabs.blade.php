@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">आयु वर्ग विन्यास (Age Slabs Master)</h4>
                    <p class="text-muted mb-0">Configure dynamic age limits, initial joining fees, and monthly support amounts per scheme.</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSlabModal">
                    <i class="fas fa-plus me-1"></i> Add New Age Slab
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom py-3">
            <h5 class="card-title mb-0 fw-semibold">All Configured Scheme Age Slabs</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Slab Code</th>
                        <th>Scheme</th>
                        <th>Age Range</th>
                        <th>Joining Amount</th>
                        <th>Monthly Support Amount</th>
                        <th>Status</th>
                        <th>Effective Period</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ageSlabs as $slab)
                    <tr>
                        <td><strong class="text-primary">{{ $slab->slab_code }}</strong></td>
                        <td>
                            <div>
                                <strong style="font-family: 'Hind', sans-serif;">{{ $slab->scheme->name_hindi ?? '' }}</strong>
                                <small class="d-block text-muted">{{ $slab->scheme->name ?? '' }}</small>
                            </div>
                        </td>
                        <td><span class="badge bg-label-info fs-6">{{ $slab->min_age }} – {{ $slab->max_age }} Years</span></td>
                        <td><strong class="text-success">₹{{ number_format($slab->joining_amount) }}</strong></td>
                        <td><strong class="text-primary">₹{{ number_format($slab->support_amount) }}/mo</strong></td>
                        <td><span class="badge bg-success">{{ $slab->status }}</span></td>
                        <td><small class="text-muted">{{ $slab->effective_from ?? '2021-01-01' }} to {{ $slab->effective_to ?? '2030-12-31' }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Slab Modal -->
<div class="modal fade" id="addSlabModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.schemes.age-slabs.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Scheme Age Slab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Scheme</label>
                        <select name="scheme_id" class="form-select" required>
                            @foreach($schemes as $sch)
                            <option value="{{ $sch->id }}">{{ $sch->name_hindi }} ({{ $sch->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Minimum Age (Yrs)</label>
                            <input type="number" name="min_age" class="form-control" placeholder="e.g. 18" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Maximum Age (Yrs)</label>
                            <input type="number" name="max_age" class="form-control" placeholder="e.g. 40" min="0" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Joining Amount (₹)</label>
                            <input type="number" name="joining_amount" class="form-control" placeholder="e.g. 1100" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Monthly Support (₹)</label>
                            <input type="number" name="support_amount" class="form-control" placeholder="e.g. 200" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Age Slab</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
