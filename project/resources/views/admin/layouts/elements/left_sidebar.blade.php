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
			<span class="menu-header-text" data-i18n="mainMenu">Main Menu</span>
		</li>
		<li class="menu-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
			<a href="{{ route('admin.dashboard') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-chart-pie"></i>
				<div data-i18n="dashboard">Dashboard</div>
			</a>
		</li>

		<!-- Master Records -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text" data-i18n="masterRecords">Master Records</span>
		</li>
		<li class="menu-item {{ request()->is('admin/schemes*') && !request()->is('admin/age-slabs*') ? 'active' : '' }}">
			<a href="{{ route('admin.schemes.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-hand-holding-heart"></i>
				<div data-i18n="schemesMaster">Schemes Master</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/age-slabs*') ? 'active' : '' }}">
			<a href="{{ route('admin.schemes.age-slabs') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-sliders-h"></i>
				<div data-i18n="ageSlabs">Age Slabs</div>
			</a>
		</li>

		<!-- Member Enrolment -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text" data-i18n="memberEnrolment">Member Enrolment</span>
		</li>
		<li class="menu-item {{ request()->is('admin/members') || (request()->is('admin/members/*') && !request()->is('admin/members/create')) ? 'active' : '' }}">
			<a href="{{ route('admin.members.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-users"></i>
				<div data-i18n="allMembers">All Members</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/members/create') ? 'active' : '' }}">
			<a href="{{ route('admin.members.create') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-user-plus"></i>
				<div data-i18n="addMember">Add New Member</div>
			</a>
		</li>

		<!-- Agent Network -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text" data-i18n="agentNetwork">Agent Network</span>
		</li>
		<li class="menu-item {{ request()->is('admin/agents*') ? 'active' : '' }}">
			<a href="{{ route('admin.agents.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-user-tie"></i>
				<div data-i18n="allAgents">All Agents</div>
			</a>
		</li>

		<!-- Collections & Accounting -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text" data-i18n="collections">Collections & Accounting</span>
		</li>
		<li class="menu-item {{ request()->is('admin/payment-entry*') ? 'active' : '' }}">
			<a href="{{ route('admin.payments.create') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-cash-register"></i>
				<div data-i18n="paymentEntry">Payment Entry</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/payments') ? 'active' : '' }}">
			<a href="{{ route('admin.payments.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-history"></i>
				<div data-i18n="paymentList">Payment History</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/receipts*') ? 'active' : '' }}">
			<a href="{{ route('admin.receipts.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-receipt"></i>
				<div data-i18n="receipts">Receipts</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/ledger*') ? 'active' : '' }}">
			<a href="{{ route('admin.ledger.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-book-open"></i>
				<div data-i18n="ledger">Financial Ledgers</div>
			</a>
		</li>

		<!-- Certificates & Events -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text" data-i18n="certificatesEvents">Certificates & Events</span>
		</li>
		<li class="menu-item {{ request()->is('admin/certificates*') ? 'active' : '' }}">
			<a href="{{ route('admin.certificates.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-certificate"></i>
				<div data-i18n="certificates">Certificates</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/events*') ? 'active' : '' }}">
			<a href="{{ route('admin.events.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-calendar-alt"></i>
				<div data-i18n="events">Marriage Events</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/payouts*') ? 'active' : '' }}">
			<a href="{{ route('admin.payouts.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-hand-holding-usd"></i>
				<div data-i18n="payouts">Beneficiary Payouts</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/whatsapp*') ? 'active' : '' }}">
			<a href="{{ route('admin.whatsapp.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fab fa-whatsapp"></i>
				<div data-i18n="whatsapp">WhatsApp Center</div>
			</a>
		</li>

		<!-- Reports & Admin -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">{{ __('erp.reportsAdmin', [], 'en') ?: 'Reports & System' }}</span>
		</li>
		<li class="menu-item {{ request()->is('admin/reports*') ? 'active' : '' }}">
			<a href="{{ route('admin.reports.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-file-alt"></i>
				<div>{{ __('erp.reports') }}</div>
			</a>
		</li>
		@if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin'))
		<li class="menu-item {{ request()->is('admin/users*') ? 'active' : '' }}">
			<a href="{{ route('admin.users.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-user-shield"></i>
				<div>{{ __('erp.users') }}</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
			<a href="{{ route('admin.settings.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-cog"></i>
				<div>{{ __('erp.settings') }}</div>
			</a>
		</li>
		@endif
	</ul>
</aside>