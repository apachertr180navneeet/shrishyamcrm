@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">व्हाट्सएप सेवा केंद्र (WhatsApp Messaging Center)</h4>
                    <p class="text-muted mb-0">Dispatch official payment receipts, monthly events shared broadcast, and member-wise dues breakdown via WhatsApp.</p>
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
                        <i class="fab fa-whatsapp me-1"></i> Open WhatsApp Web Broadcast
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

    <div class="row g-4">
        <!-- Message Dispatch Form / Tabs -->
        <div class="col-lg-7 col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom p-0">
                    <ul class="nav nav-tabs nav-fill" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active py-3 fw-bold" role="tab" data-bs-toggle="tab" data-bs-target="#tab-monthly-broadcast">
                                <i class="fas fa-bullhorn text-success me-2"></i> माह के सभी कार्यक्रमों का साझा संदेश (Common Message)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link py-3 fw-bold" role="tab" data-bs-toggle="tab" data-bs-target="#tab-single-message">
                                <i class="fas fa-user me-2 text-primary"></i> व्यक्तिगत संदेश (Single Message)
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content p-0">
                        <!-- Tab 1: Monthly All Events Broadcast -->
                        <div class="tab-pane fade show active" id="tab-monthly-broadcast" role="tabpanel">
                            <form action="{{ route('admin.events.broadcast-send') }}" method="POST">
                                @csrf
                                <div class="row g-3 mb-3 align-items-center">
                                    <div class="col-md-6 col-12">
                                        <label class="form-label fw-bold text-dark">
                                            <i class="fas fa-calendar-alt text-primary me-1"></i> माह चुनें (Month) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="month" name="month" id="waBroadcastMonth" class="form-control form-control-lg fw-bold" value="{{ date('Y-m') }}" onchange="fetchWaMonthEvents(this.value)" required>
                                            <button type="button" class="btn btn-outline-primary" onclick="fetchWaMonthEvents(document.getElementById('waBroadcastMonth').value)">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="p-2 bg-light rounded border text-center">
                                            <div class="d-flex justify-content-around">
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">कार्यक्रम</small>
                                                    <strong class="text-primary fs-6" id="waEventsCountBadge">0</strong>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">इस माह</small>
                                                    <strong class="text-dark fs-6" id="waStatThisMonth">₹0</strong>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">कुल देय</small>
                                                    <strong class="text-success fs-6" id="waStatTotalDue">₹0</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted small mb-1">इस माह के पंजीकृत विवाह कार्यक्रम (Month Events):</label>
                                    <div id="waEventsList" class="p-2 bg-light rounded border small" style="max-height: 120px; overflow-y: auto;">
                                        Loading events...
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold mb-0">
                                            <i class="fas fa-comment-alt text-success me-1"></i> Common Message Body (साझा संदेश) <span class="text-danger">*</span>
                                        </label>
                                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" onclick="fetchWaMonthEvents(document.getElementById('waBroadcastMonth').value)">
                                            <i class="fas fa-sync-alt me-1"></i> Reset Template
                                        </button>
                                    </div>
                                    <textarea name="message" id="waBroadcastMessage" class="form-control font-monospace" rows="8" required placeholder="Generating common message..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100 shadow mb-4">
                                    <i class="fab fa-whatsapp me-2"></i> सभी सदस्यों के लिए साझा संदेश लॉग व सेंड करें (Bulk Send)
                                </button>

                                <!-- Member-wise personalized dispatch table -->
                                <div class="border-top pt-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <h6 class="fw-bold mb-0 text-dark">
                                            <i class="fas fa-users text-primary me-1"></i> सदस्यवार देय राशि व 1-क्लिक व्हाट्सएप
                                        </h6>
                                        <input type="text" id="waMemberSearch" class="form-control form-control-sm" style="max-width: 200px;" placeholder="🔍 Search member..." oninput="filterWaMembersTable()">
                                    </div>
                                    <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr style="font-size: 0.78rem;">
                                                    <th>सदस्य (Member)</th>
                                                    <th class="text-center">दर</th>
                                                    <th class="text-end">इस माह</th>
                                                    <th class="text-end">बकाया</th>
                                                    <th class="text-end">कुल देय</th>
                                                    <th class="text-center">व्हाट्सएप</th>
                                                </tr>
                                            </thead>
                                            <tbody id="waMembersTableBody">
                                                <tr>
                                                    <td colspan="6" class="text-center py-3 text-muted">Loading members...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 2: Single Member Message -->
                        <div class="tab-pane fade" id="tab-single-message" role="tabpanel">
                            <form action="{{ route('admin.whatsapp.send') }}" method="POST" target="_blank">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Select Member (सदस्य चुनें)</label>
                                    <select name="member_id" id="memberSelect" class="form-select" onchange="populateMemberDetails()">
                                        <option value="">-- Choose Society Member --</option>
                                        @foreach($members as $m)
                                        <option value="{{ $m->id }}" data-name="{{ $m->full_name }}" data-mobile="{{ $m->mobile }}" data-memno="{{ $m->membership_no }}" data-scheme="{{ $m->scheme ? $m->scheme->name_hindi : '' }}">
                                            {{ $m->membership_no }} - {{ $m->full_name }} ({{ $m->mobile }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">Recipient Name <span class="text-danger">*</span></label>
                                        <input type="text" name="recipient_name" id="recipientName" class="form-control" placeholder="Member Name" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">Mobile (10 digits) <span class="text-danger">*</span></label>
                                        <input type="tel" name="mobile" id="recipientMobile" class="form-control" placeholder="Mobile No" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Message Template Type</label>
                                    <select name="message_type" id="templateSelect" class="form-select" onchange="applyTemplate()">
                                        <option value="Receipt">Payment Receipt (रसीद सूचना)</option>
                                        <option value="Event Reminder">Event Reminder (विवाह कार्यक्रम सूचना)</option>
                                        <option value="Due Alert">Monthly Dues Reminder (सहयोग राशि सूचना)</option>
                                        <option value="Welcome">Welcome Greeting (सोसायटी स्वागत संदेश)</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Message Text (संदेश) <span class="text-danger">*</span></label>
                                    <textarea name="message_body" id="messageBody" class="form-control" rows="5" required>जय श्री श्याम 🙏

श्री श्याम वेलफेयर सोसायटी लोहीकी की ओर से आपका हार्दिक स्वागत है। आपके सहयोग से समाज सेवा का कार्य निरंतर गतिमान है।</textarea>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100 shadow">
                                    <i class="fab fa-whatsapp me-2"></i> Open & Send via WhatsApp
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Sent Logs -->
        <div class="col-lg-5 col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-history me-2 text-primary"></i> Dispatched Messages History
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Recipient</th>
                                <th>Mobile</th>
                                <th>Type</th>
                                <th>Sent Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td><strong>{{ $log->recipient_name }}</strong></td>
                                <td>{{ $log->mobile }}</td>
                                <td><span class="badge bg-label-info">{{ $log->message_type }}</span></td>
                                <td><small class="text-muted">{{ $log->sent_at ? $log->sent_at->diffForHumans() : '-' }}</small></td>
                                <td>
                                    <span class="badge {{ $log->status === 'Sent' ? 'bg-success' : 'bg-warning' }}">
                                        {{ $log->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No WhatsApp messages dispatched yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let waMembersCache = [];

function fetchWaMonthEvents(monthStr) {
    if (!monthStr) return;
    const badge = document.getElementById('waEventsCountBadge');
    const list = document.getElementById('waEventsList');
    const msg = document.getElementById('waBroadcastMessage');
    const thisMonthEl = document.getElementById('waStatThisMonth');
    const totalDueEl = document.getElementById('waStatTotalDue');
    const tbody = document.getElementById('waMembersTableBody');

    badge.innerText = '...';
    list.innerHTML = 'Fetching events for ' + monthStr + '...';
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Loading member details...</td></tr>';

    fetch('{{ route("admin.api.events-by-month") }}?month=' + encodeURIComponent(monthStr))
        .then(res => res.json())
        .then(data => {
            badge.innerText = (data.events_count || 0) + ' Events';
            thisMonthEl.innerText = '₹' + (data.grand_this_month_total || 0).toLocaleString('en-IN');
            totalDueEl.innerText = '₹' + (data.grand_total_due || 0).toLocaleString('en-IN');
            msg.value = data.default_message || '';

            if (data.events && data.events.length > 0) {
                let html = '<ul class="mb-0 ps-3">';
                data.events.forEach(e => {
                    html += `<li><strong>${e.girl_name}</strong> - ${e.event_date.split('T')[0]} (सहयोग: ₹${e.rate_per_event || 200})</li>`;
                });
                html += '</ul>';
                list.innerHTML = html;
            } else {
                list.innerHTML = '<span class="text-muted">इस माह (' + monthStr + ') में कोई विवाह कार्यक्रम पंजीकृत नहीं है।</span>';
            }

            waMembersCache = data.members_preview || [];
            renderWaMembersTable(waMembersCache);
        })
        .catch(err => {
            list.innerHTML = '<span class="text-danger">Error loading events: ' + err.message + '</span>';
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-3 text-danger">Error: ' + err.message + '</td></tr>';
        });
}

function renderWaMembersTable(members) {
    const tbody = document.getElementById('waMembersTableBody');
    if (!members || members.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-3 text-muted">No members found.</td></tr>';
        return;
    }

    let html = '';
    members.forEach(m => {
        const hasMobile = m.mobile && m.mobile.trim().length > 0;
        html += `<tr>
            <td>
                <strong class="d-block text-dark">${m.name}</strong>
                <small class="text-muted">${m.membership_no}</small>
            </td>
            <td class="text-center font-monospace small">₹${m.rate}</td>
            <td class="text-end font-monospace small">₹${m.this_month}</td>
            <td class="text-end font-monospace small text-danger">₹${m.previous_due}</td>
            <td class="text-end font-monospace small fw-bold text-success">₹${m.total_due}</td>
            <td class="text-center">
                ${hasMobile ? `
                    <a href="${m.whatsapp_url}" target="_blank" class="btn btn-xs btn-success px-2 py-0" title="Send WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                ` : `
                    <span class="badge bg-label-secondary" style="font-size:0.65rem;">No No.</span>
                `}
            </td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function filterWaMembersTable() {
    const q = (document.getElementById('waMemberSearch').value || '').toLowerCase().trim();
    if (!q) {
        renderWaMembersTable(waMembersCache);
        return;
    }
    const filtered = waMembersCache.filter(m => {
        return (m.name && m.name.toLowerCase().includes(q)) ||
               (m.membership_no && m.membership_no.toLowerCase().includes(q)) ||
               (m.mobile && m.mobile.toLowerCase().includes(q));
    });
    renderWaMembersTable(filtered);
}

document.addEventListener('DOMContentLoaded', function() {
    const mInput = document.getElementById('waBroadcastMonth');
    if (mInput) {
        fetchWaMonthEvents(mInput.value);
    }
});

function populateMemberDetails() {
    const select = document.getElementById('memberSelect');
    const option = select.options[select.selectedIndex];
    if (!option.value) return;

    document.getElementById('recipientName').value = option.getAttribute('data-name');
    document.getElementById('recipientMobile').value = option.getAttribute('data-mobile');
    applyTemplate();
}

function applyTemplate() {
    const type = document.getElementById('templateSelect').value;
    const name = document.getElementById('recipientName').value || 'सदस्य महोदय';
    const select = document.getElementById('memberSelect');
    const option = select.options[select.selectedIndex];
    const memNo = option.value ? option.getAttribute('data-memno') : 'MEM-2026-XXXX';
    const scheme = option.value ? option.getAttribute('data-scheme') : 'बुजुर्ग सम्मान योजना';

    let text = "";
    if (type === 'Receipt') {
        text = "जय श्री श्याम 🙏\n\nआदरणीय " + name + " जी,\nश्री श्याम वेलफेयर सोसायटी लोहीकी में आपका अंशदान/सहयोग प्राप्त हुआ है।\n\nसदस्यता क्रमांक: " + memNo + "\nयोजना: " + scheme + "\n\nसोसायटी को निरंतर सहयोग देने के लिए आपका बहुत-बहुत धन्यवाद!";
    } else if (type === 'Event Reminder') {
        text = "जय श्री श्याम 🙏\n\nआदरणीय " + name + " जी,\nश्री श्याम वेलफेयर सोसायटी द्वारा आयोजित आगामी विवाह सहायता कार्यक्रम की सूचना। कृपया अपनी उपस्थिति व सहयोग सुनिश्चित करें।\n\nस्थान: श्री श्याम धर्मशाला, लोहीकी";
    } else if (type === 'Due Alert') {
        text = "जय श्री श्याम 🙏\n\nआदरणीय " + name + " जी (" + memNo + "),\nश्री श्याम वेलफेयर सोसायटी लोहीकी के कार्यक्रमों हेतु आपकी सहयोग राशि अपेक्षित है। कृपया समय पर सहयोग राशि जमा करवाकर समाज सेवा में भागीदार बनें।";
    } else {
        text = "जय श्री श्याम 🙏\n\nआदरणीय " + name + " जी,\nश्री श्याम वेलफेयर सोसायटी लोहीकी में आपका हार्दिक स्वागत है।\nसदस्यता क्रमांक: " + memNo + "\n\nकल्याणकारी योजनाओं से जुड़ने के लिए धन्यवाद!";
    }

    document.getElementById('messageBody').value = text;
}
</script>
@endsection

