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
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">सदस्य पंजीकरण (Add New Member Registration)</h4>
                    <p class="text-muted mb-0">Enroll new society member, auto-calculate scheme age slab, add dual nominees, and record initial fees.</p>
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

            <form action="{{ route('admin.members.store') }}" method="POST" id="memberWizardForm" enctype="multipart/form-data">
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
                            <input type="tel" name="mobile" id="mobile" class="form-control" placeholder="10 digit mobile" maxlength="10" value="{{ old('mobile') }}" required>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Father / Spouse Name (पिता/पति का नाम)</label>
                            <input type="text" name="father_spouse_name" class="form-control" placeholder="e.g. S/o Bhagwan Das Sharma" value="{{ old('father_spouse_name') }}">
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Mother Name (माता का नाम)</label>
                            <input type="text" name="mother_name" class="form-control" placeholder="e.g. Shanti Devi" value="{{ old('mother_name') }}">
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Gender (लिंग)</label>
                            <select name="gender" class="form-select">
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male (पुरुष)</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female (महिला)</option>
                                <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Date of Birth (जन्म तिथि) <span class="text-danger">*</span></label>
                            <input type="date" name="dob" id="dob" class="form-control" value="{{ old('dob') }}" required onchange="calculateAgeAndSlab()">
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Calculated Age (आयु - वर्ष)</label>
                            <div class="input-group">
                                <input type="number" id="calculatedAge" class="form-control bg-light fw-bold text-primary" readonly value="{{ old('calculated_age') }}" placeholder="Age">
                                <span class="input-group-text">Years</span>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Gotra (गौत्र)</label>
                            <input type="text" name="gotra" class="form-control" placeholder="e.g. Kaushik, Vats, Garg..." value="{{ old('gotra') }}">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Residential Address (स्थायी पता)</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="House No, Ward, Village...">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-md-3 col-12">
                            <label class="form-label fw-semibold">District (जिला)</label>
                            <input type="text" name="district" class="form-control" value="{{ old('district', 'Mahendragarh') }}">
                        </div>
                        <div class="col-md-3 col-12">
                            <label class="form-label fw-semibold">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="{{ old('pincode', '123001') }}">
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
                            <input type="text" name="aadhaar_no" class="form-control" placeholder="XXXX-XXXX-XXXX" value="{{ old('aadhaar_no') }}">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Caste (जाति)</label>
                            <input type="text" name="caste" class="form-control" placeholder="e.g. Brahmin, Yadav, Saini..." value="{{ old('caste') }}">
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="border border-dashed p-4 text-center rounded bg-lighter">
                                <i class="fas fa-camera fs-2 text-primary mb-2"></i>
                                <h6 class="fw-semibold mb-1">Member Passport Photo</h6>
                                <small class="text-muted d-block mb-2">JPG, PNG up to 2MB</small>
                                <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="border border-dashed p-4 text-center rounded bg-lighter">
                                <i class="fas fa-file-pdf fs-2 text-warning mb-2"></i>
                                <h6 class="fw-semibold mb-1">Aadhaar / ID Card Copy</h6>
                                <small class="text-muted d-block mb-2">PDF, JPG up to 5MB</small>
                                <input type="file" name="aadhaar_doc" class="form-control form-control-sm" accept=".pdf,image/*">
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
                                    <input type="text" name="nominee1_name" class="form-control" placeholder="e.g. Rameshwar Sharma" value="{{ old('nominee1_name') }}">
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label fw-semibold">Relation (संबंध)</label>
                                    <select name="nominee1_relation" class="form-select">
                                        <option value="Spouse" {{ old('nominee1_relation') == 'Spouse' ? 'selected' : '' }}>Spouse (पति/पत्नी)</option>
                                        <option value="Son" {{ old('nominee1_relation') == 'Son' ? 'selected' : '' }}>Son (पुत्र)</option>
                                        <option value="Daughter" {{ old('nominee1_relation') == 'Daughter' ? 'selected' : '' }}>Daughter (पुत्री)</option>
                                        <option value="Father" {{ old('nominee1_relation') == 'Father' ? 'selected' : '' }}>Father (पिता)</option>
                                        <option value="Mother" {{ old('nominee1_relation') == 'Mother' ? 'selected' : '' }}>Mother (माता)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label fw-semibold">Nominee Mobile</label>
                                    <input type="tel" name="nominee1_mobile" class="form-control" placeholder="10 digit mobile" value="{{ old('nominee1_mobile') }}">
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
                                    <input type="text" name="nominee2_name" class="form-control" placeholder="e.g. Manoj Sharma" value="{{ old('nominee2_name') }}">
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label fw-semibold">Relation (संबंध)</label>
                                    <select name="nominee2_relation" class="form-select">
                                        <option value="Son" {{ old('nominee2_relation') == 'Son' ? 'selected' : '' }}>Son (पुत्र)</option>
                                        <option value="Daughter" {{ old('nominee2_relation') == 'Daughter' ? 'selected' : '' }}>Daughter (पुत्री)</option>
                                        <option value="Brother" {{ old('nominee2_relation') == 'Brother' ? 'selected' : '' }}>Brother (भाई)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label fw-semibold">Nominee Mobile</label>
                                    <input type="tel" name="nominee2_mobile" class="form-control" placeholder="10 digit mobile" value="{{ old('nominee2_mobile') }}">
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
                            <select name="scheme_id" id="schemeSelect" class="form-select" required onchange="onSchemeChange()">
                                <option value="" selected disabled>-- योजना का चयन करें (Select Scheme) --</option>
                                @forelse($schemes as $sch)
                                <option value="{{ $sch->id }}" data-code="{{ $sch->code }}">{{ $sch->name_hindi ?? $sch->name }} ({{ $sch->name }})</option>
                                @empty
                                <option value="" disabled>-- No Active Schemes Available --</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Assigned Agent (आवंटित एजेंट) <span class="text-danger">*</span></label>
                            @if(auth()->check() && auth()->user()->isAgent() && auth()->user()->agent_id)
                                @php $currentAgent = $agents->first(); @endphp
                                <input type="hidden" name="agent_id" value="{{ auth()->user()->agent_id }}">
                                <input type="text" class="form-control bg-light fw-semibold" value="{{ $currentAgent ? $currentAgent->name . ' (' . $currentAgent->agent_code . ' - ' . $currentAgent->district . ')' : 'Assigned to your agent profile' }}" readonly>
                            @else
                                <select name="agent_id" id="agentSelect" class="form-select" required>
                                    <option value="" selected disabled>-- एजेंट का चयन करें (Select Agent) --</option>
                                    @forelse($agents as $agt)
                                    <option value="{{ $agt->id }}">{{ $agt->name }} ({{ $agt->agent_code }} - {{ $agt->district }})</option>
                                    @empty
                                    <option value="" disabled>-- No Active Agents Available --</option>
                                    @endforelse
                                </select>
                            @endif
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Select Scheme Age Slab (आयु वर्ग का चयन करें) <span class="text-danger">*</span></label>
                            <select name="age_slab_id" id="ageSlabSelect" class="form-select" required onchange="onAgeSlabChange()" disabled>
                                <option value="" selected disabled>-- पहले योजना का चयन करें (Select Scheme First) --</option>
                            </select>
                            <input type="hidden" name="joining_amount" id="joiningAmountInput" value="">
                            <input type="hidden" name="monthly_support_amount" id="supportAmountInput" value="">
                        </div>

                        <div class="col-md-6 col-12 d-flex align-items-end">
                            <div class="alert alert-light border py-2 px-3 mb-0 w-100 text-muted small" id="slabHint">
                                <i class="fas fa-info-circle text-primary me-1"></i> योजना चयन के बाद आयु वर्ग लोड होगा एवं जन्मतिथि अनुसार स्वतः चयनित होगा।
                            </div>
                        </div>

                        <!-- Auto Determined Slab Details Card -->
                        <div class="col-12">
                            <div class="card border border-primary bg-lighter mt-2">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="fas fa-calculator me-1"></i> Determined Scheme Amounts (आयु वर्ग के अनुसार निर्धारित शुल्क)
                                    </h6>
                                    <div class="row g-3 text-center">
                                        <div class="col-md-4 col-12">
                                            <div class="bg-white p-3 rounded border">
                                                <small class="text-muted d-block">Applicable Age Slab</small>
                                                <span class="fs-5 fw-bold text-heading" id="slabLabel">--</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-12">
                                            <div class="bg-white p-3 rounded border">
                                                <small class="text-muted d-block">Initial Joining Amount</small>
                                                <span class="fs-4 fw-bold text-success" id="joiningAmountDisplay">₹0</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-12">
                                            <div class="bg-white p-3 rounded border">
                                                <small class="text-muted d-block">Monthly Support Amount</small>
                                                <span class="fs-4 fw-bold text-primary" id="supportAmountDisplay">₹0 / mo</span>
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
                                             <strong class="text-success" id="summaryJoining">₹0</strong>
                                        </li>
                                        <li class="d-flex justify-content-between py-1 border-bottom">
                                             <span class="text-muted">Monthly Recurring Support:</span>
                                             <strong class="text-primary" id="summarySupport">₹0 / mo</strong>
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
const schemesData = @json($schemes);
let currentStep = 1;

