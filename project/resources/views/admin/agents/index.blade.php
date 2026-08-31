@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">एजेंट नेटवर्क (Agent Network Directory)</h4>
                    <p class="text-muted mb-0">Manage society agents across all districts, commission structures, and field collection metrics.</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAgentModal">
                    <i class="fas fa-user-plus me-1"></i> Register New Agent
                </button>
            </div>
        </div>
    </div>

    <!-- Agent Cards Grid -->
    <div class="row g-4">
        @forelse($agents as $agent)
        <div class="col-xl-4 col-md-6 col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-label-primary fs-6">{{ $agent->agent_code }}</span>
                        <span class="badge bg-success">{{ $agent->status }}</span>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar avatar-md bg-label-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-user-tie fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-heading">{{ $agent->name }}</h5>
                            <small class="text-muted d-block"><i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ $agent->district ?? 'N/A' }}</small>
                            <small class="text-muted"><i class="fas fa-phone me-1 text-primary"></i> {{ $agent->mobile ?? ($agent->user?->phone ?? 'N/A') }}</small>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="bg-lighter p-2 rounded text-center">
                                <small class="text-muted d-block">Assigned Members</small>
                                <strong class="fs-5 text-primary">{{ $agent->members->count() }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-lighter p-2 rounded text-center">
                                <small class="text-muted d-block">Total Collection</small>
                                <strong class="fs-5 text-success">₹{{ number_format($agent->total_collection) }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                        <div>
                            <small class="text-muted d-block">Commission ({{ $agent->commission_rate }}%)</small>
                            <strong class="text-warning">₹{{ number_format($agent->total_commission) }}</strong>
                        </div>
                        <div>
                            <a href="{{ route('admin.agents.show', $agent->id) }}" class="btn btn-sm btn-outline-primary">
                                View Profile <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm p-5 text-center">
                <div class="avatar avatar-xl bg-label-primary mx-auto mb-3" style="width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="fas fa-user-tie fs-2"></i>
                </div>
                <h5 class="fw-bold mb-1">No Society Agents Registered</h5>
                <p class="text-muted mb-3">Agents created in User Management (with role Agent) or registered here will be listed here.</p>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAgentModal">
                        <i class="fas fa-user-plus me-1"></i> Register First Agent
                    </button>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Register Agent Modal -->
<div class="modal fade" id="addAgentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.agents.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Register New Society Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">First Name (पहला नाम) <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="e.g. Rameshwar" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Last Name (अंतिम नाम)</label>
                            <input type="text" name="last_name" class="form-control" placeholder="e.g. Sharma">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" name="mobile" class="form-control" placeholder="10 digit mobile" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">District <span class="text-danger">*</span></label>
                            <input type="text" name="district" class="form-control" placeholder="e.g. Mahendragarh" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="agent@mail.com">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Commission Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" name="commission_rate" class="form-control" value="5.00" step="0.5" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Agent Login Password (लॉगिन पासवर्ड) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="agentPasswordInput" class="form-control" placeholder="Set password for agent (min 6 characters)" minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="const p = document.getElementById('agentPasswordInput'); p.type = p.type === 'password' ? 'text' : 'password';">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Agent will log in with their Mobile/Email and this password.</small>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Office / Residential Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Full address..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register Agent</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
