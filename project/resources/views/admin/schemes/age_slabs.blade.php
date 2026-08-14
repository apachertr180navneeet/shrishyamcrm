@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">आयु वर्ग विन्यास (Age Slabs Master)</h4>
                    <p class="text-muted mb-0">Configure dynamic age limits, initial joining fees, and monthly support amounts per scheme with full add, edit, and status controls.</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSlabModal">
                        <i class="fas fa-plus me-1"></i> Add New Age Slab
                    </button>
                    <a href="{{ route('admin.schemes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Schemes Master
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Age Slabs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-semibold">All Configured Scheme Age Slabs</h5>
            <span class="badge bg-label-primary">{{ $ageSlabs->count() }} Slabs</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Slab Code</th>
                        <th>Scheme</th>
                        <th>Age Range</th>
                        <th>Joining Amount</th>
                        <th>Monthly Support</th>
                        <th>Status (Enum)</th>
                        <th>Effective Period</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ageSlabs as $slab)
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
                        <td>
                            <span class="badge {{ $slab->status == 'Active' ? 'bg-success' : 'bg-danger' }}">
                                {{ $slab->status }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $slab->effective_from ?? '2021-01-01' }} to {{ $slab->effective_to ?? '2030-12-31' }}</small></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" title="Edit Slab"
                                    onclick="openEditSlabModal({{ json_encode($slab) }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.schemes.age-slabs.destroy', $slab->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this age slab?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete Slab">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            No age slabs configured yet. Click "Add New Age Slab" to create one.
                        </td>
                    </tr>
                    @endforelse
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
                        <label class="form-label fw-semibold">Select Scheme <span class="text-danger">*</span></label>
                        <select name="scheme_id" class="form-select" required>
                            @foreach($schemes as $sch)
                            <option value="{{ $sch->id }}">{{ $sch->name_hindi }} ({{ $sch->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slab Identifier / Code</label>
                        <input type="text" name="slab_code" class="form-control" placeholder="e.g. SLAB-S1">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Minimum Age (Yrs) <span class="text-danger">*</span></label>
                            <input type="number" name="min_age" class="form-control" placeholder="e.g. 18" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Maximum Age (Yrs) <span class="text-danger">*</span></label>
                            <input type="number" name="max_age" class="form-control" placeholder="e.g. 40" min="0" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Joining Fee (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="joining_amount" class="form-control" placeholder="e.g. 1100" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Monthly Support (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="support_amount" class="form-control" placeholder="e.g. 200" min="0" required>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Status (Enum) <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="Active" selected>Active (सक्रिय)</option>
                            <option value="Inactive">Inactive (निष्क्रिय)</option>
                        </select>
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

<!-- Edit Slab Modal -->
<div class="modal fade" id="editSlabModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editSlabForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Scheme Age Slab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Scheme <span class="text-danger">*</span></label>
                        <select name="scheme_id" id="edit_slab_scheme_id" class="form-select" required>
                            @foreach($schemes as $sch)
                            <option value="{{ $sch->id }}">{{ $sch->name_hindi }} ({{ $sch->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slab Identifier / Code</label>
                        <input type="text" name="slab_code" id="edit_slab_code" class="form-control">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Minimum Age (Yrs) <span class="text-danger">*</span></label>
                            <input type="number" name="min_age" id="edit_slab_min_age" class="form-control" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Maximum Age (Yrs) <span class="text-danger">*</span></label>
                            <input type="number" name="max_age" id="edit_slab_max_age" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Joining Fee (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="joining_amount" id="edit_slab_joining_amount" class="form-control" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Monthly Support (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="support_amount" id="edit_slab_support_amount" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Status (Enum) <span class="text-danger">*</span></label>
                        <select name="status" id="edit_slab_status" class="form-select" required>
                            <option value="Active">Active (सक्रिय)</option>
                            <option value="Inactive">Inactive (निष्क्रिय)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Age Slab</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function openEditSlabModal(slab) {
    document.getElementById('editSlabForm').action = "/admin/age-slabs/" + slab.id;
    document.getElementById('edit_slab_scheme_id').value = slab.scheme_id || '';
    document.getElementById('edit_slab_code').value = slab.slab_code || '';
    document.getElementById('edit_slab_min_age').value = slab.min_age || 0;
    document.getElementById('edit_slab_max_age').value = slab.max_age || 0;
    document.getElementById('edit_slab_joining_amount').value = slab.joining_amount || 0;
    document.getElementById('edit_slab_support_amount').value = slab.support_amount || 0;
    document.getElementById('edit_slab_status').value = slab.status || 'Active';

    const modal = new bootstrap.Modal(document.getElementById('editSlabModal'));
    modal.show();
}
</script>
@endsection
