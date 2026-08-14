<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
	<div class="app-brand demo py-3" style="height: auto; min-height: 75px;">
		<a href="{{ route('admin.dashboard') }}" class="app-brand-link d-flex align-items-center gap-2">
			<span class="app-brand-logo demo">
				<img src="{{ asset('assets/logo.svg') }}" alt="Shri Shyam Welfare Society Logo" style="width: 38px; height: 38px; object-fit: contain;">
			</span>
			<div class="d-flex flex-column text-start">
				<span class="app-brand-text fw-bold text-heading fs-6 lh-1 mb-1" style="font-family: 'Hind', sans-serif;">श्री श्याम वेलफेयर सोसायटी</span>
				<small class="text-muted fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Welfare Society ERP</small>
			</div>
		</a>

		<a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
			<i class="bx bx-chevron-left bx-sm align-middle"></i>
		</a>
	</div>

	<div class="menu-inner-shadow"></div>

	<ul class="menu-inner py-2">
		<!-- Main Menu -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">Main Menu</span>
		</li>
		<li class="menu-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
			<a href="{{ route('admin.dashboard') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-chart-pie"></i>
				<div>Dashboard</div>
			</a>
		</li>

		<!-- Master Records -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">Master Records</span>
		</li>
		<li class="menu-item {{ request()->is('admin/schemes*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-hand-holding-heart"></i>
				<div>Schemes Master</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/age-slabs*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-sliders-h"></i>
				<div>Age Slabs</div>
			</a>
		</li>

		<!-- Member Enrolment -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">Member Enrolment</span>
		</li>
		<li class="menu-item {{ request()->is('admin/members*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-users"></i>
				<div>All Members</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/add-member*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-user-plus"></i>
				<div>Add Member</div>
			</a>
		</li>

		<!-- Agent Network -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">Agent Network</span>
		</li>
		<li class="menu-item {{ request()->is('admin/agents*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-user-tie"></i>
				<div>All Agents</div>
			</a>
		</li>

		<!-- Collections & Accounting -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">Collections & Accounting</span>
		</li>
		<li class="menu-item {{ request()->is('admin/payment-entry*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-cash-register"></i>
				<div>Payment Entry</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/payments*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-history"></i>
				<div>Payment History</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/receipts*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-receipt"></i>
				<div>Receipts</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/ledger*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-book-open"></i>
				<div>Financial Ledgers</div>
			</a>
		</li>

		<!-- Certificates & Events -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">Certificates & Events</span>
		</li>
		<li class="menu-item {{ request()->is('admin/certificates*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-certificate"></i>
				<div>Certificates</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/events*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-calendar-alt"></i>
				<div>Marriage Events</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/payouts*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-hand-holding-usd"></i>
				<div>Beneficiary Payouts</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/whatsapp*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fab fa-whatsapp"></i>
				<div>WhatsApp Center</div>
			</a>
		</li>

		<!-- Reports & Admin -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">Reports & Admin</span>
		</li>
		<li class="menu-item {{ request()->is('admin/reports*') ? 'active' : '' }}">
			<a href="javascript:void(0);" class="menu-link">
				<i class="menu-icon tf-icons fas fa-file-alt"></i>
				<div>Reports Center</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/profile') ? 'active' : '' }}">
			<a href="{{ route('admin.profile') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-user-shield"></i>
				<div>Users & Roles</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/change-password') ? 'active' : '' }}">
			<a href="{{ route('admin.change.password') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-cog"></i>
				<div>Settings</div>
			</a>
		</li>
	</ul>
</aside>