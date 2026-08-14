@extends('admin.layouts.app')

@section('style')
<style>
    .wizard-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
    }
    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 3px;
        background: #E2E8F0;
        z-index: 1;
    }
    .wizard-step {
        position: relative;
        z-index: 2;
        text-align: center;
        background: #FFFFFF;
        padding: 0 10px;
        cursor: pointer;
    }
    .wizard-step-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #E2E8F0;
        color: #64748B;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .wizard-step.active .wizard-step-circle {
        background: #2563EB;
        color: #FFFFFF;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
    }
    .wizard-step.completed .wizard-step-circle {
        background: #16A34A;
        color: #FFFFFF;
    }
    .wizard-step-label {
        font-size: 0.82rem;
        font-weight: 500;
        color: #64748B;
    }
    .wizard-step.active .wizard-step-label {
        color: #2563EB;
        font-weight: 600;
    }
    .wizard-pane {
        display: none;
    }
    .wizard-pane.active {
        display: block;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">सदस्य पंजीकरण विज़ार्ड (5-Step Member Registration)</h4>
                    <p class="text-muted mb-0">Step-by-step registration wizard with auto age calculation, scheme age slab matching, and initial receipt generation.</p>
                </div>
                <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Directory
                </a>
            </div>
        </div>
    </div>

    <!-- Wizard Form Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <!-- Stepper Progress Bar -->
            <div class="wizard-steps">
                <div class="wizard-step active" id="stepIndicator1" onclick="goToStep(1)">
                    <div class="wizard-step-circle"><i class="fas fa-user"></i></div>
                    <div class="wizard-step-label">1. Primary Info</div>
                </div>
                <div class="wizard-step" id="stepIndicator2" onclick="goToStep(2)">
                    <div class="wizard-step-circle"><i class="fas fa-id-card"></i></div>
                    <div class="wizard-step-label">2. Documents</div>
                </div>
                <div class="wizard-step" id="stepIndicator3" onclick="goToStep(3)">
                    <div class="wizard-step-circle"><i class="fas fa-users-cog"></i></div>
                    <div class="wizard-step-label">3. Nominees (वारिसदार)</div>
                </div>
                <div class="wizard-step" id="stepIndicator4" onclick="goToStep(4)">
                    <div class="wizard-step-circle"><i class="fas fa-hand-holding-heart"></i></div>
                    <div class="wizard-step-label">4. Scheme & Slab</div>
                </div>
                <div class="wizard-step" id="stepIndicator5" onclick="goToStep(5)">
                    <div class="wizard-step-circle"><i class="fas fa-check-circle"></i></div>
                    <div class="wizard-step-label">5. Payment & Confirm</div>
                </div>
            </div>

            <form action="{{ route('admin.members.store') }}" method="POST" id="memberWizardForm">
                @csrf

                <!-- STEP 1: Basic Information -->
                <div class="wizard-pane active" id="stepPane1">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary">
                        <i class="fas fa-user me-2"></i> Step 1: Member Primary Details (प्राथमिक विवरण)
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Auto Membership Number</label>
                            <input type="text" name="membership_no" class="form-control bg-light" value="{{ $nextMemNum }}" readonly>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Full Name (सदस्य का पूरा नाम) <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="full_name" class="form-control" placeholder="e.g. Radheshyam Sharma" required>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Mobile Number (मोबाइल नंबर) <span class="text-danger">*</span></label>
                            <input type="tel" name="mobile" id="mobile" class="form-control" placeholder="10 digit mobile" maxlength="10" required>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Father / Spouse Name (पिता/पति का नाम)</label>
                            <input type="text" name="father_spouse_name" class="form-control" placeholder="e.g. S/o Bhagwan Das Sharma">
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Mother Name (माता का नाम)</label>
                            <input type="text" name="mother_name" class="form-control" placeholder="e.g. Shanti Devi">
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Gender (लिंग)</label>
                            <select name="gender" class="form-select">
                                <option value="Male">Male (पुरुष)</option>
                                <option value="Female">Female (महिला)</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Date of Birth (जन्म तिथि) <span class="text-danger">*</span></label>
                            <input type="date" name="dob" id="dob" class="form-control" value="1975-06-15" required onchange="calculateAgeAndSlab()">
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Calculated Age (आयु - वर्ष)</label>
                            <div class="input-group">
                                <input type="number" id="calculatedAge" class="form-control bg-light fw-bold text-primary" readonly value="51">
                                <span class="input-group-text">Years</span>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Gotra (गौत्र)</label>
                            <input type="text" name="gotra" class="form-control" placeholder="e.g. Kaushik, Vats, Garg...">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Residential Address (स्थायी पता)</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="House No, Ward, Village...">Ward No. 4, Village Lohki, Tehsil Narnaul</textarea>
                        </div>
                        <div class="col-md-3 col-12">
                            <label class="form-label fw-semibold">District (जिला)</label>
                            <input type="text" name="district" class="form-control" value="Mahendragarh">
                        </div>
                        <div class="col-md-3 col-12">
                            <label class="form-label fw-semibold">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="123001">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-primary px-4" onclick="goToStep(2)">
                            Next: Documents <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Documents -->
                <div class="wizard-pane" id="stepPane2">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary">
                        <i class="fas fa-id-card me-2"></i> Step 2: Identification & Document Details
                    </h5>
                    <div class="row g-4">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Aadhaar Card Number (आधार नंबर)</label>
                            <input type="text" name="aadhaar_no" class="form-control" placeholder="XXXX-XXXX-XXXX" value="XXXX-XXXX-8921">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Caste (जाति)</label>
                            <input type="text" name="caste" class="form-control" placeholder="e.g. Brahmin, Yadav, Saini..." value="Brahmin">
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="border border-dashed p-4 text-center rounded bg-lighter">
                                <i class="fas fa-camera fs-2 text-primary mb-2"></i>
                                <h6 class="fw-semibold mb-1">Member Passport Photo</h6>
                                <small class="text-muted d-block mb-2">JPG, PNG up to 2MB</small>
                                <input type="file" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="border border-dashed p-4 text-center rounded bg-lighter">
                                <i class="fas fa-file-pdf fs-2 text-warning mb-2"></i>
                                <h6 class="fw-semibold mb-1">Aadhaar / ID Card Copy</h6>
                                <small class="text-muted d-block mb-2">PDF, JPG up to 5MB</small>
                                <input type="file" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" onclick="goToStep(1)"><i class="fas fa-arrow-left me-1"></i> Back</button>
                        <button type="button" class="btn btn-primary px-4" onclick="goToStep(3)">Next: Nominees <i class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </div>

                <!-- STEP 3: Nominees -->
                <div class="wizard-pane" id="stepPane3">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary">
                        <i class="fas fa-users-cog me-2"></i> Step 3: Nominee Details (वारिसदार विवरण)
                    </h5>
                    <!-- Nominee 1 -->
                    <div class="card border mb-3 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user-shield me-1"></i> Primary Nominee 1 (मुख्य वारिसदार)</h6>
                            <div class="row g-3">
                                <div class="col-md-4 col-12">
                                    <label class="form-label fw-semibold">Nominee Name <span class="text-danger">*</span></label>
                                    <input type="text" name="nominee1_name" class="form-control" placeholder="e.g. Rameshwar Sharma" value="Shanti Devi Sharma">
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label fw-semibold">Relation (संबंध)</label>
                                    <select name="nominee1_relation" class="form-select">
                                        <option value="Spouse">Spouse (पति/पत्नी)</option>
                                        <option value="Son">Son (पुत्र)</option>
                                        <option value="Daughter">Daughter (पुत्री)</option>
                                        <option value="Father">Father (पिता)</option>
                                        <option value="Mother">Mother (माता)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label fw-semibold">Nominee Mobile</label>
                                    <input type="tel" name="nominee1_mobile" class="form-control" placeholder="10 digit mobile" value="9829011223">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nominee 2 -->
                    <div class="card border bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-user me-1"></i> Secondary Nominee 2 (द्वितीय वारिसदार - वैकल्पिक)</h6>
                            <div class="row g-3">
                                <div class="col-md-4 col-12">
                                    <label class="form-label fw-semibold">Nominee Name</label>
                                    <input type="text" name="nominee2_name" class="form-control" placeholder="e.g. Manoj Sharma" value="Manoj Sharma">
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label fw-semibold">Relation (संबंध)</label>
                                    <select name="nominee2_relation" class="form-select">
                                        <option value="Son" selected>Son (पुत्र)</option>
                                        <option value="Daughter">Daughter (पुत्री)</option>
                                        <option value="Brother">Brother (भाई)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label fw-semibold">Nominee Mobile</label>
                                    <input type="tel" name="nominee2_mobile" class="form-control" placeholder="10 digit mobile" value="9812044556">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" onclick="goToStep(2)"><i class="fas fa-arrow-left me-1"></i> Back</button>
                        <button type="button" class="btn btn-primary px-4" onclick="goToStep(4)">Next: Scheme & Slab <i class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </div>

                <!-- STEP 4: Scheme & Slab -->
                <div class="wizard-pane" id="stepPane4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary">
                        <i class="fas fa-hand-holding-heart me-2"></i> Step 4: Scheme Enrolment & Dynamic Age Slab
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Select Society Scheme (योजना का चयन करें) <span class="text-danger">*</span></label>
                            <select name="scheme_id" id="schemeSelect" class="form-select" required onchange="calculateAgeAndSlab()">
                                @foreach($schemes as $sch)
                                <option value="{{ $sch->id }}" data-code="{{ $sch->code }}">{{ $sch->name_hindi }} ({{ $sch->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Assigned Agent (आवंटित एजेंट) <span class="text-danger">*</span></label>
                            <select name="agent_id" class="form-select" required>
                                @foreach($agents as $agt)
                                <option value="{{ $agt->id }}">{{ $agt->name }} ({{ $agt->agent_code }} - {{ $agt->district }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Auto Determined Slab Details Card -->
                        <div class="col-12">
                            <div class="card border border-primary bg-lighter mt-2">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="fas fa-calculator me-1"></i> Auto Determined Scheme Amounts (आयु वर्ग के अनुसार निर्धारित शुल्क)
                                    </h6>
                                    <div class="row g-3 text-center">
                                        <div class="col-md-4 col-12">
                                            <div class="bg-white p-3 rounded border">
                                                <small class="text-muted d-block">Applicable Age Slab</small>
                                                <span class="fs-5 fw-bold text-heading" id="slabLabel">41 – 60 Years (SLAB-S2)</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-12">
                                            <div class="bg-white p-3 rounded border">
                                                <small class="text-muted d-block">Initial Joining Amount</small>
                                                <span class="fs-4 fw-bold text-success" id="joiningAmountDisplay">₹1,500</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-12">
                                            <div class="bg-white p-3 rounded border">
                                                <small class="text-muted d-block">Monthly Support Amount</small>
                                                <span class="fs-4 fw-bold text-primary" id="supportAmountDisplay">₹300 / mo</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" onclick="goToStep(3)"><i class="fas fa-arrow-left me-1"></i> Back</button>
                        <button type="button" class="btn btn-primary px-4" onclick="goToStep(5)">Next: Payment & Confirm <i class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </div>

                <!-- STEP 5: Payment & Confirm -->
                <div class="wizard-pane" id="stepPane5">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary">
                        <i class="fas fa-check-circle me-2"></i> Step 5: Initial Payment & Enrolment Confirmation
                    </h5>
                    <div class="row g-4">
                        <div class="col-md-6 col-12">
                            <div class="card border p-3">
                                <h6 class="fw-bold mb-3">Payment Collection Details</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Payment Mode</label>
                                    <select name="payment_mode" class="form-select">
                                        <option value="UPI">UPI (PhonePe, GPay, Paytm)</option>
                                        <option value="Cash">Cash (नकद)</option>
                                        <option value="Bank Transfer">Bank Transfer (NEFT/IMPS)</option>
                                        <option value="Cheque">Cheque (चेक)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Transaction / UTR Reference No</label>
                                    <input type="text" name="reference_no" class="form-control" placeholder="e.g. UPI8723910293">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Enrolment Date</label>
                                    <input type="date" name="joining_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="card border border-success bg-lighter p-4 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-success mb-3"><i class="fas fa-receipt me-1"></i> Summary of Enrolment</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li class="d-flex justify-content-between py-1 border-bottom">
                                            <span class="text-muted">Society Registration Fee:</span>
                                            <strong class="text-success" id="summaryJoining">₹1,500</strong>
                                        </li>
                                        <li class="d-flex justify-content-between py-1 border-bottom">
                                            <span class="text-muted">Monthly Recurring Support:</span>
                                            <strong class="text-primary" id="summarySupport">₹300 / mo</strong>
                                        </li>
                                        <li class="d-flex justify-content-between py-1 border-bottom">
                                            <span class="text-muted">Official Society Receipt:</span>
                                            <span class="badge bg-success">Auto-Generated</span>
                                        </li>
                                        <li class="d-flex justify-content-between py-1">
                                            <span class="text-muted">Membership Certificate:</span>
                                            <span class="badge bg-warning">Gold-Border Ready</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-success btn-lg w-100 shadow">
                                        <i class="fas fa-check me-2"></i> Submit & Complete Registration
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-start mt-4">
                        <button type="button" class="btn btn-outline-secondary" onclick="goToStep(4)"><i class="fas fa-arrow-left me-1"></i> Back</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function goToStep(stepNumber) {
    for (let i = 1; i <= 5; i++) {
        const pane = document.getElementById('stepPane' + i);
        const ind = document.getElementById('stepIndicator' + i);
        if (pane) pane.classList.remove('active');
        if (ind) {
            ind.classList.remove('active');
            if (i < stepNumber) ind.classList.add('completed');
            else ind.classList.remove('completed');
        }
    }
    const targetPane = document.getElementById('stepPane' + stepNumber);
    const targetInd = document.getElementById('stepIndicator' + stepNumber);
    if (targetPane) targetPane.classList.add('active');
    if (targetInd) targetInd.classList.add('active');
}

function calculateAgeAndSlab() {
    const dobInput = document.getElementById('dob').value;
    if (!dobInput) return;

    const dob = new Date(dobInput);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    if (age < 0) age = 0;

    document.getElementById('calculatedAge').value = age;

    const schemeSelect = document.getElementById('schemeSelect');
    const schemeId = schemeSelect.value;

    fetch("{{ route('admin.api.slab-by-age') }}?scheme_id=" + schemeId + "&age=" + age)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.slab) {
                const slab = data.slab;
                document.getElementById('slabLabel').innerText = slab.min_age + ' – ' + slab.max_age + ' Years (' + slab.slab_code + ')';
                document.getElementById('joiningAmountDisplay').innerText = '₹' + Number(slab.joining_amount).toLocaleString('en-IN');
                document.getElementById('supportAmountDisplay').innerText = '₹' + Number(slab.support_amount).toLocaleString('en-IN') + ' / mo';
                document.getElementById('summaryJoining').innerText = '₹' + Number(slab.joining_amount).toLocaleString('en-IN');
                document.getElementById('summarySupport').innerText = '₹' + Number(slab.support_amount).toLocaleString('en-IN') + ' / mo';
            }
        })
        .catch(err => console.log(err));
}

document.addEventListener("DOMContentLoaded", function () {
    calculateAgeAndSlab();
});
</script>
@endsection
