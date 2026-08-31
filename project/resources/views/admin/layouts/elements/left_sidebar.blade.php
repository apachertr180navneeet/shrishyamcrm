<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
	<div class="app-brand demo py-3" style="height: auto; min-height: 75px;">
		<a href="{{ route('admin.dashboard') }}" class="app-brand-link d-flex align-items-center gap-2">
			<span class="app-brand-logo demo">
				<img src="{{ asset('assets/logo.svg') }}" alt="Shri Shyam Welfare Society Logo" style="width: 38px; height: 38px; object-fit: contain;">
			</span>
			<div class="d-flex flex-column text-start">
				<span class="app-brand-text fw-bold text-heading fs-6 lh-1 mb-1" style="font-family: 'Hind', sans-serif;">{{ __('erp.app_name_hindi') }}</span>
				<small class="text-muted fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">{{ __('erp.welfare_society_erp') }}</small>
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
			<span class="menu-header-text">{{ __('erp.main_menu') }}</span>
		</li>
		<li class="menu-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
			<a href="{{ route('admin.dashboard') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-chart-pie"></i>
				<div>{{ __('erp.dashboard') }}</div>
			</a>
		</li>

		<!-- Master Records -->
		@if(auth()->check() && auth()->user()->hasRole(['admin', 'super_admin']))
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">{{ __('erp.master_records') }}</span>
		</li>
		<li class="menu-item {{ request()->is('admin/schemes*') && !request()->is('admin/age-slabs*') ? 'active' : '' }}">
			<a href="{{ route('admin.schemes.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-hand-holding-heart"></i>
				<div>{{ __('erp.schemes_master') }}</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/age-slabs*') ? 'active' : '' }}">
			<a href="{{ route('admin.schemes.age-slabs') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-sliders-h"></i>
				<div>{{ __('erp.age_slabs') }}</div>
			</a>
		</li>

		<!-- Agent Network -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">{{ __('erp.agent_network') }}</span>
		</li>
		<li class="menu-item {{ request()->is('admin/agents*') ? 'active' : '' }}">
			<a href="{{ route('admin.agents.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-user-tie"></i>
				<div>{{ __('erp.all_agents') }}</div>
			</a>
		</li>
		@endif

		<!-- Member Enrolment -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">{{ __('erp.member_enrolment') }}</span>
		</li>
		<li class="menu-item {{ request()->is('admin/members') || (request()->is('admin/members/*') && !request()->is('admin/members/create')) ? 'active' : '' }}">
			<a href="{{ route('admin.members.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-users"></i>
				<div>{{ __('erp.all_members') }}</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/members/create') ? 'active' : '' }}">
			<a href="{{ route('admin.members.create') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-user-plus"></i>
				<div>{{ __('erp.add_member') }}</div>
			</a>
		</li>

		@if(auth()->check() && auth()->user()->isAgent() && auth()->user()->agent_id)
		<!-- Agent Profile -->
		<li class="menu-item {{ request()->is('admin/agents/' . auth()->user()->agent_id) ? 'active' : '' }}">
			<a href="{{ route('admin.agents.show', auth()->user()->agent_id) }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-id-badge"></i>
				<div>{{ __('erp.my_profile') }}</div>
			</a>
		</li>
		@endif

		<!-- Collections & Accounting -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">{{ __('erp.collections_accounting') }}</span>
		</li>
		<li class="menu-item {{ request()->is('admin/payment-entry*') ? 'active' : '' }}">
			<a href="{{ route('admin.payments.create') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-cash-register"></i>
				<div>{{ __('erp.payment_entry') }}</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/payments') ? 'active' : '' }}">
			<a href="{{ route('admin.payments.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-history"></i>
				<div>{{ __('erp.payment_history') }}</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/receipts*') ? 'active' : '' }}">
			<a href="{{ route('admin.receipts.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-receipt"></i>
				<div>{{ __('erp.receipts') }}</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/ledger*') ? 'active' : '' }}">
			<a href="{{ route('admin.ledger.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-book-open"></i>
				<div>{{ __('erp.ledger') }}</div>
			</a>
		</li>

		<!-- Certificates & Events -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">{{ __('erp.certificates_events') }}</span>
		</li>
		<li class="menu-item {{ request()->is('admin/certificates*') ? 'active' : '' }}">
			<a href="{{ route('admin.certificates.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-certificate"></i>
				<div>{{ __('erp.certificates') }}</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/events*') ? 'active' : '' }}">
			<a href="{{ route('admin.events.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-calendar-alt"></i>
				<div>{{ __('erp.marriage_events') }}</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/payouts*') ? 'active' : '' }}">
			<a href="{{ route('admin.payouts.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fas fa-hand-holding-usd"></i>
				<div>{{ __('erp.payouts') }}</div>
			</a>
		</li>
		<li class="menu-item {{ request()->is('admin/whatsapp*') ? 'active' : '' }}">
			<a href="{{ route('admin.whatsapp.index') }}" class="menu-link">
				<i class="menu-icon tf-icons fab fa-whatsapp"></i>
				<div>{{ __('erp.whatsapp') }}</div>
			</a>
		</li>

		<!-- Reports & Admin -->
		<li class="menu-header small text-uppercase">
			<span class="menu-header-text">{{ __('erp.reports_system') }}</span>
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