@php
    $authUser = Auth::user();
    $avatarUrl = (!empty($authUser?->avatar) && file_exists(public_path($authUser->avatar)))
        ? asset($authUser->avatar)
        : asset('assets/admin/img/avatars/1.png');
    $userName = $authUser->full_name ?? ($authUser->first_name ?? 'Shri Navneet Sharma');
    $userRole = ucfirst($authUser->role ?? 'Super Admin');
    $userEmail = $authUser->email ?? 'admin@shrishyamwelfare.org';
@endphp

<nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
	id="layout-navbar">
	<div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
		<a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
			<i class="bx bx-menu bx-sm"></i>
		</a>
	</div>

	<div class="navbar-nav-right d-flex align-items-center justify-content-between w-100" id="navbar-collapse">
		<!-- Left: Global Search -->
		<div class="navbar-nav align-items-center">
			<div class="nav-item d-flex align-items-center">
				<i class="bx bx-search fs-4 lh-0 me-2 text-muted"></i>
				<input type="text" class="form-control border-0 shadow-none bg-transparent" placeholder="Global search (Name, Member No, Receipt...)" style="max-width: 320px;" onkeyup="if(event.key==='Enter') window.location.href='{{ route('admin.members.index') }}?search=' + encodeURIComponent(this.value)">
			</div>
		</div>

		<!-- Right: Tools, Switcher & User Dropdown -->
		<ul class="navbar-nav flex-row align-items-center ms-auto gap-2">
			<!-- Date Badge -->
			<li class="nav-item d-none d-lg-block">
				<div class="nav-link px-2 d-flex align-items-center text-secondary">
					<i class="bx bx-calendar-event fs-5 text-primary me-1"></i>
					<span class="fw-semibold small text-body">{{ date('l, d M Y') }}</span>
				</div>
			</li>

			<!-- Bilingual Toggle Button -->
			<li class="nav-item">
				<a href="{{ route('lang.switch', app()->getLocale() === 'hi' ? 'en' : 'hi') }}" class="btn btn-sm btn-outline-primary px-2 py-1 d-flex align-items-center gap-1 shadow-sm" title="Switch Language">
					<i class="fas fa-globe"></i>
					<span class="fw-semibold" style="font-size: 12px;">{{ app()->getLocale() === 'hi' ? 'English' : 'हिंदी' }}</span>
				</a>
			</li>


			<!-- User Profile Dropdown -->
			<li class="nav-item navbar-dropdown dropdown-user dropdown ms-1">
				<a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center p-0" href="javascript:void(0);"
					data-bs-toggle="dropdown" aria-expanded="false">
					<div class="avatar avatar-online me-2">
						<img src="{{ $avatarUrl }}" alt="{{ $userName }}" class="w-px-40 h-auto rounded-circle" />
					</div>
					<div class="d-none d-xl-block text-start me-1">
						<span class="fw-semibold d-block lh-1 text-heading" style="font-size: 13px;">{{ $userName }}</span>
						<span class="badge bg-label-primary px-1 py-0 text-capitalize" id="displayRoleBadge" style="font-size: 10px;">{{ $userRole }}</span>
					</div>
					<i class="bx bx-chevron-down d-none d-xl-block ms-1 text-muted"></i>
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
		</ul>
	</div>
</nav>

<!-- Agent Mode Banner -->
<div id="agentViewBanner" class="alert alert-warning border-0 rounded-0 py-2 px-4 mb-0 d-none d-flex align-items-center justify-content-between shadow-sm">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-user-shield fs-5 text-warning"></i>
        <strong data-i18n="agentViewBanner">एजेंट मोड: केवल आपके आवंटित सदस्य (रामेश्वर लाल शर्मा - AGT-001) प्रदर्शित हो रहे हैं</strong>
    </div>
    <span class="badge bg-warning text-dark">AGT-001 ACTIVE</span>
</div>