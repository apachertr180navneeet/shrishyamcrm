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
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Agent Profile
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick='openEditAgentModal(@json($agent))'>
                        <i class="fas fa-bolt me-1"></i> Quick Edit
                    </button>
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

<!-- Edit Agent Modal -->
<div class="modal fade" id="editAgentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editAgentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Society Agent (<span id="editAgentCodeBadge" class="text-primary">{{ $agent->agent_code }}</span>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Agent Full Name (पूरा नाम) <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_agent_name" class="form-control" value="{{ $agent->name }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" name="mobile" id="edit_agent_mobile" class="form-control" value="{{ $agent->mobile }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">District (जिला) <span class="text-danger">*</span></label>
                            <input type="text" name="district" id="edit_agent_district" class="form-control" value="{{ $agent->district }}" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" id="edit_agent_email" class="form-control" value="{{ $agent->email }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Commission Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" name="commission_rate" id="edit_agent_commission_rate" class="form-control" value="{{ $agent->commission_rate }}" step="0.5" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit_agent_status" class="form-select" required>
                                <option value="Active" {{ $agent->status == 'Active' ? 'selected' : '' }}>Active (सक्रिय)</option>
                                <option value="Inactive" {{ $agent->status == 'Inactive' ? 'selected' : '' }}>Inactive (निष्क्रिय)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">New Password (वैकल्पिक)</label>
                            <input type="password" name="password" id="edit_agent_password" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Office / Residential Address</label>
                        <textarea name="address" id="edit_agent_address" class="form-control" rows="2">{{ $agent->address }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Agent</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function openEditAgentModal(agent) {
    document.getElementById('editAgentForm').action = "{{ url('admin/agents') }}/" + agent.id;
    document.getElementById('editAgentCodeBadge').innerText = agent.agent_code || '';
    document.getElementById('edit_agent_name').value = agent.name || '';
    document.getElementById('edit_agent_mobile').value = agent.mobile || (agent.user ? agent.user.phone : '');
    document.getElementById('edit_agent_district').value = agent.district || '';
    document.getElementById('edit_agent_email').value = agent.email || (agent.user ? agent.user.email : '');
    document.getElementById('edit_agent_commission_rate').value = agent.commission_rate || 5.0;
    document.getElementById('edit_agent_status').value = agent.status || 'Active';
    document.getElementById('edit_agent_password').value = '';
    document.getElementById('edit_agent_address').value = agent.address || '';

    const modal = new bootstrap.Modal(document.getElementById('editAgentModal'));
    modal.show();
}
</script>
@endsection

