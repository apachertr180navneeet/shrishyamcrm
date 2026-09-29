@extends('admin.layouts.app')

@section('style')
<style>
    .kpi-card {
        border-radius: 12px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
    }
    .status-tab-btn {
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 18px;
        transition: all 0.2s ease;
    }
    .status-tab-btn.active {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .table-hover tbody tr:hover {
        background-color: rgba(27, 54, 93, 0.03);
    }
    .badge-slab {
        background-color: #EBF3FA;
        color: #1B365D;
        font-weight: 600;
        border: 1px solid #D0E1F3;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header Card -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1B365D 0%, #2A4D80 100%); color: #fff;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge bg-light text-primary px-3 py-1 fw-bold font-monospace">{{ $event->event_code }}</span>
                        <span class="badge bg-warning text-dark fw-semibold">
                            <i class="fas fa-layer-group me-1"></i> {{ $event->scheme ? $event->scheme->name_hindi : 'All Schemes' }}
                        </span>
                        <span class="badge bg-{{ $event->status === 'Completed' ? 'success' : ($event->status === 'Active' ? 'info' : 'secondary') }}">
                            {{ $event->status }}
                        </span>
                    </div>
                    <h3 class="fw-bold mb-1 text-white" style="font-family: 'Hind', sans-serif;">
                        <i class="fas fa-list-check text-warning me-2"></i>{{ $event->title }} - अंशदान मास्टर लिस्ट
                    </h3>
                    <p class="text-white-50 mb-0 small">
                        <span class="text-white"><i class="fas fa-calendar-alt text-warning me-1"></i> <strong>{{ $event->event_date ? $event->event_date->format('d M Y') : 'N/A' }}</strong></span> &nbsp;|&nbsp;
                        <span class="text-white"><i class="fas fa-female text-warning me-1"></i> कन्या/लाभार्थी: <strong>{{ $event->girl_name }}</strong> {{ $event->father_name ? '(पिता: ' . $event->father_name . ')' : '' }}</span> &nbsp;|&nbsp;
                        <span class="text-white"><i class="fas fa-map-marker-alt text-danger me-1"></i> स्थल: <strong>{{ $event->venue }}</strong></span>
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.events.contributions.print', ['id' => $event->id] + request()->all()) }}" target="_blank" class="btn btn-light shadow-sm text-dark fw-semibold">
                        <i class="fas fa-print me-1 text-primary"></i> प्रिंट मास्टर लिस्ट (Print)
                    </a>
                    <a href="{{ route('admin.events.contributions.export', ['id' => $event->id] + request()->all()) }}" class="btn btn-success shadow-sm fw-semibold">
                        <i class="fas fa-file-excel me-1"></i> एक्सेल डाउनलोड (Excel/CSV)
                    </a>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i> Back to Events
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Members -->
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm kpi-card h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted d-block fw-semibold">कुल अपेक्षित सदस्य (Members)</small>
                            <h3 class="fw-bold mb-0 text-primary">{{ $stats['total_members'] }}</h3>
                            <small class="text-muted">{{ $event->scheme ? $event->scheme->name_hindi : 'समस्त योजनाएं' }}</small>
                        </div>
                        <div class="avatar avatar-md bg-label-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-users fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Expected -->
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm kpi-card h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted d-block fw-semibold">कुल अपेक्षित राशि (Expected Total)</small>
                            <h3 class="fw-bold mb-0 text-dark">₹{{ number_format($stats['total_expected'], 2) }}</h3>
                            <small class="text-muted">सदस्य स्लैब नियमानुसार</small>
                        </div>
                        <div class="avatar avatar-md bg-label-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-calculator fs-4 text-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Collected (Paid) -->
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm kpi-card h-100" style="background: #F0FDF4; border-left: 4px solid #16A34A !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-success d-block fw-bold"><i class="fas fa-check-circle me-1"></i> प्राप्त कलेक्शन (Collected)</small>
                            <h3 class="fw-bold mb-0 text-success">₹{{ number_format($stats['total_collected'], 2) }}</h3>
                            <small class="text-success fw-semibold">{{ $stats['paid_count'] }} सदस्य जमा ({{ $stats['collection_percentage'] }}%)</small>
                        </div>
                        <div class="avatar avatar-md bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-check-double fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Total Pending (Due) -->
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm kpi-card h-100" style="background: #FEF2F2; border-left: 4px solid #DC2626 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-danger d-block fw-bold"><i class="fas fa-clock me-1"></i> शेष बकाया (Pending Due)</small>
                            <h3 class="fw-bold mb-0 text-danger">₹{{ number_format($stats['total_pending'], 2) }}</h3>
                            <small class="text-danger fw-semibold">{{ $stats['pending_count'] }} सदस्य शेष ({{ 100 - $stats['collection_percentage'] }}%)</small>
                        </div>
                        <div class="avatar avatar-md bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-hourglass-half fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-semibold text-dark">
                    <i class="fas fa-chart-pie text-primary me-1"></i> कलेक्शन प्रगति (Collection Progress): <strong>{{ $stats['collection_percentage'] }}% पूर्ण</strong>
                </span>
                <span class="small text-muted">
                    प्राप्त: <strong class="text-success">{{ $stats['paid_count'] }}</strong> &nbsp;|&nbsp;
                    बकाया: <strong class="text-danger">{{ $stats['pending_count'] }}</strong> / कुल: <strong>{{ $stats['total_members'] }}</strong>
                </span>
            </div>
            <div class="progress" style="height: 10px; border-radius: 5px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['collection_percentage'] }}%;" aria-valuenow="{{ $stats['collection_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                <div class="progress-bar bg-danger opacity-75" role="progressbar" style="width: {{ 100 - $stats['collection_percentage'] }}%;" aria-valuenow="{{ 100 - $stats['collection_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <!-- Master List Navigation Tabs & Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <!-- Status Switcher Tabs -->
                @php
                    $currentStatus = request('status');
                @endphp
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.events.contributions', array_merge(['id' => $event->id], request()->except('status', 'page'))) }}"
                       class="btn status-tab-btn {{ empty($currentStatus) ? 'btn-dark active' : 'btn-outline-secondary' }}">
                        <i class="fas fa-users me-1"></i> सभी सदस्य (All: {{ $stats['total_members'] }})
                        <span class="badge bg-light text-dark ms-1">₹{{ number_format($stats['total_expected'], 0) }}</span>
                    </a>

                    <a href="{{ route('admin.events.contributions', array_merge(['id' => $event->id, 'status' => 'Paid'], request()->except('page', 'status'))) }}"
                       class="btn status-tab-btn {{ $currentStatus === 'Paid' ? 'btn-success active text-white' : 'btn-outline-success' }}">
                        <i class="fas fa-check-circle me-1"></i> प्राप्त कलेक्शन (Paid: {{ $stats['paid_count'] }})
                        <span class="badge bg-white text-success ms-1">₹{{ number_format($stats['total_collected'], 0) }}</span>
                    </a>

                    <a href="{{ route('admin.events.contributions', array_merge(['id' => $event->id, 'status' => 'Pending'], request()->except('page', 'status'))) }}"
                       class="btn status-tab-btn {{ $currentStatus === 'Pending' ? 'btn-danger active text-white' : 'btn-outline-danger' }}">
                        <i class="fas fa-clock me-1"></i> शेष बकाया (Pending: {{ $stats['pending_count'] }})
                        <span class="badge bg-white text-danger ms-1">₹{{ number_format($stats['total_pending'], 0) }}</span>
                    </a>
                </div>

                @if($stats['pending_count'] > 0)
                <div>
                    <span class="badge bg-label-danger py-2 px-3 fw-semibold">
                        <i class="fas fa-exclamation-triangle me-1"></i> {{ $stats['pending_count'] }} सदस्यों से ₹{{ number_format($stats['total_pending'], 2) }} वसूलना शेष है
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card-body p-3 bg-light border-bottom">
            <form action="{{ route('admin.events.contributions', $event->id) }}" method="GET" class="row g-2 align-items-center">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="col-lg-5 col-md-5 col-12">
                    <div class="input-group shadow-xs">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search member name, membership no, mobile, receipt no..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                    <select name="agent_id" class="form-select shadow-xs" onchange="this.form.submit()">
                        <option value="">-- All Agents / समस्त कार्यकर्ता --</option>
                        @foreach($agents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>
                            {{ $ag->name }} ({{ $ag->agent_code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                    <select name="per_page" class="form-select shadow-xs" onchange="this.form.submit()">
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 Records</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 Records</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 Records</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All (सम्पूर्ण सूची)</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-12 col-sm-12 col-6 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request('search') || request('agent_id') || request('status') || request('per_page'))
                    <a href="{{ route('admin.events.contributions', $event->id) }}" class="btn btn-outline-secondary" title="Reset Filters">
                        <i class="fas fa-times"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Master List Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0 fw-bold" style="font-family: 'Hind', sans-serif;">
                <i class="fas fa-clipboard-list text-primary me-2"></i>
                @if(request('status') === 'Paid')
                    प्राप्त अंशदान सूची (Received Collections List)
                @elseif(request('status') === 'Pending')
                    शेष बकाया अंशदान सूची (Pending Dues List)
                @else
                    सदस्य अंशदान मास्टर लिस्ट (Complete Member Contribution List)
                @endif
            </h5>
            <span class="badge bg-label-primary fs-6 px-3 py-1">
                कुल रिकॉर्ड: {{ $contributions->total() }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="width: 45px;">#</th>
                        <th>सदस्य विवरण (Member Info)</th>
                        <th>आयु एवं वर्ग (Age & Slab)</th>
                        <th>अधिकृत कार्यकर्ता (Agent)</th>
                        <th class="text-end">निर्धारित अंशदान</th>
                        <th class="text-center">कलेक्शन स्थिति</th>
                        <th class="text-end">प्राप्त राशि</th>
                        <th>रसीद क्र. व दिनांक</th>
                        <th class="text-end" style="min-width: 170px;">कार्यवाही (Actions)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contributions as $index => $c)
                    @php
                        $member = $c->member;
                        $isPaid = $c->payment_status === 'Paid';
                        $agent = $c->agent ?? ($member ? $member->agent : null);

                        // Build WhatsApp reminder message for pending member
                        $waPendingText = "जय श्री श्याम 🙏\nश्री श्याम वेलफेयर सोसायटी, लोहीकी\n\nप्रिय सदस्य: " . $c->member_name . " (" . ($member ? $member->membership_no : 'N/A') . ")\n\n📢 कार्यक्रम सूचना: " . $event->title . " (" . $event->event_code . ")\nदिनांक: " . ($event->event_date ? $event->event_date->format('d/m/Y') : 'N/A') . "\nस्थल: " . $event->venue . "\n\n📊 अंशदान विवरण:\n• निर्धारित सहयोग राशि: ₹" . number_format($c->contribution_amount, 2) . "\n• स्थिति: शेष बकाया (Pending)\n\nकृपया अपनी सहयोग राशि समय पर अधिकृत कार्यकर्ता (" . ($agent ? $agent->name . ' - मो. ' . $agent->mobile : 'सोसायटी कार्यालय') . ") के पास जमा करवाकर रसीद प्राप्त करें।\n\nजय श्री श्याम 🙏";

                        // Build WhatsApp thank you message for paid member
                        $waPaidText = "जय श्री श्याम 🙏\nश्री श्याम वेलफेयर सोसायटी, लोहीकी\n\nप्रिय सदस्य: " . $c->member_name . " (" . ($member ? $member->membership_no : 'N/A') . ")\n\n✅ कार्यक्रम सहयोग रसीद पुष्टि\nकार्यक्रम: " . $event->title . " (" . $event->event_code . ")\n• जमा राशि: ₹" . number_format($c->contribution_amount, 2) . "\n• रसीद क्र.: " . ($c->receipt_no ?? 'N/A') . "\n• जमा दिनांक: " . ($c->payment_date ? $c->payment_date->format('d/m/Y') : date('d/m/Y')) . "\n\nआपके पुनीत सहयोग के लिए सोसायटी आपका हार्दिक आभार व्यक्त करती है।\nजय श्री श्याम 🙏";

                        $cleanMobile = preg_replace('/[^0-9]/', '', $member ? $member->mobile : '');
                        if (strlen($cleanMobile) === 10) {
                            $cleanMobile = '91' . $cleanMobile;
                        }
                        $waPendingUrl = $cleanMobile ? "https://api.whatsapp.com/send?phone={$cleanMobile}&text=" . urlencode($waPendingText) : '#';
                        $waPaidUrl = $cleanMobile ? "https://api.whatsapp.com/send?phone={$cleanMobile}&text=" . urlencode($waPaidText) : '#';
                    @endphp
                    <tr class="{{ $isPaid ? 'table-success-subtle' : '' }}">
                        <td class="text-muted fw-bold">{{ $contributions->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-sm {{ $isPaid ? 'bg-label-success' : 'bg-label-warning' }} rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="fas {{ $isPaid ? 'fa-user-check' : 'fa-user-clock' }} fs-6"></i>
                                </div>
                                <div>
                                    <strong class="text-dark d-block" style="font-size: 0.95rem;">{{ $c->member_name }}</strong>
                                    <small class="text-muted d-block">
                                        <span class="badge bg-label-secondary font-monospace">{{ $member ? $member->membership_no : 'N/A' }}</span>
                                        @if($member && $member->father_spouse_name)
                                            • पि/प: {{ $member->father_spouse_name }}
                                        @endif
                                        @if($member && $member->mobile)
                                            • <i class="fas fa-phone-alt text-primary ms-1"></i> {{ $member->mobile }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark fw-bold">{{ $c->member_age }} वर्ष</span>
                            <span class="badge badge-slab d-block mt-1">{{ $c->age_slab ?: 'General Slab' }}</span>
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">{{ $c->scheme ? $c->scheme->name_hindi : ($member && $member->scheme ? $member->scheme->name_hindi : 'Welfare') }}</small>
                        </td>
                        <td>
                            @if($agent)
                                <strong class="text-dark d-block" style="font-size: 0.85rem;">{{ $agent->name }}</strong>
                                <small class="text-muted font-monospace">{{ $agent->agent_code }}</small>
                                @if($agent->mobile)
                                <small class="text-muted d-block"><i class="fas fa-phone text-success me-1"></i>{{ $agent->mobile }}</small>
                                @endif
                            @else
                                <span class="badge bg-label-secondary">HQ Direct</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-dark fs-6">₹{{ number_format($c->contribution_amount, 2) }}</span>
                        </td>
                        <td class="text-center">
                            @if($isPaid)
                                <span class="badge bg-success px-3 py-1 shadow-xs">
                                    <i class="fas fa-check-circle me-1"></i> प्राप्त (Paid)
                                </span>
                            @else
                                <span class="badge bg-danger px-3 py-1 shadow-xs">
                                    <i class="fas fa-hourglass-half me-1"></i> बकाया (Pending)
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($isPaid)
                                <span class="fw-bold text-success fs-6">₹{{ number_format($c->contribution_amount, 2) }}</span>
                            @else
                                <span class="text-muted">₹0.00</span>
                            @endif
                        </td>
                        <td>
                            @if($isPaid)
                                @if($c->payment_id)
                                    <a href="{{ route('admin.payments.receipt', $c->payment_id) }}" class="fw-bold text-primary font-monospace d-block" target="_blank" title="View Receipt">
                                        <i class="fas fa-receipt me-1"></i>{{ $c->receipt_no ?: 'RC-' . $c->payment_id }}
                                    </a>
                                @else
                                    <strong class="text-primary font-monospace">{{ $c->receipt_no ?: '-' }}</strong>
                                @endif
                                <small class="text-muted d-block">
                                    <i class="fas fa-calendar-check me-1 text-success"></i>{{ $c->payment_date ? $c->payment_date->format('d/m/Y') : '-' }}
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center gap-1">
                                @if(!$isPaid)
                                    <!-- 1-Click Collect Cash Modal Trigger -->
                                    <button type="button" class="btn btn-sm btn-success fw-bold shadow-xs px-2"
                                            onclick="openQuickCollectModal({{ $c->id }}, '{{ addslashes($c->member_name) }}', '{{ $member ? $member->membership_no : '' }}', {{ $c->contribution_amount }}, {{ $event->id }}, '{{ addslashes($event->title) }}')"
                                            title="जमा दर्ज करें (Collect Payment)">
                                        <i class="fas fa-cash-register me-1"></i> जमा करें
                                    </button>

                                    <!-- WhatsApp Reminder Link -->
                                    @if($cleanMobile)
                                    <a href="{{ $waPendingUrl }}" target="_blank" class="btn btn-sm btn-outline-success px-2" title="Send WhatsApp Payment Reminder">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                    @endif
                                @else
                                    <!-- View Receipt -->
                                    @if($c->payment_id)
                                    <a href="{{ route('admin.payments.receipt', $c->payment_id) }}" target="_blank" class="btn btn-sm btn-outline-primary px-2" title="Official Receipt">
                                        <i class="fas fa-receipt me-1"></i> रसीद
                                    </a>
                                    @endif

                                    <!-- WhatsApp Receipt Share -->
                                    @if($cleanMobile)
                                    <a href="{{ $waPaidUrl }}" target="_blank" class="btn btn-sm btn-outline-success px-2" title="Share Receipt Confirmation on WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                    @endif
                                @endif

                                <!-- Member Ledger Card -->
                                @if($member)
                                <a href="{{ route('admin.ledger.index', ['member_id' => $member->id]) }}" class="btn btn-sm btn-outline-secondary px-2" title="View Member Ledger Card" target="_blank">
                                    <i class="fas fa-book"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-users-slash fs-1 text-secondary d-block mb-3"></i>
                            <h6 class="fw-bold">कोई रिकॉर्ड नहीं मिला (No Records Found)</h6>
                            <p class="small text-muted mb-0">चयनित फ़िल्टर के अनुसार इस कार्यक्रम में कोई सदस्य अंशदान रिकॉर्ड उपलब्ध नहीं है।</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contributions->hasPages())
        <div class="card-footer py-3 border-top bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    Showing {{ $contributions->firstItem() }} to {{ $contributions->lastItem() }} of {{ $contributions->total() }} entries
                </small>
                <div>
                    {{ $contributions->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Quick Payment Collection Modal -->
<div class="modal fade" id="quickCollectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="event_id" value="{{ $event->id }}">
                <input type="hidden" name="event_contribution_id" id="qcContributionId">
                <input type="hidden" name="member_id" id="qcMemberId">
                <input type="hidden" name="payment_type" value="Event Contribution">

                <div class="modal-header bg-success text-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-hand-holding-usd fs-4"></i>
                        <div>
                            <h5 class="modal-title text-white fw-bold mb-0">अंशदान राशि जमा करें (Collect Contribution)</h5>
                            <small class="text-white-50" id="qcEventTitleSubtitle">{{ $event->title }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <!-- Member Info Alert -->
                    <div class="card border-0 bg-white shadow-xs p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">सदस्य का नाम (Member Name)</small>
                                <strong class="fs-6 text-dark" id="qcMemberNameDisplay">-</strong>
                            </div>
                            <span class="badge bg-label-primary font-monospace fs-6" id="qcMembershipNoDisplay">-</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">जमा राशि (Amount ₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white fw-bold">₹</span>
                                <input type="number" step="0.01" min="1" name="amount" id="qcAmountField" class="form-control form-control-lg fw-bold text-success" required>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold">भुगतान माध्यम (Mode) <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-select form-select-lg" required>
                                <option value="Cash" selected>Cash (नकद)</option>
                                <option value="UPI">UPI / QR Code</option>
                                <option value="Bank Transfer">Bank Transfer / NEFT</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold">दिनांक (Payment Date) <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control form-control-lg" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Reference / Transaction No (यदि कोई हो)</label>
                            <input type="text" name="reference_no" class="form-control" placeholder="UPI Ref, UTR, Cheque No...">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Remarks (टिप्पणी)</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional payment note..." value="अंशदान - {{ $event->title }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg px-4 fw-bold shadow">
                        <i class="fas fa-check-circle me-1"></i> पुष्टि करें एवं रसीद बनाएं
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function openQuickCollectModal(contributionId, memberName, membershipNo, amount, eventId, eventTitle) {
    document.getElementById('qcContributionId').value = contributionId;
    document.getElementById('qcMemberNameDisplay').innerText = memberName;
    document.getElementById('qcMembershipNoDisplay').innerText = membershipNo || 'N/A';
    document.getElementById('qcAmountField').value = amount;
    
    // Also find member_id if linked
    @php
        $memberMap = [];
        foreach($contributions as $c) {
            $memberMap[$c->id] = $c->member_id;
        }
    @endphp
    const memberMap = @json($memberMap);
    if (memberMap[contributionId]) {
        document.getElementById('qcMemberId').value = memberMap[contributionId];
    }

    const modal = new bootstrap.Modal(document.getElementById('quickCollectModal'));
    modal.show();
}
</script>
@endsection
