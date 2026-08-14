@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1" style="color: #1B365D;"><i class="fas fa-users-cog me-2"></i>User & Role Management</h4>
                <p class="text-muted mb-0">Manage system users, assign role permissions and map agents</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal" style="background: #1B365D; border-color: #1B365D;">
                <i class="fas fa-user-plus me-1"></i> Add System User
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background: #f8fafc; color: #1B365D;">
                            <tr>
                                <th class="ps-4">User</th>
                                <th>Email / Phone</th>
                                <th>Assigned Role</th>
                                <th>Linked Agent</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $user->avatar_full_path }}" alt="Avatar" class="rounded-circle me-3" width="40" height="40">
                                        <div>
                                            <div class="fw-bold">{{ $user->full_name }}</div>
                                            <small class="text-muted">ID: #USR-{{ $user->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><i class="fas fa-envelope text-muted me-1 small"></i>{{ $user->email }}</div>
                                    <div><i class="fas fa-phone text-muted me-1 small"></i>{{ $user->phone ?: '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary px-2 py-1">
                                        {{ $user->roleModel ? $user->roleModel->display_name : ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $user->agent ? $user->agent->name . ' (' . $user->agent->agent_code . ')' : 'N/A' }}
                                </td>
                                <td>
                                    <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header" style="background: #1B365D; color: #fff;">
                                                <h5 class="modal-title">Edit User: {{ $user->full_name }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">First Name</label>
                                                        <input type="text" name="first_name" class="form-control" value="{{ $user->first_name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Last Name</label>
                                                        <input type="text" name="last_name" class="form-control" value="{{ $user->last_name }}" required>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">Phone</label>
                                                        <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" required>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">Role</label>
                                                        <select name="role_id" class="form-select" required>
                                                            @foreach($roles as $role)
                                                                <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                                                    {{ $role->display_name }} ({{ $role->name }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">Link Agent (Optional)</label>
                                                        <select name="agent_id" class="form-select">
                                                            <option value="">-- None --</option>
                                                            @foreach($agents as $agent)
                                                                <option value="{{ $agent->id }}" {{ $user->agent_id == $agent->id ? 'selected' : '' }}>
                                                                    {{ $agent->name }} ({{ $agent->agent_code }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Status</label>
                                                        <select name="status" class="form-select">
                                                            <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Password (leave blank to keep)</label>
                                                        <input type="password" name="password" class="form-control" placeholder="New Password">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary" style="background: #1B365D;">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="background: #1B365D; color: #fff;">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add System User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required placeholder="e.g. Ramesh">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required placeholder="e.g. Sharma">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="user@shrishyam.org">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required placeholder="9829012345">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Minimum 6 characters">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->display_name }} ({{ $role->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Link Agent (Optional)</label>
                            <select name="agent_id" class="form-select">
                                <option value="">-- None --</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }} ({{ $agent->agent_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: #1B365D;">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
