@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">कल्याण सहायता कार्यक्रम (Events & Support Pool)</h4>
                    <p class="text-muted mb-0">Manage welfare assistance grants, event collections, and member contribution billing.</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#monthlyBroadcastModal">
                        <i class="fab fa-whatsapp me-1"></i> Common Message to Members
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventBillingModal">
                        <i class="fas fa-calculator me-1"></i> Bill Members for Event
                    </button>
                    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addEventModal">
                        <i class="fas fa-plus me-1"></i> Create Event
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
                            <small class="text-muted d-block">Beneficiary / Girl Name</small>
                            <strong class="fs-6 text-primary">{{ $event->girl_name }}</strong>
                        </div>
                        <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Father / Guardian</small>
                            <strong class="fs-6">{{ $event->father_name ?? ($event->member ? $event->member->full_name : 'N/A') }}</strong>
                        </div>
                        <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Event Date</small>
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
                        <small class="text-muted d-block mb-1">Venue / स्थल</small>
                        <span class="text-dark"><i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $event->venue ?? 'Shri Shyam Dharamshala, Lohki' }}</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top flex-wrap gap-2">
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.events.contributions', $event->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-users-cog me-1"></i> View Contributions (अंशदान सूची)
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openEditEventModal({{ json_encode($event) }})">
                                <i class="fas fa-edit me-1"></i> Edit
                            </button>
                        </div>

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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.events.store') }}" method="POST" id="createEventForm">
                @csrf
                <input type="hidden" name="event_type" id="eventTypeField" value="Marriage Support">
                <div class="modal-header" style="background: #1B365D; color: #fff;">
                    <h5 class="modal-title fw-bold text-white" id="modalMainTitle">
                        <i class="fas fa-calendar-plus me-2"></i>Create Welfare Event (कार्यक्रम जोड़ें)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Member Dropdown (Only Members, No Nominees) -->
                        <div class="col-12">
                            <label class="form-label fw-bold" id="beneficiaryFieldLabel">
                                <i class="fas fa-user text-primary me-1"></i> Select Member (सदस्य का नाम) <span class="text-danger">*</span>
                            </label>
                            <select id="beneficiaryDropdownSelect" class="form-select form-select-lg mb-2" onchange="onBeneficiarySelectChange(this)">
                                <option value="">-- Choose Registered Member (पंजीकृत सदस्य चुनें) --</option>
                                @foreach($beneficiariesList as $b)
                                <option value="{{ $b['beneficiary_name'] }}"
                                    data-father="{{ $b['father_name'] }}"
                                    data-member-id="{{ $b['member_id'] }}"
                                    data-member-name="{{ $b['member_name'] }}">
                                    {{ $b['label'] }}
                                </option>
                                @endforeach
                                <option value="__custom__">➕ Other / Enter Name Manually (अन्य नाम खुद दर्ज करें)</option>
                            </select>
                            <input type="text" name="beneficiary_name" id="beneficiaryNameField" class="form-control form-control-lg fw-semibold" placeholder="Member's Full Name (सदस्य का पूरा नाम)" required oninput="onBeneficiaryManualTyping()">
                            <input type="hidden" name="girl_name" id="legacyGirlNameField">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold" id="fatherFieldLabel">Father / Spouse / Guardian (पिता / अभिभावक)</label>
                            <input type="text" name="father_name" id="fatherNameField" class="form-control" placeholder="e.g. श्री राधेश्याम शर्मा">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Linked Member ID</label>
                            <select name="member_id" id="linkedMemberSelect" class="form-select">
                                <option value="">-- Direct Welfare --</option>
                                @foreach($members as $mem)
                                <option value="{{ $mem->id }}">{{ $mem->membership_no }} - {{ $mem->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Event Title (कार्यक्रम शीर्षक)</label>
                            <input type="text" name="title" id="eventTitleField" class="form-control" placeholder="e.g. कल्याण सहायता कार्यक्रम">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold" id="eventDateFieldLabel">
                                Event Date (दिनांक) <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="event_date" id="eventDateField" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Rate per Event (₹)</label>
                            <input type="number" name="rate_per_event" class="form-control" value="200" step="0.01">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Venue (स्थल)</label>
                            <input type="text" name="venue" class="form-control" value="श्री श्याम धर्मशाला, लोहीकी">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Description / Notes</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Event notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-lg px-4" style="background: #1B365D;">
                        <i class="fas fa-check-circle me-1"></i> Create Event & Generate Contributions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="" method="POST" id="editEventForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="event_type" id="editEventTypeField">
                <div class="modal-header" style="background: #1B365D; color: #fff;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-edit me-2"></i>Edit Welfare Event (कार्यक्रम संपादित करें)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold">
                                <i class="fas fa-info-circle text-primary me-1"></i> Status (स्थिति) <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="editEventStatusSelect" class="form-select" required>
                                <option value="Upcoming">Upcoming</option>
                                <option value="Active">Active</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Linked Member ID</label>
                            <select name="member_id" id="editLinkedMemberSelect" class="form-select">
                                <option value="">-- Direct Welfare --</option>
                                @foreach($members as $mem)
                                <option value="{{ $mem->id }}">{{ $mem->membership_no }} - {{ $mem->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user text-primary me-1"></i> Select Member (सदस्य चुनें) <span class="text-danger">*</span>
                            </label>
                            <select id="editBeneficiaryDropdownSelect" class="form-select mb-2" onchange="onEditBeneficiarySelectChange(this)">
                                <option value="">-- Choose Registered Member (पंजीकृत सदस्य चुनें) --</option>
                                @foreach($beneficiariesList as $b)
                                <option value="{{ $b['beneficiary_name'] }}"
                                    data-father="{{ $b['father_name'] }}"
                                    data-member-id="{{ $b['member_id'] }}"
                                    data-member-name="{{ $b['member_name'] }}">
                                    {{ $b['label'] }}
                                </option>
                                @endforeach
                                <option value="__custom__">➕ Other / Enter Name Manually (अन्य नाम खुद दर्ज करें)</option>
                            </select>
                            <input type="text" name="beneficiary_name" id="editBeneficiaryNameField" class="form-control fw-semibold" placeholder="Member's Full Name (सदस्य का पूरा नाम)" required>
                            <input type="hidden" name="girl_name" id="editLegacyGirlNameField">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Father / Spouse / Guardian (पिता / अभिभावक)</label>
                            <input type="text" name="father_name" id="editFatherNameField" class="form-control" placeholder="e.g. श्री राधेश्याम शर्मा">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Event Title (कार्यक्रम शीर्षक)</label>
                            <input type="text" name="title" id="editEventTitleField" class="form-control" placeholder="Event Title">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Event Date (दिनांक) <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" id="editEventDateField" class="form-control" required>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Rate per Event (₹)</label>
                            <input type="number" name="rate_per_event" id="editEventRatePerEvent" class="form-control" step="0.01">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Venue (स्थल)</label>
                            <input type="text" name="venue" id="editEventVenueField" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Description / Notes</label>
                            <textarea name="description" id="editEventDescriptionField" class="form-control" rows="2" placeholder="Event notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" style="background: #1B365D;">
                        <i class="fas fa-save me-1"></i> Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Monthly All-Events Common Message Modal ("All Event ka ek saath data jayega") -->
<div class="modal fade" id="monthlyBroadcastModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.events.broadcast-send') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fab fa-whatsapp fs-3"></i>
                        <div>
                            <h5 class="modal-title text-white fw-bold mb-0">माह के सभी कार्यक्रमों का साझा संदेश (Monthly Common Message & Member Dispatch)</h5>
                            <small class="text-white-50">माह के सभी कार्यक्रमों की सूची + प्रत्येक सदस्य का (इस माह शुल्क + पिछला बकाया = कुल देय) विवरण</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <!-- Top Filter & Stats Bar -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-lg-4 col-md-5 col-12">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-calendar-alt text-primary me-1"></i> माह चुनें (Billing / Event Month) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="month" name="month" id="broadcastMonthInput" class="form-control form-control-lg fw-bold" value="{{ date('Y-m') }}" onchange="fetchMonthEventsData(this.value)" required>
                                        <button type="button" class="btn btn-outline-primary" onclick="fetchMonthEventsData(document.getElementById('broadcastMonthInput').value)">
                                            <i class="fas fa-sync-alt me-1"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                                <div class="col-lg-8 col-md-7 col-12">
                                    <div class="row g-2 text-center">
                                        <div class="col-3">
                                            <div class="p-2 bg-white rounded border shadow-xs">
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">कुल कार्यक्रम</small>
                                                <strong class="fs-6 text-primary" id="statEventsCount">0</strong>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="p-2 bg-white rounded border shadow-xs">
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">इस माह का सहयोग</small>
                                                <strong class="fs-6 text-dark" id="statThisMonthTotal">₹0</strong>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="p-2 bg-white rounded border shadow-xs">
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">पिछला बकाया</small>
                                                <strong class="fs-6 text-danger" id="statPrevDueTotal">₹0</strong>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="p-2 bg-white rounded border shadow-xs">
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">कुल देय राशि</small>
                                                <strong class="fs-6 text-success fw-bold" id="statGrandTotal">₹0</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Tabs -->
                    <ul class="nav nav-pills nav-fill mb-3 bg-white p-1 rounded border shadow-sm" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active fw-bold py-2" data-bs-toggle="tab" data-bs-target="#tab-common-broadcast">
                                <i class="fas fa-bullhorn me-1 text-primary"></i> साझा संदेश टेम्पलेट (Common Broadcast Template)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link fw-bold py-2" data-bs-toggle="tab" data-bs-target="#tab-member-dispatch">
                                <i class="fab fa-whatsapp me-1 text-success"></i> सदस्यवार देय राशि व व्हाट्सएप प्रेषण (<span id="tabMembersCountBadge">0</span> Members)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- TAB 1: Common Broadcast & Events -->
                        <div class="tab-pane fade show active" id="tab-common-broadcast" role="tabpanel">
                            <div class="row g-3">
                                <!-- Scheduled Events in Month -->
                                <div class="col-lg-5 col-12">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white border-bottom py-2">
                                            <h6 class="card-title fw-bold mb-0 text-dark">
                                                <i class="fas fa-list-check text-primary me-1"></i> इस माह के पंजीकृत विवाह कार्यक्रम:
                                            </h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div id="eventsListContainer" style="max-height: 320px; overflow-y: auto;">
                                                <div class="text-muted small"><i class="fas fa-spinner fa-spin me-1"></i> Loading events...</div>
                                            </div>
                                            <div class="alert alert-light border small mt-3 mb-0 p-2 text-muted">
                                                <i class="fas fa-info-circle text-primary me-1"></i>
                                                सदस्यों को भेजे जाने वाले व्यक्तिगत संदेश में यह सभी कार्यक्रम और सदस्य का <strong>[इस माह का सहयोग + पिछला बकाया = कुल देय]</strong> स्वतः जुड़ जाएगा।
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Common Broadcast Template -->
                                <div class="col-lg-7 col-12">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                                            <h6 class="card-title fw-bold mb-0 text-dark">
                                                <i class="fas fa-comment-dots text-success me-1"></i> साझा संदेश (Common Message Body):
                                            </h6>
                                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" onclick="fetchMonthEventsData(document.getElementById('broadcastMonthInput').value)">
                                                <i class="fas fa-sync-alt me-1"></i> Reset Template
                                            </button>
                                        </div>
                                        <div class="card-body p-3">
                                            <textarea name="message" id="broadcastMessageBody" class="form-control font-monospace" rows="12" required placeholder="Message content will appear here..."></textarea>
                                            <div class="small text-muted mt-2">
                                                उपलब्ध टैग्स: <code>@{{member_name}}</code>, <code>@{{membership_no}}</code>, <code>@{{this_month}}</code>, <code>@{{previous_due}}</code>, <code>@{{total_due}}</code>, <code>@{{rate}}</code>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: Member-wise Breakdown & Individual WhatsApp Links -->
                        <div class="tab-pane fade" id="tab-member-dispatch" role="tabpanel">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom py-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <h6 class="card-title fw-bold mb-0 text-dark">
                                                <i class="fas fa-users-cog text-primary me-1"></i> प्रत्येक सदस्य की गणना एवं 1-क्लिक व्हाट्सएप प्रेषण
                                            </h6>
                                            <small class="text-muted">Formula: [इस माह का शुल्क (कार्यक्रम × स्लैब दर)] + [पिछला बकाया] = [कुल देय]</small>
                                        </div>
                                        <div style="min-width: 260px;">
                                            <input type="text" id="memberSearchFilter" class="form-control form-control-sm" placeholder="🔍 Search member name, no, mobile..." oninput="filterMembersTable()">
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0" id="membersDispatchTable">
                                        <thead class="table-light sticky-top">
                                            <tr style="font-size: 0.82rem;">
                                                <th>सदस्य (Member)</th>
                                                <th>योजना (Scheme)</th>
                                                <th class="text-center">दर (Rate)</th>
                                                <th class="text-end">इस माह (This Month)</th>
                                                <th class="text-end">पिछला बकाया (Due)</th>
                                                <th class="text-end">कुल देय (Total)</th>
                                                <th class="text-center">व्हाट्सएप (Direct Send)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="membersTableBody">
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="fas fa-spinner fa-spin me-1"></i> Loading member records...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success btn-lg px-4 shadow">
                        <i class="fab fa-whatsapp me-2"></i> सभी सदस्यों के लिए साझा संदेश लॉग व सेंड करें (Bulk Dispatch)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Event Billing Modal -->
<div class="modal fade" id="eventBillingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.events.billing') }}" method="POST">
                @csrf
                <input type="hidden" name="rate_type" value="member_slab">
                <div class="modal-header" style="background: #1B365D; color: #fff;">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-calculator me-2"></i>Consolidated Monthly Event Billing (मासिक कार्यक्रम बिलिंग)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        यह प्रक्रिया सभी सक्रिय सदस्यों के वित्तीय लेजर में उनकी <strong>मासिक सहयोग दर (Monthly Support / Age Slab Amount)</strong> के आधार पर कार्यक्रमों का बिल दर्ज करती है।
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold">Billing Month (बिलिंग माह) <span class="text-danger">*</span></label>
                            <input type="month" name="billing_month" id="billingMonthInput" class="form-control form-control-lg fw-bold" value="{{ date('Y-m') }}" required onchange="onBillingMonthChange(this.value)">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold">Number of Events in Month (माह के कुल कार्यक्रम) <span class="text-danger">*</span></label>
                            <input type="number" name="events_count" class="form-control form-control-lg fw-bold" value="1" min="1" required id="billingEventsCount" oninput="updateBillingLiveSummary()">
                            <small class="text-muted" id="billingMonthEventsHint">माह के पंजीकृत कार्यक्रमों की संख्या</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Target Scheme (योजना चयन - Optional)</label>
                        <select name="scheme_id" class="form-select form-select-lg">
                            <option value="">-- All Active Members Across Schemes (सभी योजनाएं) --</option>
                            @foreach($schemes as $sch)
                            <option value="{{ $sch->id }}">{{ $sch->name_hindi }} ({{ $sch->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Live Calculation Summary Card -->
                    <div class="card border bg-light shadow-xs p-3">
                        <div class="row g-3 text-center align-items-center">
                            <div class="col-4">
                                <small class="text-muted d-block" style="font-size: 0.75rem;">माह के कार्यक्रम</small>
                                <strong class="fs-5 text-primary" id="billingSummaryEventsCount">0</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block" style="font-size: 0.75rem;">सक्रिय सदस्य</small>
                                <strong class="fs-5 text-dark" id="billingSummaryMembersCount">0</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block" style="font-size: 0.75rem;">अनुमानित कुल बिलिंग</small>
                                <strong class="fs-5 text-success fw-bold" id="billingSummaryTotalAmount">₹0</strong>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="small text-muted text-center">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            प्रत्येक सदस्य के खाते में केवल उसकी <strong>निर्धारित मासिक सहयोग राशि (Monthly Support Amount)</strong> × कार्यक्रमों की संख्या के अनुसार बिल डेबिट होगा।
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold" style="background: #1B365D;">
                        <i class="fas fa-check-circle me-1"></i> Process Consolidated Billing (बिल जनरेट करें)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let allMembersCache = [];
let billingMonthDataCache = null;

function onBillingMonthChange(monthStr) {
    if (!monthStr) return;
    fetch('{{ route("admin.api.events-by-month") }}?month=' + encodeURIComponent(monthStr))
        .then(res => res.json())
        .then(data => {
            billingMonthDataCache = data;
            const countInput = document.getElementById('billingEventsCount');
            const eventsCount = data.events_count > 0 ? data.events_count : 1;
            countInput.value = eventsCount;
            document.getElementById('billingMonthEventsHint').innerText = `${data.events_count || 0} कार्यक्रम पंजीकृत हैं (${data.month_name})`;
            updateBillingLiveSummary();
        })
        .catch(err => console.error(err));
}

function updateBillingLiveSummary() {
    if (!billingMonthDataCache) return;
    const eventsCount = parseInt(document.getElementById('billingEventsCount').value) || 1;
    const members = billingMonthDataCache.members_preview || [];
    
    let totalBilling = 0;
    members.forEach(m => {
        const rate = parseFloat(m.rate) || 0;
        totalBilling += (rate * eventsCount);
    });

    document.getElementById('billingSummaryEventsCount').innerText = eventsCount;
    document.getElementById('billingSummaryMembersCount').innerText = members.length;
    document.getElementById('billingSummaryTotalAmount').innerText = '₹' + totalBilling.toLocaleString('en-IN');
}

function onBeneficiaryManualTyping() {
    const nameField = document.getElementById('beneficiaryNameField');
    const legacyField = document.getElementById('legacyGirlNameField');
    const titleField = document.getElementById('eventTitleField');
    if (legacyField) legacyField.value = nameField.value;

    if (nameField.value.trim().length > 0) {
        titleField.value = 'कल्याण सहायता कार्यक्रम - ' + nameField.value.trim();
    }
}

function onBeneficiarySelectChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const nameField = document.getElementById('beneficiaryNameField');
    const legacyField = document.getElementById('legacyGirlNameField');
    const fatherField = document.getElementById('fatherNameField');
    const memberSelect = document.getElementById('linkedMemberSelect');
    const titleField = document.getElementById('eventTitleField');

    if (select.value === '__custom__') {
        nameField.value = '';
        if (legacyField) legacyField.value = '';
        nameField.focus();
        return;
    }

    if (select.value) {
        nameField.value = select.value;
        if (legacyField) legacyField.value = select.value;
        const father = selectedOption.getAttribute('data-father') || '';
        const memberId = selectedOption.getAttribute('data-member-id') || '';

        if (father) fatherField.value = father;
        if (memberId && memberSelect) memberSelect.value = memberId;

        titleField.value = 'कल्याण सहायता कार्यक्रम - ' + select.value;
    }
}

function openEditEventModal(event) {
    const form = document.getElementById('editEventForm');
    form.action = '{{ url("admin/events") }}/' + event.id;

    document.getElementById('editEventStatusSelect').value = event.status || 'Upcoming';
    document.getElementById('editEventTypeField').value = event.event_type || 'Marriage Support';
    document.getElementById('editBeneficiaryNameField').value = event.girl_name || event.beneficiary_name || '';
    document.getElementById('editLegacyGirlNameField').value = event.girl_name || event.beneficiary_name || '';
    document.getElementById('editFatherNameField').value = event.father_name || '';
    document.getElementById('editLinkedMemberSelect').value = event.member_id || '';
    document.getElementById('editEventTitleField').value = event.title || '';
    document.getElementById('editEventDateField').value = event.event_date ? event.event_date.split('T')[0] : '';
    document.getElementById('editEventRatePerEvent').value = event.rate_per_event || '200';
    document.getElementById('editEventVenueField').value = event.venue || '';
    document.getElementById('editEventDescriptionField').value = event.description || '';

    const select = document.getElementById('editBeneficiaryDropdownSelect');
    if (select) {
        select.value = '';
        if (event.member_id) {
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].getAttribute('data-member-id') == event.member_id) {
                    select.selectedIndex = i;
                    break;
                }
            }
        }
    }

    const editModal = new bootstrap.Modal(document.getElementById('editEventModal'));
    editModal.show();
}

function onEditBeneficiarySelectChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const nameField = document.getElementById('editBeneficiaryNameField');
    const legacyField = document.getElementById('editLegacyGirlNameField');
    const fatherField = document.getElementById('editFatherNameField');
    const memberSelect = document.getElementById('editLinkedMemberSelect');
    const titleField = document.getElementById('editEventTitleField');

    if (select.value === '__custom__') {
        nameField.value = '';
        if (legacyField) legacyField.value = '';
        nameField.focus();
        return;
    }

    if (select.value) {
        nameField.value = select.value;
        if (legacyField) legacyField.value = select.value;
        const father = selectedOption.getAttribute('data-father') || '';
        const memberId = selectedOption.getAttribute('data-member-id') || '';

        if (father) fatherField.value = father;
        if (memberId && memberSelect) memberSelect.value = memberId;

        titleField.value = 'कल्याण सहायता कार्यक्रम - ' + select.value;
    }
}

function fetchMonthEventsData(monthStr) {
    if (!monthStr) return;
    const container = document.getElementById('eventsListContainer');
    const msgArea = document.getElementById('broadcastMessageBody');
    const statEvents = document.getElementById('statEventsCount');
    const statThisMonth = document.getElementById('statThisMonthTotal');
    const statPrevDue = document.getElementById('statPrevDueTotal');
    const statGrand = document.getElementById('statGrandTotal');
    const membersBadge = document.getElementById('tabMembersCountBadge');
    const tableBody = document.getElementById('membersTableBody');

    container.innerHTML = '<div class="text-muted small"><i class="fas fa-spinner fa-spin me-1"></i> Loading events for ' + monthStr + '...</div>';
    tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Calculating member dues and personalized messages...</td></tr>';

    fetch('{{ route("admin.api.events-by-month") }}?month=' + encodeURIComponent(monthStr))
        .then(res => res.json())
        .then(data => {
            statEvents.innerText = data.events_count || 0;
            statThisMonth.innerText = '₹' + (data.grand_this_month_total || 0).toLocaleString('en-IN');
            statPrevDue.innerText = '₹' + (data.grand_previous_due_total || 0).toLocaleString('en-IN');
            statGrand.innerText = '₹' + (data.grand_total_due || 0).toLocaleString('en-IN');
            membersBadge.innerText = data.total_members_count || 0;
            msgArea.value = data.default_message || '';

            // Render Events list
            if (data.events && data.events.length > 0) {
                let html = '<div class="list-group list-group-flush">';
                data.events.forEach((ev, idx) => {
                    html += `<div class="list-group-item px-0 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong class="text-primary">${idx + 1}. ${ev.girl_name}</strong>
                                ${ev.father_name ? `<small class="text-muted"> (पिता: ${ev.father_name})</small>` : ''}
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-calendar-alt text-danger me-1"></i> ${ev.event_date.split('T')[0]} | 
                                    <i class="fas fa-map-marker-alt text-muted me-1"></i> ${ev.venue || 'श्री श्याम धर्मशाला, लोहीकी'}
                                </div>
                            </div>
                            <span class="badge bg-label-success">₹${parseFloat(ev.rate_per_event || 200).toFixed(0)}</span>
                        </div>
                    </div>`;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-muted small py-3"><i class="fas fa-info-circle me-1"></i> इस माह (' + monthStr + ') में कोई पंजीकृत विवाह कार्यक्रम नहीं है। सामान्य मासिक सहयोग लागू होगा।</div>';
            }

            // Store and Render Members preview table
            allMembersCache = data.members_preview || [];
            renderMembersTable(allMembersCache);
        })
        .catch(err => {
            container.innerHTML = '<div class="text-danger small">Error loading events: ' + err.message + '</div>';
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Error: ' + err.message + '</td></tr>';
        });
}

function renderMembersTable(members) {
    const tbody = document.getElementById('membersTableBody');
    if (!members || members.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">कोई सक्रिय सदस्य नहीं मिला।</td></tr>';
        return;
    }

    let rowsHtml = '';
    members.forEach(m => {
        const hasMobile = m.mobile && m.mobile.trim().length > 0;
        rowsHtml += `<tr>
            <td>
                <div class="fw-bold text-dark">${m.name}</div>
                <small class="text-muted">${m.membership_no} ${hasMobile ? '| ' + m.mobile : ''}</small>
            </td>
            <td>
                <span class="badge bg-label-info">${m.scheme_name}</span>
                <small class="text-muted d-block" style="font-size: 0.72rem;">${m.agent_name}</small>
            </td>
            <td class="text-center font-monospace">₹${m.rate}</td>
            <td class="text-end font-monospace text-dark">₹${m.this_month.toLocaleString('en-IN')}</td>
            <td class="text-end font-monospace text-danger">₹${m.previous_due.toLocaleString('en-IN')}</td>
            <td class="text-end font-monospace">
                <span class="badge bg-label-success fw-bold fs-6">₹${m.total_due.toLocaleString('en-IN')}</span>
            </td>
            <td class="text-center">
                ${hasMobile ? `
                    <a href="${m.whatsapp_url}" target="_blank" class="btn btn-sm btn-success shadow-xs px-2 py-1" title="Send WhatsApp to ${m.name}">
                        <i class="fab fa-whatsapp me-1"></i> Send
                    </a>
                ` : `
                    <span class="badge bg-label-secondary">No Mobile</span>
                `}
            </td>
        </tr>`;
    });

    tbody.innerHTML = rowsHtml;
}

function filterMembersTable() {
    const q = (document.getElementById('memberSearchFilter').value || '').toLowerCase().trim();
    if (!q) {
        renderMembersTable(allMembersCache);
        return;
    }
    const filtered = allMembersCache.filter(m => {
        return (m.name && m.name.toLowerCase().includes(q)) ||
               (m.membership_no && m.membership_no.toLowerCase().includes(q)) ||
               (m.mobile && m.mobile.toLowerCase().includes(q)) ||
               (m.scheme_name && m.scheme_name.toLowerCase().includes(q)) ||
               (m.agent_name && m.agent_name.toLowerCase().includes(q));
    });
    renderMembersTable(filtered);
}

// Pre-load on modal open
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($editEvent) && $editEvent)
        openEditEventModal(@json($editEvent));
    @endif

    if (window.location.hash === '#createEventModal' || window.location.hash === '#createEvent') {
        const addModal = document.getElementById('addEventModal');
        if (addModal) {
            new bootstrap.Modal(addModal).show();
        }
    }

    const broadcastModal = document.getElementById('monthlyBroadcastModal');
    if (broadcastModal) {
        broadcastModal.addEventListener('shown.bs.modal', function () {
            const currentMonth = document.getElementById('broadcastMonthInput').value;
            fetchMonthEventsData(currentMonth);
        });
    }

    const billingMonthInput = document.getElementById('billingMonthInput');
    if (billingMonthInput) {
        onBillingMonthChange(billingMonthInput.value);
    }
});
</script>
@endsection

