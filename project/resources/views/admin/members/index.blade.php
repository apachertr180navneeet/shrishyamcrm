@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">सदस्य निर्देशिका (Society Members Directory)</h4>
                    <p class="text-muted mb-0">Total {{ $members->total() }} registered society members across all schemes and agent districts.</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#importMembersModal">
                        <i class="fas fa-file-import me-1"></i> बल्क इम्पोर्ट (Import Members)
                    </button>
                    <a href="{{ route('admin.members.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-user-plus me-1"></i> Add New Member
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-exclamation-triangle fs-4 text-warning me-3 mt-1"></i>
            <div class="flex-grow-1">
                <h6 class="alert-heading fw-bold mb-1">इम्पोर्ट सूचना (Import Notice) - {{ count(session('import_errors')) }} पंक्तियां अस्वीकृत (Rows Skipped)</h6>
                <p class="mb-2 small">निम्नलिखित पंक्तियों में अमान्य डेटा के कारण उन्हें इम्पोर्ट नहीं किया जा सका (The following rows could not be imported):</p>
                <div class="bg-white p-3 rounded border" style="max-height: 180px; overflow-y: auto;">
                    <ul class="mb-0 ps-3 small text-danger">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.members.index') }}" method="GET" class="row g-3">
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, member no, mobile, aadhaar..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="{{ (auth()->check() && auth()->user()->isAgent() && auth()->user()->agent_id) ? 'col-lg-5' : 'col-lg-3' }} col-md-6 col-12">
                    <select name="scheme_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Schemes (सभी योजनाएं)</option>
                        @foreach($schemes as $sch)
                        <option value="{{ $sch->id }}" {{ request('scheme_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name_hindi }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!(auth()->check() && auth()->user()->isAgent() && auth()->user()->agent_id))
                <div class="col-lg-3 col-md-6 col-12">
                    <select name="agent_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Agents (सभी एजेंट)</option>
                        @foreach($agents as $agt)
                        <option value="{{ $agt->id }}" {{ request('agent_id') == $agt->id ? 'selected' : '' }}>{{ $agt->name }} ({{ $agt->agent_code }})</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-lg-2 col-md-6 col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Members Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Member No</th>
                        <th>Member Name & Details</th>
                        <th>Scheme</th>
                        <th>Age / DOB</th>
                        <th>Agent</th>
                        <th>सहयोग दर (प्रति कार्यक्रम)</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $m)
                    <tr>
                        <td>
                            <a href="{{ route('admin.members.show', $m->id) }}" class="fw-bold text-primary">
                                {{ $m->membership_no }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center overflow-hidden" style="width: 38px; height: 38px; flex-shrink: 0;">
                                    @if(!empty($m->photo_src))
                                        <img src="{{ $m->photo_src }}" alt="{{ $m->full_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <i class="fas {{ $m->gender == 'Female' ? 'fa-female' : 'fa-user' }}"></i>
                                    @endif
                                </div>
                                <div>
                                    <strong class="d-block text-heading">{{ $m->full_name }}</strong>
                                    <small class="text-muted"><i class="fas fa-phone-alt me-1" style="font-size: 10px;"></i>{{ $m->mobile }} | Gotra: {{ $m->gotra ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $m->scheme && $m->scheme->code == 'SENIOR' ? 'bg-label-primary' : 'bg-label-warning' }}">
                                {{ $m->scheme ? $m->scheme->name_hindi : 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $m->age }} Yrs</span>
                            <small class="d-block text-muted">{{ $m->dob ? $m->dob->format('d M Y') : '' }}</small>
                        </td>
                        <td>
                            <span>{{ $m->agent ? $m->agent->name : 'HQ Direct' }}</span>
                            <small class="d-block text-muted">{{ $m->agent ? $m->agent->district : '' }}</small>
                        </td>
                        <td>
                            <strong class="text-success">₹{{ number_format($m->monthly_support_amount) }}/कार्यक्रम</strong>
                            @if($m->pending_amount > 0)
                                <small class="d-block text-danger"><i class="fas fa-exclamation-circle me-1"></i>Due: ₹{{ number_format($m->pending_amount) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($m->status == 'Active')
                                <span class="badge bg-success">Active</span>
                            @elseif($m->status == 'Inactive')
                                <span class="badge bg-danger">Inactive</span>
                            @else
                                <span class="badge bg-secondary">{{ $m->status }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.members.show', $m->id) }}" class="btn btn-outline-primary" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.members.edit', $m->id) }}" class="btn btn-outline-info" title="Edit Member">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.members.ledger.pdf', $m->id) }}" class="btn btn-outline-danger" title="Download Ledger Card PDF" target="_blank">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                                <a href="{{ route('admin.certificates.show', $m->id) }}" class="btn btn-outline-warning" title="Certificate" target="_blank">
                                    <i class="fas fa-certificate"></i>
                                </a>
                                <a href="{{ route('admin.payments.create', ['member_id' => $m->id]) }}" class="btn btn-outline-success" title="Record Payment">
                                    <i class="fas fa-rupee-sign"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-users-slash fs-1 d-block mb-2"></i>
                            No members found matching your search filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3">
            {{ $members->links() }}
        </div>
    </div>
</div>

<!-- Import Members Modal -->
<div class="modal fade" id="importMembersModal" tabindex="-1" aria-labelledby="importMembersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white py-3 px-4" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <h5 class="modal-title fw-bold text-white mb-0" id="importMembersModalLabel">
                    <i class="fas fa-file-excel me-2"></i> सदस्य बल्क इम्पोर्ट एवं टेम्पलेट डाउनलोड (Import Members & Download Template)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Section 1: Template Download by Selected Scheme -->
                <div class="card bg-light border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary rounded-pill me-2 px-2 py-1">1</span>
                            <h6 class="fw-bold mb-0 text-heading">चरण 1: योजना अनुसार टेम्पलेट डाउनलोड करें (Download CSV Template by Scheme)</h6>
                        </div>
                        <p class="text-muted small mb-3">
                            कृपया जिस योजना (Scheme) में नए सदस्यों को जोड़ना चाहते हैं, उसे चुनें और टेम्पलेट डाउनलोड करें। टेम्पलेट में चयनित योजना की उपयुक्त आयु सीमा (Age Slabs) और शुल्क संरचना पहले से शामिल रहेगी।
                        </p>
                        
                        <div class="row g-2 align-items-end">
                            <div class="col-md-8 col-12">
                                <label for="templateSchemeSelect" class="form-label fw-semibold small text-muted">योजना चुनें (Select Scheme):</label>
                                <select id="templateSchemeSelect" class="form-select">
                                    <option value="">-- All Schemes (सभी योजनाओं का सामान्य टेम्पलेट) --</option>
                                    @foreach($schemes as $sch)
                                    <option value="{{ $sch->id }}" 
                                            data-code="{{ $sch->code }}"
                                            data-slabs="{{ $sch->ageSlabs->map(fn($sl) => $sl->min_age . '-' . $sl->max_age . ' वर्ष (प्रवेश: ₹' . number_format($sl->joining_amount) . ', सहयोग: ₹' . number_format($sl->support_amount) . ')')->implode(' | ') }}"
                                            {{ request('scheme_id') == $sch->id ? 'selected' : '' }}>
                                        {{ $sch->name_hindi }} ({{ $sch->code }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 col-12">
                                <a id="btnDownloadTemplate" href="{{ route('admin.members.template') }}" class="btn btn-outline-success w-100 fw-semibold">
                                    <i class="fas fa-download me-1"></i> टेम्पलेट डाउनलोड करें
                                </a>
                            </div>
                        </div>

                        <!-- Scheme Slabs Preview Badge -->
                        <div id="schemeSlabInfo" class="mt-3 p-2 bg-white rounded border border-success border-opacity-25 small" style="display: none;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                <div>
                                    <strong class="text-success d-block mb-1">चयनित योजना की सक्रिय आयु सीमाएं (Active Age Slabs):</strong>
                                    <span id="schemeSlabText" class="text-muted"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Upload CSV and Import -->
                <div class="card border border-primary border-opacity-25" style="border-radius: 12px;">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary rounded-pill me-2 px-2 py-1">2</span>
                            <h6 class="fw-bold mb-0 text-heading">चरण 2: भरी हुई CSV फ़ाइल अपलोड करें (Upload Completed CSV)</h6>
                        </div>
                        <p class="text-muted small mb-3">
                            टेम्पलेट में सदस्यों का विवरण भरें और यहाँ अपलोड करें। सदस्यता संख्या एवं प्रमाण पत्र संख्या स्वतः जनरेट हो जाएगी।
                        </p>

                        <form action="{{ route('admin.members.import') }}" method="POST" enctype="multipart/form-data" id="memberImportForm">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label for="importSchemeSelect" class="form-label fw-semibold small">इम्पोर्ट योजना (Apply Scheme to Import):</label>
                                    <select name="scheme_id" id="importSchemeSelect" class="form-select">
                                        <option value="">-- Auto-detect from CSV (फ़ाइल के अनुसार) --</option>
                                        @foreach($schemes as $sch)
                                        <option value="{{ $sch->id }}" {{ request('scheme_id') == $sch->id ? 'selected' : '' }}>
                                            {{ $sch->name_hindi }} ({{ $sch->code }})
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text small">यदि आप सभी सदस्यों को एक ही योजना में जोड़ना चाहते हैं, तो यहाँ योजना चुनें।</div>
                                </div>

                                @if(!(auth()->check() && auth()->user()->isAgent() && auth()->user()->agent_id))
                                <div class="col-md-6 col-12">
                                    <label for="defaultAgentSelect" class="form-label fw-semibold small">डिफ़ॉल्ट एजेंट (Default Agent):</label>
                                    <select name="default_agent_id" id="defaultAgentSelect" class="form-select">
                                        <option value="">-- Detect from CSV or Direct HQ --</option>
                                        @foreach($agents as $agt)
                                        <option value="{{ $agt->id }}">{{ $agt->name }} ({{ $agt->agent_code }})</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text small">यदि CSV में एजेंट कोड खाली है, तो यह एजेंट लागू होगा।</div>
                                </div>
                                @endif

                                <div class="col-12">
                                    <label for="csvFileInput" class="form-label fw-semibold small">CSV फ़ाइल चुनें (Choose CSV File): <span class="text-danger">*</span></label>
                                    <input type="file" name="csv_file" id="csvFileInput" class="form-control" accept=".csv, text/csv, application/vnd.ms-excel" required>
                                    <div class="form-text small">केवल <code>.csv</code> प्रारूप मान्य है। अधिकतम आकार: 10MB</div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Guidelines Notice Box -->
                <div class="alert alert-info border-0 mt-3 p-3 mb-0 small" style="border-radius: 10px;">
                    <div class="fw-bold mb-1 text-primary"><i class="fas fa-info-circle me-1"></i> मुख्य दिशानिर्देश (Important Guidelines):</div>
                    <ul class="mb-0 ps-3">
                        <li><strong>पूरा नाम (Full Name)</strong> और <strong>मोबाइल नंबर (Mobile)</strong> अनिवार्य हैं।</li>
                        <li><strong>जन्म तिथि (DOB)</strong> प्रारूप: <code>YYYY-MM-DD</code> या <code>DD-MM-YYYY</code> (जैसे 1980-05-15)। यदि जन्म तिथि न हो तो <strong>Age (आयु)</strong> भरें।</li>
                        <li><strong>सदस्यता संख्या (Membership No)</strong> खाली रखने पर सिस्टम स्वचालित क्रमिक संख्या प्रदान करेगा।</li>
                        <li>प्रवेश शुल्क (Joining Fee) एवं सहयोग राशि आयु सीमा (Age Slab) के अनुसार स्वतः निर्धारित होगी।</li>
                        <li>नॉमिनी 1 एवं नॉमिनी 2 का नाम, संबंध और हिस्सा % भी CSV में शामिल किया जा सकता है।</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer px-4 py-3 bg-light" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">रद्द करें (Cancel)</button>
                <button type="submit" form="memberImportForm" class="btn btn-primary" id="btnSubmitImport">
                    <i class="fas fa-file-upload me-1"></i> फ़ाइल अपलोड और इम्पोर्ट करें (Import Now)
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const schemeSelect = document.getElementById('templateSchemeSelect');
    const btnDownload = document.getElementById('btnDownloadTemplate');
    const infoDiv = document.getElementById('schemeSlabInfo');
    const infoText = document.getElementById('schemeSlabText');
    const importSchemeSelect = document.getElementById('importSchemeSelect');
    const importForm = document.getElementById('memberImportForm');
    const btnSubmit = document.getElementById('btnSubmitImport');

    function syncSchemeSelection() {
        if (!schemeSelect || !btnDownload) return;
        const schemeId = schemeSelect.value;
        const baseUrl = "{{ route('admin.members.template') }}";
        btnDownload.href = schemeId ? `${baseUrl}?scheme_id=${encodeURIComponent(schemeId)}` : baseUrl;

        const selectedOption = schemeSelect.options[schemeSelect.selectedIndex];
        const slabs = selectedOption ? selectedOption.getAttribute('data-slabs') : null;

        if (slabs && infoDiv && infoText) {
            infoText.textContent = slabs;
            infoDiv.style.display = 'block';
        } else if (infoDiv) {
            infoDiv.style.display = 'none';
        }

        // Auto-sync into import scheme dropdown
        if (importSchemeSelect && schemeId) {
            importSchemeSelect.value = schemeId;
        }
    }

    if (schemeSelect) {
        schemeSelect.addEventListener('change', syncSchemeSelection);
        // Initial sync
        syncSchemeSelection();
    }

    if (importForm && btnSubmit) {
        importForm.addEventListener('submit', function () {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>इम्पोर्ट जारी है (Importing)...';
        });
    }
});
</script>
@endsection
