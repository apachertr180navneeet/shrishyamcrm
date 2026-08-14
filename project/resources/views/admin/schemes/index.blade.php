@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">योजनाएं (Schemes Master)</h4>
                    <p class="text-muted mb-0">Create, edit, manage active/inactive status, and configure age slab amounts for all welfare schemes.</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSchemeModal">
                        <i class="fas fa-plus me-1"></i> Add New Scheme
                    </button>
                    <a href="{{ route('admin.schemes.age-slabs') }}" class="btn btn-outline-primary">
                        <i class="fas fa-sliders-h me-1"></i> Configure Age Slabs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Schemes Table View -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-semibold">All Registered Schemes</h5>
            <span class="badge bg-label-primary">{{ $schemes->count() }} Schemes</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Scheme Code</th>
                        <th>Scheme Name (Hindi & English)</th>
                        <th>Scheme Type</th>
                        <th>Slabs Configured</th>
                        <th>Members Enrolled</th>
                        <th>Status (Enum)</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schemes as $s)
                    <tr>
                        <td><strong class="text-primary fs-6">{{ $s->code }}</strong></td>
                        <td>
                            <strong class="d-block text-heading" style="font-family: 'Hind', sans-serif;">{{ $s->name_hindi }}</strong>
                            <small class="text-muted">{{ $s->name }}</small>
                        </td>
                        <td><span class="badge bg-label-info">{{ $s->type }}</span></td>
                        <td>
                            <a href="{{ route('admin.schemes.age-slabs') }}" class="fw-semibold text-warning">
                                <i class="fas fa-sliders-h me-1"></i> {{ $s->ageSlabs->count() }} Slabs
                            </a>
                        </td>
                        <td>
                            <strong class="text-primary">{{ $s->members->count() }} Members</strong>
                        </td>
                        <td>
                            <form action="{{ route('admin.schemes.toggle-status', $s->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs {{ $s->status == 'Active' ? 'btn-success' : 'btn-danger' }}" title="Click to Toggle Status">
                                    <i class="fas {{ $s->status == 'Active' ? 'fa-check' : 'fa-times' }} me-1"></i> {{ $s->status }}
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" title="Edit Scheme"
                                    onclick="openEditSchemeModal({{ json_encode($s) }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.schemes.destroy', $s->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this scheme?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete Scheme">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-hand-holding-heart fs-1 d-block mb-2"></i>
                            No schemes found. Click "Add New Scheme" to create one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scheme Details Cards Grid -->
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
                    <form action="{{ route('admin.schemes.toggle-status', $scheme->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-xs {{ $scheme->status == 'Active' ? 'btn-success' : 'btn-danger' }}">
                            {{ $scheme->status }}
                        </button>
                    </form>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-3">{{ $scheme->description ?? 'Official welfare assistance scheme.' }}</p>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 bg-lighter rounded text-center">
                                <small class="text-muted d-block">Enrolled Members</small>
                                <strong class="fs-5 text-primary">{{ $scheme->members->count() }} Members</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-lighter rounded text-center">
                                <small class="text-muted d-block">Configured Slabs</small>
                                <strong class="fs-5 text-warning">{{ $scheme->ageSlabs->count() }} Slabs</strong>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <small class="text-muted">Effective: {{ $scheme->effective_from ?? '2021-01-01' }} to {{ $scheme->effective_to ?? '2030-12-31' }}</small>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openEditSchemeModal({{ json_encode($scheme) }})">
                            <i class="fas fa-edit me-1"></i> Edit Scheme
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Add Scheme Modal -->
<div class="modal fade" id="addSchemeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.schemes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-hand-holding-heart text-primary me-2"></i> Add New Welfare Scheme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Scheme Code (योजना कोड) <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. SENIOR, MARRIAGE" required style="text-transform: uppercase;">
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Hindi Name (हिंदी नाम) <span class="text-danger">*</span></label>
                            <input type="text" name="name_hindi" class="form-control" placeholder="e.g. बुजुर्ग सम्मान योजना" required>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">English Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Senior Welfare Scheme" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Scheme Type (प्रकार) <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="Senior Welfare Scheme">Senior Welfare Scheme</option>
                                <option value="Marriage Scheme">Marriage Scheme</option>
                                <option value="Medical Assistance">Medical Assistance</option>
                                <option value="Education Support">Education Support</option>
                                <option value="General Welfare">General Welfare</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Status (स्थिति - Enum) <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="Active" selected>Active (सक्रिय)</option>
                                <option value="Inactive">Inactive (निष्क्रिय)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Effective From</label>
                            <input type="date" name="effective_from" class="form-control" value="{{ date('Y-01-01') }}">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Effective To</label>
                            <input type="date" name="effective_to" class="form-control" value="2030-12-31">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">Description (विवरण)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Scheme objectives, terms and benefits..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i> Save Scheme</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Scheme Modal -->
<div class="modal fade" id="editSchemeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="editSchemeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit text-primary me-2"></i> Edit Welfare Scheme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Scheme Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="edit_code" class="form-control" required style="text-transform: uppercase;">
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Hindi Name <span class="text-danger">*</span></label>
                            <input type="text" name="name_hindi" id="edit_name_hindi" class="form-control" required>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">English Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Scheme Type <span class="text-danger">*</span></label>
                            <select name="type" id="edit_type" class="form-select" required>
                                <option value="Senior Welfare Scheme">Senior Welfare Scheme</option>
                                <option value="Marriage Scheme">Marriage Scheme</option>
                                <option value="Medical Assistance">Medical Assistance</option>
                                <option value="Education Support">Education Support</option>
                                <option value="General Welfare">General Welfare</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Status (Enum) <span class="text-danger">*</span></label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="Active">Active (सक्रिय)</option>
                                <option value="Inactive">Inactive (निष्क्रिय)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Effective From</label>
                            <input type="date" name="effective_from" id="edit_effective_from" class="form-control">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Effective To</label>
                            <input type="date" name="effective_to" id="edit_effective_to" class="form-control">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Scheme</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function openEditSchemeModal(scheme) {
    document.getElementById('editSchemeForm').action = "/admin/schemes/" + scheme.id;
    document.getElementById('edit_code').value = scheme.code || '';
    document.getElementById('edit_name_hindi').value = scheme.name_hindi || '';
    document.getElementById('edit_name').value = scheme.name || '';
    document.getElementById('edit_type').value = scheme.type || 'Senior Welfare Scheme';
    document.getElementById('edit_status').value = scheme.status || 'Active';
    document.getElementById('edit_effective_from').value = scheme.effective_from || '';
    document.getElementById('edit_effective_to').value = scheme.effective_to || '';
    document.getElementById('edit_description').value = scheme.description || '';

    const modal = new bootstrap.Modal(document.getElementById('editSchemeModal'));
    modal.show();
}
</script>
@endsection
