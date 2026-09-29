@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Agent Header Profile -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-xl bg-label-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
                        <i class="fas fa-user-tie fs-2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h3 class="fw-bold mb-0">{{ $agent->name }}</h3>
                            <span class="badge bg-label-primary">{{ $agent->agent_code }}</span>
                            <span class="badge bg-success">{{ $agent->status }}</span>
                        </div>
                        <p class="text-muted mb-0">
                            <i class="fas fa-phone-alt me-1 text-primary"></i> {{ $agent->mobile }} &nbsp;|&nbsp;
                            <i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ $agent->district }} &nbsp;|&nbsp;
                            <i class="fas fa-percentage me-1 text-warning"></i> Commission Rate: {{ $agent->commission_rate }}%
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-warning shadow-sm" onclick='openCredentialsModal(@json($agent))'>
                        <i class="fas fa-key me-1"></i> Login Credentials & WhatsApp
                    </button>
                    <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Agent Profile
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick='openEditAgentModal(@json($agent))'>
                        <i class="fas fa-bolt me-1"></i> Quick Edit
                    </button>
                    <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Agents
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Portal Login Details Card -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold mb-1 text-primary">
                        <i class="fas fa-id-card text-warning me-2"></i> एजेंट पोर्टल लॉगिन विवरण (Agent Portal Account)
                    </h5>
                    <p class="text-muted mb-0 small">कार्यकर्ता को पोर्टल लिंक, मोबाइल नंबर (यूजरनेम) और पासवर्ड भेजें जिससे वे लॉगिन कर सकें।</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-warning fw-semibold shadow-sm" onclick='openCredentialsModal(@json($agent))'>
                        <i class="fas fa-key me-1"></i> क्रेडेंशियल्स देखें / पासवर्ड बदलें
                    </button>
                    @php
                        $rawMob = $agent->mobile ?: ($agent->user ? $agent->user->phone : '');
                        $cleanMob = preg_replace('/[^0-9]/', '', $rawMob);
                        if (strlen($cleanMob) === 10) $cleanMob = '91' . $cleanMob;
                        $credMsg = \App\Services\WhatsAppService::getAgentCredentialsMessage($agent);
                    @endphp
                    <a href="{{ $credMsg['url'] }}" target="_blank" class="btn btn-success fw-semibold shadow-sm" style="background-color: #25D366; border-color: #25D366;">
                        <i class="fab fa-whatsapp me-1 fs-5"></i> 📲 WhatsApp पर विवरण भेजें
                    </a>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-4 col-12">
                    <div class="p-3 bg-white rounded border">
                        <small class="text-muted d-block fw-semibold mb-1"><i class="fas fa-globe text-primary me-1"></i> Portal Login URL</small>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="font-monospace fw-bold text-truncate me-2 small">{{ route('admin.login') }}</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ route('admin.login') }}'); showToast('पोर्टल लिंक कॉपी हो गया!');" title="Copy URL">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="p-3 bg-white rounded border">
                        <small class="text-muted d-block fw-semibold mb-1"><i class="fas fa-user text-primary me-1"></i> Username (Login ID)</small>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="font-monospace fw-bold text-dark">{{ $agent->mobile ?: ($agent->user ? $agent->user->email : $agent->agent_code) }}</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ $agent->mobile ?: ($agent->user ? $agent->user->email : $agent->agent_code) }}'); showToast('यूजरनेम कॉपी हो गया!');" title="Copy Username">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="p-3 bg-white rounded border">
                        <small class="text-muted d-block fw-semibold mb-1"><i class="fas fa-lock text-warning me-1"></i> Password Status</small>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="badge bg-label-success"><i class="fas fa-check-circle me-1"></i> User Account Active</span>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick='openCredentialsModal(@json($agent))'>
                                <i class="fas fa-sync-alt me-1"></i> Reset Pass
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-semibold">Assigned Members</small>
                <h3 class="fw-bold text-primary my-1">{{ $agent->members->count() }} Members</h3>
                <small class="text-muted">Active in {{ $agent->district }}</small>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-semibold">Total Collections</small>
                <h3 class="fw-bold text-success my-1">₹{{ number_format($agent->total_collection) }}</h3>
                <small class="text-success"><i class="fas fa-check-circle me-1"></i> Recorded in system</small>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-semibold">Commission Earned</small>
                <h3 class="fw-bold text-warning my-1">₹{{ number_format($agent->total_commission) }}</h3>
                <small class="text-muted">Calculated at {{ $agent->commission_rate }}%</small>
            </div>
        </div>
    </div>

    <!-- Assigned Members Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header border-bottom py-3">
            <h5 class="card-title mb-0 fw-semibold">Assigned Society Members ({{ $agent->members->count() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Membership No</th>
                        <th>Member Name</th>
                        <th>Mobile</th>
                        <th>Scheme</th>
                        <th>सहयोग दर (प्रति कार्यक्रम)</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agent->members as $m)
                    <tr>
                        <td><strong class="text-primary">{{ $m->membership_no }}</strong></td>
                        <td><strong>{{ $m->full_name }}</strong></td>
                        <td>{{ $m->mobile }}</td>
                        <td><span class="badge bg-label-primary">{{ $m->scheme ? $m->scheme->name_hindi : 'N/A' }}</span></td>
                        <td><strong class="text-success">₹{{ number_format($m->monthly_support_amount) }}/कार्यक्रम</strong></td>
                        <td><span class="badge bg-success">{{ $m->status }}</span></td>
                        <td class="text-center">
                            <a href="{{ route('admin.members.show', $m->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No members assigned to this agent yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Agent Modal -->
<div class="modal fade" id="editAgentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editAgentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit text-primary me-2"></i>Edit Society Agent (<span id="editAgentCodeBadge" class="text-primary">{{ $agent->agent_code }}</span>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Agent Full Name (पूरा नाम) <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_agent_name" class="form-control" value="{{ $agent->name }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" name="mobile" id="edit_agent_mobile" class="form-control" value="{{ $agent->mobile }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">District (जिला) <span class="text-danger">*</span></label>
                            <input type="text" name="district" id="edit_agent_district" class="form-control" value="{{ $agent->district }}" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" id="edit_agent_email" class="form-control" value="{{ $agent->email }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Commission Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" name="commission_rate" id="edit_agent_commission_rate" class="form-control" value="{{ $agent->commission_rate }}" step="0.5" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit_agent_status" class="form-select" required>
                                <option value="Active" {{ $agent->status == 'Active' ? 'selected' : '' }}>Active (सक्रिय)</option>
                                <option value="Inactive" {{ $agent->status == 'Inactive' ? 'selected' : '' }}>Inactive (निष्क्रिय)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">New Password (वैकल्पिक)</label>
                            <input type="password" name="password" id="edit_agent_password" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Office / Residential Address</label>
                        <textarea name="address" id="edit_agent_address" class="form-control" rows="2">{{ $agent->address }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Agent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Agent Login Credentials & WhatsApp Share Modal -->
<div class="modal fade" id="agentCredentialsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0">
                        <i class="fas fa-key me-2 text-warning"></i> कार्यकर्ता लॉगिन क्रेडेंशियल्स (Agent Portal Login)
                    </h5>
                    <small class="text-white-50">URL, Username और Password सीधे कार्यकर्ता को भेजें या साझा करें</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Agent Info Card -->
                <div class="p-3 bg-light rounded border mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-user-tie fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-heading" id="cred_agent_name">{{ $agent->name }}</h5>
                            <span class="badge bg-label-primary font-monospace" id="cred_agent_code">{{ $agent->agent_code }}</span>
                            <span class="text-muted small ms-2"><i class="fas fa-map-marker-alt text-danger me-1"></i><span id="cred_agent_district">{{ $agent->district }}</span></span>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success" id="cred_agent_status">{{ $agent->status }}</span>
                        <div class="text-muted small mt-1"><i class="fas fa-phone text-primary me-1"></i><strong id="cred_agent_mobile">{{ $agent->mobile }}</strong></div>
                    </div>
                </div>

                <!-- Credential Fields Grid -->
                <div class="row g-3 mb-4">
                    <!-- 1. Portal Login URL -->
                    <div class="col-12">
                        <label class="form-label fw-bold text-dark mb-1">
                            <i class="fas fa-globe text-primary me-1"></i> 1. पोर्टल लॉगिन लिंक (Login Portal URL)
                        </label>
                        <div class="input-group">
                            <input type="text" id="cred_portal_url" class="form-control font-monospace" value="{{ route('admin.login') }}" readonly>
                            <button type="button" class="btn btn-outline-primary" onclick="copyInput('cred_portal_url', 'पोर्टल लिंक कॉपी हो गया!')">
                                <i class="fas fa-copy me-1"></i> कॉपी लिंक
                            </button>
                            <a href="{{ route('admin.login') }}" target="_blank" class="btn btn-outline-secondary" title="Open Login Page">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>

                    <!-- 2. Username / Login ID -->
                    <div class="col-md-6 col-12">
                        <label class="form-label fw-bold text-dark mb-1">
                            <i class="fas fa-user text-primary me-1"></i> 2. यूजरनेम / लॉगिन ID (Mobile No. / Email)
                        </label>
                        <div class="input-group">
                            <input type="text" id="cred_username" class="form-control font-monospace fw-bold" readonly value="{{ $agent->mobile ?: ($agent->user ? $agent->user->phone : ($agent->email ?: $agent->agent_code)) }}">
                            <button type="button" class="btn btn-outline-primary" onclick="copyInput('cred_username', 'यूजरनेम कॉपी हो गया!')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <small class="text-muted">कार्यकर्ता अपने 10-अंकों के मोबाइल नंबर या ईमेल से लॉगिन कर सकते हैं।</small>
                    </div>

                    <!-- 3. Password Field & Quick Reset -->
                    <div class="col-md-6 col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold text-dark mb-0">
                                <i class="fas fa-lock text-warning me-1"></i> 3. पासवर्ड (Password)
                            </label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-primary fw-semibold" onclick="generateRandomPassword('cred_password')">
                                <i class="fas fa-magic me-1"></i> नया पासवर्ड बनाएं
                            </button>
                        </div>
                        <div class="input-group">
                            <input type="text" id="cred_password" class="form-control font-monospace fw-bold" placeholder="पासवर्ड दर्ज करें या जनरेट करें" oninput="updateLivePreview()">
                            <button type="button" class="btn btn-outline-primary" onclick="copyInput('cred_password', 'पासवर्ड कॉपी हो गया!')">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button type="button" class="btn btn-success" id="btnSaveCredPassword" onclick="saveAgentPassword()">
                                <i class="fas fa-save me-1"></i> सेव पासवर्ड
                            </button>
                        </div>
                        <div id="passwordSaveAlert" class="mt-1" style="display: none;"></div>
                    </div>
                </div>

                <!-- Formatted WhatsApp Message Preview Box -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-bold text-dark mb-0">
                            <i class="fab fa-whatsapp text-success me-1"></i> संदेश पूर्वावलोकन (WhatsApp Message Preview)
                        </label>
                        <button type="button" class="btn btn-link btn-sm p-0 text-secondary" onclick="copyTextarea('cred_message_preview', 'पूरा संदेश कॉपी हो गया!')">
                            <i class="fas fa-copy me-1"></i> संदेश कॉपी करें
                        </button>
                    </div>
                    <textarea id="cred_message_preview" class="form-control font-monospace text-dark" rows="7" style="background-color: #f8fafc; font-size: 13px; line-height: 1.45;" readonly></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> बंद करें
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-secondary" onclick="copyTextarea('cred_message_preview', 'संपूर्ण क्रेडेंशियल विवरण कॉपी हो गया!')">
                        <i class="fas fa-copy me-1"></i> पूरा विवरण कॉपी करें
                    </button>
                    <a href="#" id="btnSendWhatsAppCred" target="_blank" class="btn btn-success btn-lg shadow-sm" style="background-color: #25D366; border-color: #25D366;">
                        <i class="fab fa-whatsapp me-2 fs-5"></i> 📲 WhatsApp पर भेजें
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let currentCredAgent = @json($agent);

function openCredentialsModal(agent) {
    if (agent) currentCredAgent = agent;
    
    document.getElementById('cred_agent_name').innerText = currentCredAgent.name || 'Agent';
    document.getElementById('cred_agent_code').innerText = currentCredAgent.agent_code || '';
    document.getElementById('cred_agent_district').innerText = currentCredAgent.district || 'All Districts';
    document.getElementById('cred_agent_status').innerText = currentCredAgent.status || 'Active';
    document.getElementById('cred_agent_mobile').innerText = currentCredAgent.mobile || (currentCredAgent.user ? currentCredAgent.user.phone : '-');
    
    const loginUrl = "{{ route('admin.login') }}";
    document.getElementById('cred_portal_url').value = loginUrl;
    
    const username = currentCredAgent.mobile || (currentCredAgent.user ? currentCredAgent.user.phone : (currentCredAgent.email || currentCredAgent.agent_code));
    document.getElementById('cred_username').value = username;
    
    if (!document.getElementById('cred_password').value) {
        document.getElementById('cred_password').value = generatePassStr();
    }
    
    document.getElementById('passwordSaveAlert').style.display = 'none';

    updateLivePreview();

    const modal = new bootstrap.Modal(document.getElementById('agentCredentialsModal'));
    modal.show();
}

function generatePassStr() {
    const specials = ['@', '#', '$', '!'];
    const spec = specials[Math.floor(Math.random() * specials.length)];
    const num = Math.floor(1000 + Math.random() * 9000);
    return 'Shyam' + spec + num;
}

function generateRandomPassword(targetId) {
    const newPass = generatePassStr();
    const elem = document.getElementById(targetId);
    if (elem) {
        elem.value = newPass;
        elem.type = 'text';
    }
    if (targetId === 'cred_password') {
        updateLivePreview();
    }
}

function updateLivePreview() {
    if (!currentCredAgent) return;

    const loginUrl = "{{ route('admin.login') }}";
    const agentName = currentCredAgent.name || 'कार्यकर्ता';
    const agentCode = currentCredAgent.agent_code || '';
    const district = currentCredAgent.district || '';
    const mobile = currentCredAgent.mobile || (currentCredAgent.user ? currentCredAgent.user.phone : '');
    const username = document.getElementById('cred_username').value || mobile;
    const password = document.getElementById('cred_password').value || '(पंजीकृत पासवर्ड)';

    let cleanMobile = mobile.replace(/[^0-9]/g, '');
    if (cleanMobile.length === 10) {
        cleanMobile = '91' + cleanMobile;
    }

    const societyName = "श्री श्याम वेलफेयर सोसायटी लोहीड़ी";
    
    let msg = `🙏 *${societyName}* 🙏\n\n`;
    msg += `आदरणीय कार्यकर्ता *${agentName}* जी,\n`;
    msg += `सोसायटी एजेंट पोर्टल में आपका स्वागत है। आपके पोर्टल का लॉगिन विवरण निम्नलिखित है:\n\n`;
    msg += `🔗 *पोर्टल लॉगिन लिंक:* ${loginUrl}\n`;
    msg += `👤 *यूजरनेम (मोबाइल नंबर):* *${username}*\n`;
    msg += `🔑 *पासवर्ड (Password):* *${password}*\n`;
    msg += `🏢 *एजेंट कोड:* *${agentCode}*\n`;
    if (district) {
        msg += `📍 *कार्यक्षेत्र (जिला):* ${district}\n`;
    }
    msg += `\nकृपया ऊपर दिए गए लिंक पर जाकर अपने मोबाइल नंबर एवं पासवर्ड से लॉगिन करें और सदस्यों का रिकॉर्ड प्रबंधित करें।\n\n`;
    msg += `सहयोग/सहायता हेल्पलाइन: 9664090906`;

    document.getElementById('cred_message_preview').value = msg;

    const encodedMsg = encodeURIComponent(msg);
    const waUrl = `https://api.whatsapp.com/send?phone=${cleanMobile}&text=${encodedMsg}`;
    document.getElementById('btnSendWhatsAppCred').href = waUrl;
}

function copyInput(inputId, successMsg) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        showToast(successMsg || 'कॉपी हो गया!');
    });
}

function copyTextarea(textareaId, successMsg) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;
    textarea.select();
    navigator.clipboard.writeText(textarea.value).then(() => {
        showToast(successMsg || 'संदेश कॉपी हो गया!');
    });
}

function saveAgentPassword() {
    if (!currentCredAgent) return;
    const password = document.getElementById('cred_password').value;
    if (!password || password.length < 6) {
        alert('पासवर्ड कम से कम 6 अक्षरों का होना चाहिए!');
        return;
    }

    const btn = document.getElementById('btnSaveCredPassword');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> सेव हो रहा है...';

    fetch(`{{ url('admin/agents') }}/${currentCredAgent.id}/credentials`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            password: password,
            mobile: currentCredAgent.mobile,
            email: currentCredAgent.email
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        if (data.status === 'success') {
            const alertBox = document.getElementById('passwordSaveAlert');
            alertBox.style.display = 'block';
            alertBox.innerHTML = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> पासवर्ड डेटाबेस में सुरक्षित अपडेट हो गया!</span>';
            updateLivePreview();
            showToast('पासवर्ड सफलतापूर्वक अपडेट हो गया!');
        } else {
            alert('Error: ' + (data.message || 'Could not update password'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        console.error(err);
        alert('Server error while saving password.');
    });
}

function showToast(msg) {
    const toast = document.createElement('div');
    toast.className = 'position-fixed bottom-0 end-0 p-3';
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="toast show align-items-center text-white bg-dark border-0 shadow" role="alert">
            <div class="d-flex">
                <div class="toast-body fs-6">
                    <i class="fas fa-check-circle text-success me-2"></i> ${msg}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

function openEditAgentModal(agent) {
    document.getElementById('editAgentForm').action = "{{ url('admin/agents') }}/" + agent.id;
    document.getElementById('editAgentCodeBadge').innerText = agent.agent_code || '';
    document.getElementById('edit_agent_name').value = agent.name || '';
    document.getElementById('edit_agent_mobile').value = agent.mobile || (agent.user ? agent.user.phone : '');
    document.getElementById('edit_agent_district').value = agent.district || '';
    document.getElementById('edit_agent_email').value = agent.email || (agent.user ? agent.user.email : '');
    document.getElementById('edit_agent_commission_rate').value = agent.commission_rate || 5.0;
    document.getElementById('edit_agent_status').value = agent.status || 'Active';
    document.getElementById('edit_agent_password').value = '';
    document.getElementById('edit_agent_address').value = agent.address || '';

    const modal = new bootstrap.Modal(document.getElementById('editAgentModal'));
    modal.show();
}
</script>
@endsection

