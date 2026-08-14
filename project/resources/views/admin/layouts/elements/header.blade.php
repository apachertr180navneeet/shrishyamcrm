@php
    $authUser = Auth::user();
    $avatarUrl = (!empty($authUser?->avatar) && file_exists(public_path($authUser->avatar)))
        ? asset($authUser->avatar)
        : asset('assets/admin/img/avatars/1.png');
    $userName = $authUser->full_name ?? ($authUser->first_name ?? 'Admin User');
    $userRole = ucfirst($authUser->role ?? 'Admin');
    $userEmail = $authUser->email ?? '';
@endphp

<nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
	id="layout-navbar">
	<div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
		<a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
			<i class="bx bx-menu bx-sm"></i>
		</a>
	</div>

	<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
		<!-- Left info / Date & Time -->
		<div class="navbar-nav align-items-center">
			<div class="nav-item d-flex align-items-center text-secondary">
				<i class="bx bx-calendar-event fs-4 text-primary me-2"></i>
				<span class="fw-semibold text-body">{{ date('l, d M Y') }}</span>
			</div>
		</div>
		<!-- /Left info -->

		<ul class="navbar-nav flex-row align-items-center ms-auto">
			<!-- User Dropdown -->
			<li class="nav-item navbar-dropdown dropdown-user dropdown">
				<a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center" href="javascript:void(0);"
					data-bs-toggle="dropdown" aria-expanded="false">
					<div class="avatar avatar-online me-2">
						<img src="{{ $avatarUrl }}" alt="{{ $userName }}" class="w-px-40 h-auto rounded-circle" />
					</div>
					<div class="d-none d-md-block text-start me-1">
						<span class="fw-semibold d-block lh-1 text-heading">{{ $userName }}</span>
						<small class="text-muted text-capitalize">{{ $userRole }}</small>
					</div>
					<i class="bx bx-chevron-down d-none d-md-block ms-1 text-muted"></i>
				</a>
				<ul class="dropdown-menu dropdown-menu-end shadow-sm">
					<li>
						<a class="dropdown-item py-2" href="{{ route('admin.profile') }}">
							<div class="d-flex align-items-center">
								<div class="flex-shrink-0 me-3">
									<div class="avatar avatar-online">
										<img src="{{ $avatarUrl }}" alt="{{ $userName }}" class="w-px-40 h-auto rounded-circle">
									</div>
								</div>
								<div class="flex-grow-1">
									<h6 class="mb-0 fw-semibold">{{ $userName }}</h6>
									<div class="d-flex align-items-center gap-1 mt-1">
										<span class="badge bg-label-primary px-2 py-1">{{ $userRole }}</span>
										@if(!empty($userEmail))
											<small class="text-muted text-truncate" style="max-width: 130px;">{{ $userEmail }}</small>
										@endif
									</div>
								</div>
							</div>
						</a>
					</li>
					<li>
						<div class="dropdown-divider my-1"></div>
					</li>
					<li>
						<a class="dropdown-item" href="{{ route('admin.profile') }}">
							<i class="bx bx-user me-2 text-primary"></i>
							<span class="align-middle">My Profile</span>
						</a>
					</li>
					<li>
						<a class="dropdown-item" href="{{ route('admin.change.password') }}">
							<i class="bx bx-key me-2 text-warning"></i>
							<span class="align-middle">Change Password</span>
						</a>
					</li>
					<li>
						<div class="dropdown-divider my-1"></div>
					</li>
					<li>
						<a class="dropdown-item text-danger" href="{{ route('admin.logout') }}">
							<i class="bx bx-power-off me-2 text-danger"></i>
							<span class="align-middle fw-medium">Log Out</span>
						</a>
					</li>
				</ul>
			</li>
			<!--/ User Dropdown -->
		</ul>
	</div>
</nav>