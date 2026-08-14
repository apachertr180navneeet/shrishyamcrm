@extends('admin.layouts.app')

@section('style')
<style>
    .scheme-card-exact {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
    }
    .scheme-card-exact:hover {
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    }
    .scheme-header-exact {
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #F1F5F9;
    }
    .scheme-title-exact {
        font-family: 'Hind', sans-serif;
        font-weight: 700;
        font-size: 1.15rem;
        color: #1B365D;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin: 0;
    }
    .badge-status-active {
        background-color: #DCFCE7 !important;
        color: #15803D !important;
        padding: 4px 14px;
        border-radius: 9999px;
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.3px;
        display: inline-block;
    }
    .badge-status-inactive {
        background-color: #FEE2E2 !important;
        color: #B91C1C !important;
        padding: 4px 14px;
        border-radius: 9999px;
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.3px;
        display: inline-block;
    }
    .scheme-body-exact {
        padding: 1.5rem;
    }
    .scheme-desc-exact {
        font-size: 0.88rem;
        color: #64748B;
        margin-bottom: 1.25rem;
        line-height: 1.5;
    }
    .scheme-meta-box-exact {
        background-color: #F8FAFC;
        border: 1px solid #F1F5F9;
        border-radius: 8px;
        padding: 0.85rem 1.15rem;
        font-size: 0.84rem;
        margin-bottom: 1.35rem;
        line-height: 1.6;
        color: #334155;
    }
    .scheme-slabs-title-exact {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1B365D;
        margin-bottom: 0.65rem;
    }
    .table-slabs-exact {
        width: 100%;
        font-size: 0.84rem;
        border-collapse: collapse;
    }
    .table-slabs-exact thead th {
        background-color: #F8FAFC;
        color: #64748B;
        font-weight: 600;
        padding: 9px 12px;
        border-top: 1px solid #F1F5F9;
        border-bottom: 1px solid #E2E8F0;
    }
    .table-slabs-exact tbody td {
        padding: 11px 12px;
        border-bottom: 1px solid #F1F5F9;
        color: #1E293B;
        font-weight: 600;
    }
    .table-slabs-exact tbody tr:last-child td {
        border-bottom: none;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Top Action Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif; color: #1B365D;">योजना प्रबंधन (Membership Scheme Master)</h4>
                    <p class="text-muted mb-0">Configurable society membership schemes, age slabs and contribution amounts.</p>
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

    <!-- Schemes Side-by-Side Cards Grid Matching Screenshot -->
    <div class="row g-4 mb-4">
        @forelse($schemes as $s)
        <div class="col-lg-6 col-12">
            <div class="scheme-card-exact h-100 d-flex flex-column justify-content-between">
                <div>
                    <!-- Header with Icon, Hindi Title, Status & Actions -->
                    <div class="scheme-header-exact">
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="scheme-title-exact">
                                @if($s->code == 'SENIOR' || str_contains(strtolower($s->name), 'senior'))
                                    <i class="fas fa-user-nurse" style="color: #2563EB; font-size: 1.15rem;"></i>
                                @elseif($s->code == 'MARRIAGE' || str_contains(strtolower($s->name), 'marriage'))
                                    <i class="fas fa-female" style="color: #EA580C; font-size: 1.25rem;"></i>
                                @else
                                    <i class="fas fa-hand-holding-heart" style="color: #2563EB; font-size: 1.15rem;"></i>
                                @endif
                                <span>{{ $s->name_hindi }}</span>
                            </h4>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <!-- Clickable Status Badge -->
                            <form action="{{ route('admin.schemes.toggle-status', $s->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="border-0 p-0 bg-transparent" title="Click to Toggle Status">
                                    <span class="{{ $s->status == 'Active' ? 'badge-status-active' : 'badge-status-inactive' }}">
                                        {{ $s->status }}
                                    </span>
                                </button>
                            </form>

                            <!-- Dropdown Actions -->
                            <div class="dropdown">
                                <button class="btn btn-sm btn-icon text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <button class="dropdown-item" onclick="openEditSchemeModal({{ json_encode($s) }})">
                                            <i class="fas fa-edit me-2 text-primary"></i> Edit Scheme
                                        </button>
                                    </li>
                                    <li>
                                        <form action="{{ route('admin.schemes.toggle-status', $s->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas {{ $s->status == 'Active' ? 'fa-ban text-warning' : 'fa-check text-success' }} me-2"></i> Mark as {{ $s->status == 'Active' ? 'Inactive' : 'Active' }}
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.schemes.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this scheme?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash-alt me-2"></i> Delete Scheme
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="scheme-body-exact pb-0">
                        <p class="scheme-desc-exact">
                            {{ $s->description ?: 'Welfare assistance scheme for registered society members.' }}
                        </p>

                        <!-- Gray Info Box -->
                        <div class="scheme-meta-box-exact">
                            <div><strong>Effective Period:</strong> {{ $s->effective_from ? date('d-m-Y', strtotime($s->effective_from)) : '01-01-2021' }} to {{ $s->effective_to ? date('d-m-Y', strtotime($s->effective_to)) : '31-12-2030' }}</div>
                            <div><strong>Active Enrolled Members:</strong> {{ $s->members ? $s->members->count() : 0 }} Members</div>
                        </div>

                        <!-- Configured Age Slabs Table -->
                        <h5 class="scheme-slabs-title-exact">Configured Age Slabs:</h5>
                        <div class="table-responsive">
                            <table class="table-slabs-exact">
                                <thead>
                                    <tr>
                                        <th>Age Bracket</th>
                                        <th>Joining Fee</th>
                                        <th>Support Amt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($s->ageSlabs as $sl)
                                    <tr>
                                        <td>{{ $sl->min_age }} – {{ $sl->max_age }} Years</td>
                                        <td>₹{{ number_format($sl->joining_amount) }}</td>
                                        <td>₹{{ number_format($sl->support_amount) }}/mo</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No age slabs configured yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer Quick Action -->
                <div class="p-3 pt-2 text-end">
                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="openEditSchemeModal({{ json_encode($s) }})">
                        <i class="fas fa-edit me-1"></i> Edit Scheme
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="fas fa-hand-holding-heart fs-1 d-block mb-3"></i>
            <h5>No schemes registered. Click "Add New Scheme" to create one.</h5>
        </div>
        @endforelse
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
                                <option value="General Welfare">General Welfare</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Status (Enum) <span class="text-danger">*</span></label>
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
                        <textarea name="description" class="form-control" rows="3" placeholder="Scheme description..."></textarea>
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
