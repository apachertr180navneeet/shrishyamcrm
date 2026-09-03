@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">विवाह सहायता कार्यक्रम (Marriage Events & Support Pool)</h4>
                    <p class="text-muted mb-0">Manage girl child marriage welfare grants (₹51,000 assistance), event collections, and member contribution billing.</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#monthlyBroadcastModal">
                        <i class="fab fa-whatsapp me-1"></i> Common Message to Members
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventBillingModal">
                        <i class="fas fa-calculator me-1"></i> Bill Members for Event
                    </button>
                    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addEventModal">
                        <i class="fas fa-plus me-1"></i> Create Marriage Event
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center justify-content-between">
                <div><i class="fas fa-check-circle me-2"></i> {{ session('success') }}</div>
                @if(session('whatsapp_broadcast_url'))
                    <a href="{{ session('whatsapp_broadcast_url') }}" target="_blank" class="btn btn-sm btn-success ms-3 shadow-sm">
                        <i class="fab fa-whatsapp me-1"></i> Open WhatsApp Web Now
                    </a>
                @endif
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Events Cards Grid -->
    <div class="row g-4">
        @foreach($events as $event)
        <div class="col-lg-6 col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between bg-light">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-label-primary">{{ $event->event_code }}</span>
                            <span class="badge bg-info text-white">{{ $event->scheme ? $event->scheme->name_hindi : 'All Schemes' }}</span>
                        </div>
                        <h5 class="card-title mb-0 fw-bold">{{ $event->title }}</h5>
                    </div>
                    <span class="badge {{ $event->status == 'Completed' ? 'bg-success' : ($event->status == 'Active' ? 'bg-primary' : 'bg-warning') }}">
                        {{ $event->status }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Girl / Bride Name</small>
                            <strong class="fs-6 text-primary">{{ $event->girl_name }}</strong>
                        </div>
                        <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Father / Guardian</small>
                            <strong class="fs-6">{{ $event->father_name ?? ($event->member ? $event->member->full_name : 'N/A') }}</strong>
                        </div>
                        <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Marriage Date</small>
                            <strong><i class="fas fa-calendar-alt text-danger me-1"></i> {{ $event->event_date ? $event->event_date->format('d M Y') : '' }}</strong>
                        </div>
                        <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Assistance Grant Pool</small>
                            <strong class="fs-5 text-success">₹{{ number_format($event->target_amount) }}</strong>
                        </div>
                        <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Member Contribution Progress</small>
                            <span class="badge bg-label-success fw-bold fs-6">
                                ₹{{ number_format($event->total_collected_contribution) }} / ₹{{ number_format($event->total_expected_contribution) }}
                            </span>
                            <small class="text-muted d-block mt-1">
                                ({{ $event->paid_count }} of {{ $event->contributions->count() }} members paid)
                            </small>
                        </div>
                        <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Linked Member</small>
                            <span class="badge bg-label-secondary">{{ $event->member ? $event->member->full_name . ' (' . $event->member->membership_no . ')' : 'Direct Welfare' }}</span>
                        </div>
                    </div>

                    <div class="bg-lighter p-3 rounded mb-3">
                        <small class="text-muted d-block mb-1">Venue / विवाह स्थल</small>
                        <span class="text-dark"><i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $event->venue ?? 'Shri Shyam Dharamshala, Lohki' }}</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top flex-wrap gap-2">
                        <a href="{{ route('admin.events.contributions', $event->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-users-cog me-1"></i> View Contributions (अंशदान सूची)
                        </a>

                        @if($event->status != 'Completed' && auth()->check() && (auth()->user()->hasRole(['admin', 'super_admin']) || auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin'))
                        <a href="{{ route('admin.payouts.index') }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-hand-holding-usd me-1"></i> Disburse Grant
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <form action="{{ route('admin.events.store') }}" method="POST" id="createEventForm">
                @csrf
                <div class="modal-header" style="background: #1B365D; color: #fff;">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-hand-holding-heart me-2"></i>Create Marriage Welfare Event</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-lg-6 col-12">
                            <!-- Scheme Selection (Required) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold fs-6">
                                    <i class="fas fa-hand-holding-heart text-primary me-1"></i> Select Scheme (योजना चुनें) <span class="text-danger">*</span>
                                </label>
                                <select name="scheme_id" id="eventSchemeSelect" class="form-select form-select-lg shadow-sm" required onchange="onSchemeSelected(this.value)">
                                    <option value="">-- Choose Society Scheme --</option>
                                    @foreach($schemes as $sch)
                                    <option value="{{ $sch->id }}">{{ $sch->name_hindi }} ({{ $sch->name }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">योजना चुनते ही उसके सभी पात्र सदस्यों की सूची एवं आयु-वर्ग अनुसार अंशदान राशि नीचे प्रदर्शित होगी।</small>
                            </div>

                            <!-- Girl Name Dropdown -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-female text-danger me-1"></i> Select Girl / Bride (कन्या का नाम) <span class="text-danger">*</span>
                                </label>
                                <select id="girlDropdownSelect" class="form-select form-select-lg mb-2" onchange="onGirlSelectChange(this)">
                                    <option value="">-- Choose Registered Daughter / Member --</option>
                                    @foreach($girlsList as $g)
                                    <option value="{{ $g['girl_name'] }}"
                                        data-scheme-id="{{ $g['scheme_id'] ?? '' }}"
                                        data-father="{{ $g['father_name'] }}"
                                        data-member-id="{{ $g['member_id'] }}"
                                        data-member-name="{{ $g['member_name'] }}">
                                        {{ $g['label'] }}
                                    </option>
                                    @endforeach
                                    <option value="__custom__">➕ Other / Enter Name Manually (अन्य नाम दर्ज करें)</option>
                                </select>
                                <input type="text" name="girl_name" id="girlNameField" class="form-control" placeholder="Girl's Full Name (e.g. कुमारी पूजा शर्मा)" required>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Father / Guardian (पिता)</label>
                                    <input type="text" name="father_name" id="fatherNameField" class="form-control" placeholder="e.g. श्री राधेश्याम शर्मा">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Linked Member ID</label>
                                    <select name="member_id" id="linkedMemberSelect" class="form-select">
                                        <option value="">-- Direct Welfare --</option>
                                        @foreach($members as $mem)
                                        <option value="{{ $mem->id }}">{{ $mem->membership_no }} - {{ $mem->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Event Title (कार्यक्रम शीर्षक)</label>
                                <input type="text" name="title" id="eventTitleField" class="form-control" placeholder="e.g. कुमारी पूजा विवाह सहायता कार्यक्रम">
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Event Date (विवाह दिनांक) <span class="text-danger">*</span></label>
                                    <input type="date" name="event_date" id="eventDateField" class="form-control" required value="{{ date('Y-m-d') }}" onchange="triggerMemberPreview()">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Grant Pool (कन्यादान ₹)</label>
                                    <input type="number" name="target_amount" class="form-control fw-bold text-success" value="51000">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Venue (स्थल)</label>
                                <input type="text" name="venue" class="form-control" value="श्री श्याम धर्मशाला, लोहीकी">
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-semibold">Description / Notes</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Event notes..."></textarea>
                            </div>
                        </div>

                        <!-- Right Column: Live Scheme Members & Calculated Age-Slab Contribution Preview -->
                        <div class="col-lg-6 col-12">
                            <div class="card border bg-light h-100">
                                <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold text-primary">
                                        <i class="fas fa-calculator me-1"></i> पात्र सदस्य एवं स्वतः अंशदान गणना
                                    </h6>
                                    <span class="badge bg-success" id="previewCountBadge">0 Members</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="p-2 bg-lighter border-bottom text-muted small d-flex justify-content-between">
                                        <span>आयु अनुसार देय राशि (0-5: ₹100, 6-9: ₹200, 10-13: ₹300, 14-17: ₹400, 17+: ₹500)</span>
                                    </div>
                                    <div id="previewTableContainer" style="max-height: 380px; overflow-y: auto;">
                                        <div class="text-center py-5 text-muted">
                                            <i class="fas fa-hand-pointer fs-3 text-secondary mb-2 d-block"></i>
                                            कृपया बाईं ओर <strong>योजना (Scheme)</strong> चुनें।<br>
                                            सदस्यों की आयु अनुसार अंशदान की गणना यहाँ दिखेगी।
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">कुल अपेक्षित अंशदान (Total):</span>
                                    <strong class="fs-5 text-success" id="previewTotalAmount">₹0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-lg" style="background: #1B365D;">
                        <i class="fas fa-check-circle me-1"></i> Create Event & Generate Contributions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Monthly All-Events Common Message Modal ("All Event ka ek saath data jayega") -->
<div class="modal fade" id="monthlyBroadcastModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.events.broadcast-send') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="fab fa-whatsapp me-2"></i>माह के सभी कार्यक्रमों का साझा संदेश (Monthly Common Message)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        इस माह के सभी विवाह कार्यक्रमों का संपूर्ण डेटा एक साथ एकत्रित करके सदस्यों को एक ही संदेश में भेजा जाएगा।
                    </p>

                    <!-- Month Picker -->
                    <div class="row g-3 mb-3 align-items-center">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt text-primary me-1"></i> Month (माह चुनें) <span class="text-danger">*</span>
                            </label>
                            <input type="month" name="month" id="broadcastMonthInput" class="form-control form-control-lg" value="{{ date('Y-m') }}" onchange="fetchMonthEventsData(this.value)" required>
                        </div>
                        <div class="col-md-6 col-12 text-md-end">
                            <div class="p-3 bg-light rounded border text-start text-md-end">
                                <span class="badge bg-primary fs-6 mb-1" id="broadcastEventsBadge">Loading events...</span>
                                <div class="text-success fw-bold fs-6" id="broadcastTotalRate">प्रति सदस्य देय: ₹--</div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Events Preview list -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">इस माह के पंजीकृत कार्यक्रम (Scheduled Events in Month):</label>
                        <div id="eventsListContainer" class="p-3 bg-light rounded border" style="max-height: 140px; overflow-y: auto;">
                            <div class="text-muted small"><i class="fas fa-spinner fa-spin me-1"></i> Fetching events...</div>
                        </div>
                    </div>

                    <!-- Message Textarea -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">
                                <i class="fas fa-comment-dots text-success me-1"></i> Common Message to Users (साझा संदेश) <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" onclick="fetchMonthEventsData(document.getElementById('broadcastMonthInput').value)">
                                <i class="fas fa-sync-alt me-1"></i> Reset to Default Template
                            </button>
                        </div>
                        <textarea name="message" id="broadcastMessageBody" class="form-control font-monospace" rows="9" required placeholder="Message content will appear here..."></textarea>
                    </div>

                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-0">
                        <i class="fas fa-info-circle me-2 fs-5"></i>
                        <div>
                            "Send" बटन दबाने पर यह साझा संदेश सभी सक्रिय सदस्यों के व्हाट्सएप लॉग में दर्ज हो जाएगा और व्हाट्सएप वेब के जरिए प्रेषित किया जा सकेगा।
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg px-4 shadow">
                        <i class="fab fa-whatsapp me-2"></i> Send (भेजें)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Event Billing Modal -->
<div class="modal fade" id="eventBillingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.events.billing') }}" method="POST">
                @csrf
                <div class="modal-header" style="background: #1B365D; color: #fff;">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-calculator me-2"></i>Consolidated Monthly Event Billing</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Automatically posts consolidated event charges to member financial ledgers with duplicate protection.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Billing Month <span class="text-danger">*</span></label>
                        <input type="month" name="billing_month" class="form-control" value="{{ date('Y-m') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Applicable Event (Optional)</label>
                        <select name="event_id" class="form-select">
                            <option value="">-- Consolidated Pool / All Events --</option>
                            @foreach($events as $ev)
                            <option value="{{ $ev->id }}">{{ $ev->event_code }} - {{ $ev->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Target Scheme (Optional)</label>
                        <select name="scheme_id" class="form-select">
                            <option value="">-- All Active Members Across Schemes --</option>
                            @foreach($schemes as $sch)
                            <option value="{{ $sch->id }}">{{ $sch->name_hindi }} ({{ $sch->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Number of Events</label>
                            <input type="number" name="events_count" class="form-control" value="1" min="1" required id="billingEventsCount" oninput="updateTotalCharge()">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Rate per Event (₹)</label>
                            <input type="number" name="rate_per_event" class="form-control" value="200" min="1" required id="billingRatePerEvent" oninput="updateTotalCharge()">
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded border text-center">
                        <small class="text-muted d-block">Total Debit Per Member</small>
                        <h4 class="text-primary fw-bold mb-0" id="totalDebitPerMember">₹200.00</h4>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: #1B365D;">Process Consolidated Billing</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('script')
<script>
function updateTotalCharge() {
    const count = parseFloat(document.getElementById('billingEventsCount').value) || 0;
    const rate = parseFloat(document.getElementById('billingRatePerEvent').value) || 0;
    const total = count * rate;
    document.getElementById('totalDebitPerMember').innerText = '₹' + total.toFixed(2);
}

function onGirlSelectChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const girlNameField = document.getElementById('girlNameField');
    const fatherField = document.getElementById('fatherNameField');
    const memberSelect = document.getElementById('linkedMemberSelect');
    const titleField = document.getElementById('eventTitleField');

    if (select.value === '__custom__') {
        girlNameField.value = '';
        girlNameField.focus();
        return;
    }

    if (select.value) {
        girlNameField.value = select.value;
        const father = selectedOption.getAttribute('data-father') || '';
        const memberId = selectedOption.getAttribute('data-member-id') || '';

        if (father) fatherField.value = father;
        if (memberId && memberSelect) memberSelect.value = memberId;
        titleField.value = 'विवाह सहायता कार्यक्रम - सुपुत्री ' + select.value;
    }
}

function onSchemeSelected(schemeId) {
    // 1. Filter Girl Dropdown based on selected scheme
    const girlSelect = document.getElementById('girlDropdownSelect');
    if (girlSelect) {
        for (let i = 0; i < girlSelect.options.length; i++) {
            const opt = girlSelect.options[i];
            if (!opt.value || opt.value === '__custom__') {
                opt.style.display = '';
                continue;
            }
            const optScheme = opt.getAttribute('data-scheme-id');
            if (!schemeId || !optScheme || optScheme === schemeId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        }
        girlSelect.value = '';
    }

    // 2. Fetch and render Member Contributions Preview
    triggerMemberPreview();
}

function triggerMemberPreview() {
    const schemeId = document.getElementById('eventSchemeSelect').value;
    const eventDate = document.getElementById('eventDateField').value;
    const container = document.getElementById('previewTableContainer');
    const badge = document.getElementById('previewCountBadge');
    const totalEl = document.getElementById('previewTotalAmount');

    if (!schemeId) {
        container.innerHTML = `
            <div class="text-center py-5 text-muted">
                <i class="fas fa-hand-pointer fs-3 text-secondary mb-2 d-block"></i>
                कृपया बाईं ओर <strong>योजना (Scheme)</strong> चुनें।<br>
                सदस्यों की आयु अनुसार अंशदान की गणना यहाँ दिखेगी।
            </div>
        `;
        badge.innerText = '0 Members';
        totalEl.innerText = '₹0.00';
        return;
    }

    container.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Calculating member contributions based on Age Slabs...</div>';

    fetch('{{ route("admin.api.scheme-members-preview") }}?scheme_id=' + encodeURIComponent(schemeId) + '&event_date=' + encodeURIComponent(eventDate))
        .then(res => res.json())
        .then(data => {
            badge.innerText = (data.members_count || 0) + ' Members';
            totalEl.innerText = '₹' + Number(data.total_contribution || 0).toLocaleString('en-IN', {minimumFractionDigits: 2});

            if (data.members && data.members.length > 0) {
                let html = `
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Member (सदस्य)</th>
                                    <th>Age</th>
                                    <th>Slab</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                data.members.forEach(m => {
                    html += `
                        <tr>
                            <td>
                                <strong>${m.full_name}</strong>
                                <small class="text-muted d-block font-monospace">${m.membership_no}</small>
                            </td>
                            <td><span class="badge bg-light text-dark">${m.age} yr</span></td>
                            <td><span class="badge bg-label-primary">${m.age_slab}</span></td>
                            <td class="text-end fw-bold text-success">₹${Number(m.amount).toLocaleString('en-IN')}</td>
                            <td class="text-center"><span class="badge bg-warning text-dark">Pending</span></td>
                        </tr>
                    `;
                });
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-exclamation-circle text-warning me-1"></i>
                        इस योजना में वर्तमान में कोई सक्रिय सदस्य पंजीकृत नहीं हैं।
                    </div>
                `;
            }
        })
        .catch(err => {
            container.innerHTML = `<div class="text-center py-3 text-danger">Error: ${err.message}</div>`;
        });
}

function fetchMonthEventsData(monthStr) {
    if (!monthStr) return;
    const container = document.getElementById('eventsListContainer');
    const badge = document.getElementById('broadcastEventsBadge');
    const rateEl = document.getElementById('broadcastTotalRate');
    const msgArea = document.getElementById('broadcastMessageBody');

    container.innerHTML = '<div class="text-muted small"><i class="fas fa-spinner fa-spin me-1"></i> Loading events for ' + monthStr + '...</div>';

    fetch('{{ route("admin.api.events-by-month") }}?month=' + encodeURIComponent(monthStr))
        .then(res => res.json())
        .then(data => {
            badge.innerText = data.events_count + ' Event(s) Found';
            rateEl.innerText = 'प्रति सदस्य कुल देय: ₹' + (data.total_rate || 0);
            msgArea.value = data.default_message || '';

            if (data.events && data.events.length > 0) {
                let html = '<div class="list-group list-group-flush">';
                data.events.forEach((ev, idx) => {
                    html += `<div class="list-group-item px-0 py-1 border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-primary">${idx + 1}. ${ev.girl_name}</strong>
                            ${ev.father_name ? `<small class="text-muted"> (पिता: ${ev.father_name})</small>` : ''}
                            <small class="text-muted d-block">दिनांक: ${ev.event_date.split('T')[0]} | स्थल: ${ev.venue || 'N/A'}</small>
                        </div>
                        <span class="badge bg-success">सहयोग: ₹${parseFloat(ev.rate_per_event || 200).toFixed(0)}</span>
                    </div>`;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-muted small py-2">इस माह (' + monthStr + ') में कोई पंजीकृत विवाह कार्यक्रम नहीं है। सामान्य मासिक सहयोग लागू होगा।</div>';
            }
        })
        .catch(err => {
            container.innerHTML = '<div class="text-danger small">Error loading events: ' + err.message + '</div>';
        });
}

// Pre-load on modal open
document.addEventListener('DOMContentLoaded', function() {
    const broadcastModal = document.getElementById('monthlyBroadcastModal');
    if (broadcastModal) {
        broadcastModal.addEventListener('shown.bs.modal', function () {
            const currentMonth = document.getElementById('broadcastMonthInput').value;
            fetchMonthEventsData(currentMonth);
        });
    }
});
</script>
@endsection