function goToStep(stepNumber) {
    if (stepNumber > currentStep) {
        // Validate inputs in the current active pane before moving forward
        const currentPane = document.getElementById('stepPane' + currentStep);
        if (currentPane) {
            const inputs = currentPane.querySelectorAll('input, select, textarea');
            for (let input of inputs) {
                if (!input.checkValidity()) {
                    input.reportValidity();
                    return false;
                }
            }
        }
    }

    currentStep = stepNumber;
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

function calculateAge() {
    const dobInput = document.getElementById('dob').value;
    if (!dobInput) return null;

    const dob = new Date(dobInput);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    if (age < 0) age = 0;

    const ageEl = document.getElementById('calculatedAge');
    if (ageEl) ageEl.value = age;
    return age;
}

function calculateAgeAndSlab() {
    calculateAge();
    const schemeSelect = document.getElementById('schemeSelect');
    if (schemeSelect && schemeSelect.value) {
        onSchemeChange();
    }
}

function onSchemeChange() {
    const schemeSelect = document.getElementById('schemeSelect');
    const slabSelect = document.getElementById('ageSlabSelect');
    const schemeId = schemeSelect ? parseInt(schemeSelect.value) : null;

    if (!schemeId) {
        slabSelect.innerHTML = '<option value="" selected disabled>-- पहले योजना का चयन करें (Select Scheme First) --</option>';
        slabSelect.disabled = true;
        resetSlabDisplay();
        return;
    }

    const selectedScheme = schemesData.find(s => s.id === schemeId);
    const slabs = selectedScheme && (selectedScheme.age_slabs || selectedScheme.ageSlabs) ? (selectedScheme.age_slabs || selectedScheme.ageSlabs) : [];

    slabSelect.innerHTML = '<option value="" selected disabled>-- आयु वर्ग का चयन करें (Select Age Slab) --</option>';
    slabSelect.disabled = false;

    if (slabs.length === 0) {
        slabSelect.innerHTML = '<option value="" disabled selected>-- No Age Slabs Configured for this Scheme --</option>';
        resetSlabDisplay();
        return;
    }

    const memberAge = calculateAge();
    let autoMatchedSlabId = null;

    slabs.forEach(sl => {
        const opt = document.createElement('option');
        opt.value = sl.id;
        opt.setAttribute('data-min', sl.min_age);
        opt.setAttribute('data-max', sl.max_age);
        opt.setAttribute('data-joining', sl.joining_amount);
        opt.setAttribute('data-support', sl.support_amount);
        opt.setAttribute('data-code', sl.slab_code || '');
        opt.innerText = `${sl.min_age} – ${sl.max_age} Years (${sl.slab_code || 'SLAB'})`;

        if (memberAge !== null && memberAge >= sl.min_age && memberAge <= sl.max_age) {
            autoMatchedSlabId = sl.id;
        }

        slabSelect.appendChild(opt);
    });

    if (autoMatchedSlabId) {
        slabSelect.value = autoMatchedSlabId;
    } else if (slabs.length > 0) {
        slabSelect.value = slabs[0].id;
    }

    onAgeSlabChange();
}

function onAgeSlabChange() {
    const slabSelect = document.getElementById('ageSlabSelect');
    if (!slabSelect || !slabSelect.value) {
        resetSlabDisplay();
        return;
    }

    const selectedOption = slabSelect.options[slabSelect.selectedIndex];
    if (!selectedOption || !selectedOption.value) {
        resetSlabDisplay();
        return;
    }

    const minAge = selectedOption.getAttribute('data-min');
    const maxAge = selectedOption.getAttribute('data-max');
    const code = selectedOption.getAttribute('data-code');
    const joining = Number(selectedOption.getAttribute('data-joining') || 0);
    const support = Number(selectedOption.getAttribute('data-support') || 0);

    const slabText = `${minAge} – ${maxAge} Years` + (code ? ` (${code})` : '');

    document.getElementById('slabLabel').innerText = slabText;
    document.getElementById('joiningAmountDisplay').innerText = '₹' + joining.toLocaleString('en-IN');
    document.getElementById('supportAmountDisplay').innerText = '₹' + support.toLocaleString('en-IN') + ' / mo';

    document.getElementById('joiningAmountInput').value = joining;
    document.getElementById('supportAmountInput').value = support;

    document.getElementById('summaryJoining').innerText = '₹' + joining.toLocaleString('en-IN');
    document.getElementById('summarySupport').innerText = '₹' + support.toLocaleString('en-IN') + ' / mo';
}

function resetSlabDisplay() {
    document.getElementById('slabLabel').innerText = '--';
    document.getElementById('joiningAmountDisplay').innerText = '₹0';
    document.getElementById('supportAmountDisplay').innerText = '₹0 / mo';
    document.getElementById('joiningAmountInput').value = '';
    document.getElementById('supportAmountInput').value = '';
    document.getElementById('summaryJoining').innerText = '₹0';
    document.getElementById('summarySupport').innerText = '₹0 / mo';
}

document.addEventListener("DOMContentLoaded", function () {
    const schemeSelect = document.getElementById('schemeSelect');
    if (schemeSelect && schemeSelect.value) {
        onSchemeChange();
    }

    const form = document.getElementById('memberWizardForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const inputs = this.querySelectorAll('input, select, textarea');
            for (let input of inputs) {
                if (!input.checkValidity()) {
                    e.preventDefault();
                    const pane = input.closest('.wizard-pane');
                    if (pane) {
                        const stepNum = parseInt(pane.id.replace('stepPane', ''));
                        if (stepNum) {
                            goToStep(stepNum);
                        }
                    }
                    setTimeout(() => {
                        input.focus();
                        input.reportValidity();
                    }, 150);
                    return false;
                }
            }
        });
    }
});
</script>
@endsection
