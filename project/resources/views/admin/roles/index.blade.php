@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;"><i class="fas fa-user-shield text-primary me-2"></i>भूमिकाएं एवं अनुमतियां (Roles & Permissions Structure)</h4>
                    <p class="text-muted mb-0">Configure system roles, access controls, and operational permissions for Admin, Agents, and staff.</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                    <i class="fas fa-plus-circle me-1"></i> Create New Role
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Roles Grid -->
    <div class="row g-4">
        @foreach($roles as $role)
        <div class="col-lg-6 col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between" style="background: #f8fafc;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $role->name === 'admin' ? 'bg-primary' : ($role->name === 'agent' ? 'bg-success' : 'bg-info') }} px-3 py-2 fs-6">
                            {{ $role->display_name }}
                        </span>
                        @if($role->display_name_hindi)
                            <span class="text-muted fw-semibold" style="font-family: 'Hind', sans-serif;">({{ $role->display_name_hindi }})</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-label-secondary">
                            <i class="fas fa-users me-1"></i> {{ $role->users->count() }} Users
                        </span>
                        <span class="badge bg-label-primary">
                            <i class="fas fa-key me-1"></i> {{ $role->permissions->count() }} Permissions
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">{{ $role->description ?: 'Operational role definition.' }}</p>

                    <form action="{{ route('admin.roles.permissions.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Display Name</label>
                                <input type="text" name="display_name" class="form-control form-control-sm" value="{{ $role->display_name }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Hindi Name</label>
                                <input type="text" name="display_name_hindi" class="form-control form-control-sm" value="{{ $role->display_name_hindi }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control form-control-sm" value="{{ $role->description }}">
                        </div>

                        <h6 class="fw-bold mb-2 text-dark border-bottom pb-2">
                            <i class="fas fa-lock me-1 text-warning"></i> Module Permissions:
                        </h6>

                        <div class="accordion accordion-flush" id="roleAcc{{ $role->id }}">
                            @foreach($permissions as $group => $perms)
                            <div class="accordion-item border rounded mb-2 overflow-hidden">
                                <h2 class="accordion-header" id="heading{{ $role->id }}{{ $group }}">
                                    <button class="accordion-button collapsed py-2 px-3 bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $role->id }}{{ $group }}">
                                        <div class="d-flex justify-content-between w-100 align-items-center me-3">
                                            <span class="fw-bold text-uppercase small text-dark">
                                                <i class="fas fa-folder me-1 text-primary"></i> {{ ucfirst($group) }}
                                            </span>
                                            <span class="badge bg-secondary rounded-pill small">
                                                {{ $perms->whereIn('id', $role->permissions->pluck('id'))->count() }} / {{ $perms->count() }}
                                            </span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $role->id }}{{ $group }}" class="accordion-collapse collapse" data-bs-parent="#roleAcc{{ $role->id }}">
                                    <div class="accordion-body p-3 bg-white">
                                        <div class="row g-2">
                                            @foreach($perms as $perm)
                                            <div class="col-md-6 col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                        id="p_{{ $role->id }}_{{ $perm->id }}"
                                                        {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="p_{{ $role->id }}_{{ $perm->id }}">
                                                        <strong>{{ $perm->display_name }}</strong>
                                                        <small class="text-muted d-block font-monospace" style="font-size: 11px;">{{ $perm->name }}</small>
                                                    </label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-save me-1"></i> Save {{ $role->display_name }} Permissions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="background: #1B365D; color: #fff;">
                    <h5 class="modal-title text-white fw-bold"><i class="fas fa-shield-alt me-2"></i>Create New System Role</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role Key / Identifier <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. accountant, data_entry" required>
                        <small class="text-muted">Unique lowercase identifier (e.g. accountant)</small>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" class="form-control" placeholder="e.g. Accountant" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Hindi Name</label>
                            <input type="text" name="display_name_hindi" class="form-control" placeholder="e.g. लेखापाल">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Responsibilities of this role..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
