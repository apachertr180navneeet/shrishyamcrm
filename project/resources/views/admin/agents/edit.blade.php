@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Breadcrumb & Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.agents.index') }}">Agent Network</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Agent</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="font-family: 'Hind', sans-serif;">
                एजेंट प्रोफाइल संपादित करें (Edit Agent: {{ $agent->name }})
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.agents.show', $agent->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-id-card me-1"></i> View Profile
            </a>
            <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Agents
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Edit Form -->
        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-user-edit text-primary fs-5"></i>
                        <h5 class="card-title mb-0 fw-semibold">Agent Profile Details</h5>
                    </div>
                    <span class="badge bg-label-primary fs-6">{{ $agent->agent_code }}</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.agents.update', $agent->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Agent Code (एजेंट कोड)</label>
                                <input type="text" class="form-control bg-light" value="{{ $agent->agent_code }}" readonly>
                                <small class="text-muted">Unique system generated code</small>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Status (स्थिति) <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="Active" {{ old('status', $agent->status) === 'Active' ? 'selected' : '' }}>Active (सक्रिय)</option>
                                    <option value="Inactive" {{ old('status', $agent->status) === 'Inactive' ? 'selected' : '' }}>Inactive (निष्क्रिय)</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-12 col-12">
                                <label class="form-label fw-semibold">Agent Full Name (पूरा नाम) <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $agent->name) }}" placeholder="e.g. Rameshwar Sharma" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Mobile Number (मोबाइल नंबर) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $agent->mobile) }}" placeholder="10-digit mobile" required>
                                </div>
                                @error('mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">District (जिला) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" name="district" class="form-control @error('district') is-invalid @enderror" value="{{ old('district', $agent->district) }}" placeholder="e.g. Mahendragarh, Rewari" required>
                                </div>
                                @error('district')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Email Address (ईमेल पता)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $agent->email ?? $agent->user?->email) }}" placeholder="agent@email.com">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Commission Rate (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="commission_rate" class="form-control @error('commission_rate') is-invalid @enderror" value="{{ old('commission_rate', $agent->commission_rate) }}" step="0.1" min="0" max="100" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('commission_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Update Login Password (लॉगिन पासवर्ड बदलें)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="agentEditPassword" class="form-control @error('password') is-invalid @enderror" placeholder="Leave blank to keep existing password">
                                <button class="btn btn-outline-secondary" type="button" onclick="const p = document.getElementById('agentEditPassword'); p.type = p.type === 'password' ? 'text' : 'password';">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Fill only if you want to reset the agent's account login password (minimum 6 characters).</small>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Office / Residential Address (पता)</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3" placeholder="Complete residential / office address...">{{ old('address', $agent->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                            <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Agent Summary & Stats -->
        <div class="col-lg-4 col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    <div class="avatar avatar-xl bg-label-primary mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="fas fa-user-tie fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $agent->name }}</h5>
                    <p class="text-muted mb-2"><i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $agent->district }}</p>
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-label-primary">{{ $agent->agent_code }}</span>
                        <span class="badge {{ $agent->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">{{ $agent->status }}</span>
                    </div>

                    <div class="border-top pt-3 text-start">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Assigned Members:</span>
                            <strong class="text-primary">{{ $agent->members->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Collections:</span>
                            <strong class="text-success">₹{{ number_format($agent->total_collection) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Commission Earned:</span>
                            <strong class="text-warning">₹{{ number_format($agent->total_commission) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Linked Login User:</span>
                            <span class="badge bg-label-info">{{ $agent->user ? $agent->user->email : 'No linked user' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Action Box -->
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-2"><i class="fas fa-info-circle text-primary me-1"></i> System Information</h6>
                    <p class="small text-muted mb-2">
                        Updating an agent automatically updates their linked user credentials and contact information.
                    </p>
                    <p class="small text-muted mb-0">
                        Agent Code: <strong>{{ $agent->agent_code }}</strong> (Immutable)
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
