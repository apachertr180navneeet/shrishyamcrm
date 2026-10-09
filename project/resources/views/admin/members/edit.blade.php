@extends('admin.layouts.app')

@section('style')
<style>
    .form-section-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1E293B;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #E2E8F0;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-section-title i {
        color: #2563EB;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Breadcrumb & Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.members.index') }}">Members Directory</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.members.show', $member->id) }}">{{ $member->full_name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Member</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="font-family: 'Hind', sans-serif;">
                सदस्य विवरण संपादित करें (Edit Member: {{ $member->full_name }})
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.members.show', $member->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-eye me-1"></i> View Profile
            </a>
            <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong class="d-block mb-1"><i class="fas fa-exclamation-circle me-1"></i> Please fix the following errors:</strong>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('admin.members.update', $member->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Main Column (Form Details) -->
            <div class="col-lg-8 col-12">
                
                <!-- 1. Personal & KYC Details -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="form-section-title mb-0 border-0 p-0">
                                <i class="fas fa-user-circle fs-5"></i> 1. Personal & KYC Details (व्यक्तिगत एवं पहचान विवरण)
                            </span>
                            <span class="badge bg-label-primary fs-6">{{ $member->membership_no }}</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Member Photo Upload with Instant Live Preview -->
                        <div class="card border border-light-subtle bg-light mb-4 shadow-none">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div class="position-relative">
                                        <div id="edit_photo_preview_container" class="rounded-3 border border-2 border-primary-subtle bg-white shadow-sm overflow-hidden d-flex align-items-center justify-content-center" style="width: 105px; height: 105px;">
                                            @if($member->photo_src)
                                                <img id="edit_photo_preview" src="{{ $member->photo_src }}" alt="{{ $member->full_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                <div id="edit_photo_placeholder" class="text-center text-muted p-2" style="display: none;">
                                                    <i class="fas fa-camera fs-3 text-secondary d-block mb-1"></i>
                                                    <span style="font-size: 11px;">सदस्य फोटो</span>
                                                </div>
                                            @else
                                                <img id="edit_photo_preview" src="" alt="Photo Preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                                <div id="edit_photo_placeholder" class="text-center text-muted p-2">
                                                    <i class="fas fa-camera fs-3 text-secondary d-block mb-1"></i>
                                                    <span style="font-size: 11px;">सदस्य फोटो</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <label class="form-label fw-bold mb-0">
                                                <i class="fas fa-camera text-primary me-1"></i> Member Profile Photo (सदस्य पासपोर्ट फोटो)
                                            </label>
                                            @if($member->photo_src)
                                                <span class="badge bg-label-success small"><i class="fas fa-check-circle me-1"></i> फोटो संलग्न है</span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <input type="file" name="photo" id="edit_member_photo" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp" style="max-width: 320px;" onchange="previewEditMemberPhoto(this)">
                                            <button type="button" id="btn_clear_edit_photo" class="btn btn-outline-danger btn-sm" style="display: none;" onclick="clearEditMemberPhoto()">
                                                <i class="fas fa-undo me-1"></i> रीसेट करें (Reset)
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-info-circle text-primary me-1"></i> नई फोटो अपलोड करने के लिए फाइल चुनें (JPG, PNG, WEBP - अधिकतम 5MB)।
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Member Full Name (सदस्य का पूरा नाम) <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $member->full_name) }}" required>
                                @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Father / Husband Name (पिता / पति का नाम)</label>
                                <input type="text" name="father_spouse_name" class="form-control @error('father_spouse_name') is-invalid @enderror" value="{{ old('father_spouse_name', $member->father_spouse_name) }}">
                                @error('father_spouse_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-semibold">Mother's Name (माता का नाम)</label>
                                <input type="text" name="mother_name" class="form-control @error('mother_name') is-invalid @enderror" value="{{ old('mother_name', $member->mother_name) }}">
                                @error('mother_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-semibold">Gender (लिंग) <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="Male" {{ old('gender', $member->gender) == 'Male' ? 'selected' : '' }}>Male (पुरुष)</option>
                                    <option value="Female" {{ old('gender', $member->gender) == 'Female' ? 'selected' : '' }}>Female (महिला)</option>
                                    <option value="Other" {{ old('gender', $member->gender) == 'Other' ? 'selected' : '' }}>Other (अन्य)</option>
                                </select>
                                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-semibold">Membership Status (स्थिति) <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="Active" {{ old('status', $member->status) == 'Active' ? 'selected' : '' }}>Active (सक्रिय)</option>
                                    <option value="Inactive" {{ old('status', $member->status) == 'Inactive' ? 'selected' : '' }}>Inactive (निष्क्रिय)</option>
                                    <option value="Suspended" {{ old('status', $member->status) == 'Suspended' ? 'selected' : '' }}>Suspended (निलंबित)</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-semibold">Date of Birth (जन्म तिथि)</label>
                                <input type="date" name="dob" id="member_dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob', $member->dob ? $member->dob->format('Y-m-d') : '') }}" onchange="recalcAge()">
                                @error('dob') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-semibold">Age in Years (आयु - वर्ष)</label>
                                <input type="number" name="age" id="member_age" class="form-control @error('age') is-invalid @enderror" value="{{ old('age', $member->age) }}" min="0" max="120">
                                @error('age') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-semibold">Joining Date (पंजीकरण तिथि)</label>
                                <input type="date" name="joining_date" class="form-control @error('joining_date') is-invalid @enderror" value="{{ old('joining_date', $member->joining_date ? $member->joining_date->format('Y-m-d') : '') }}">
                                @error('joining_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-0">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Aadhaar Card No (आधार नंबर)</label>
                                <input type="text" name="aadhaar_no" class="form-control @error('aadhaar_no') is-invalid @enderror" value="{{ old('aadhaar_no', $member->aadhaar_no) }}" placeholder="12 digit Aadhaar">
                                @error('aadhaar_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold">Gotra (गोत्र)</label>
                                <input type="text" name="gotra" class="form-control @error('gotra') is-invalid @enderror" value="{{ old('gotra', $member->gotra) }}">
                                @error('gotra') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold">Caste (जाति)</label>
                                <input type="text" name="caste" class="form-control @error('caste') is-invalid @enderror" value="{{ old('caste', $member->caste) }}">
                                @error('caste') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Contact & Address Details -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <span class="form-section-title mb-0 border-0 p-0">
                            <i class="fas fa-map-marked-alt fs-5"></i> 2. Contact & Address Details (संपर्क एवं पता विवरण)
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Primary Mobile Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $member->mobile) }}" required>
                                </div>
                                @error('mobile') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Pincode (पिनकोड)</label>
                                <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror" value="{{ old('pincode', $member->pincode) }}" maxlength="10">
                                @error('pincode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">District (जिला)</label>
                                <input type="text" name="district" class="form-control @error('district') is-invalid @enderror" value="{{ old('district', $member->district) }}" placeholder="e.g. Mahendragarh, Rewari">
                                @error('district') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">State (राज्य)</label>
                                <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $member->state ?? 'Haryana') }}">
                                @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Complete Address (स्थायी पता)</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" placeholder="House No, Street, Village / City...">{{ old('address', $member->address) }}</textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- 3. Scheme, Age Slab & Agent Assignment -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <span class="form-section-title mb-0 border-0 p-0">
                            <i class="fas fa-hand-holding-heart fs-5"></i> 3. Scheme, Age Slab & Agent (योजना, स्लैब एवं एजेंट)
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Society Scheme (योजना) <span class="text-danger">*</span></label>
                                <select name="scheme_id" id="schemeSelect" class="form-select @error('scheme_id') is-invalid @enderror" onchange="onSchemeChange()" required>
                                    <option value="">-- Select Scheme (योजना चुनें) --</option>
                                    @foreach($schemes as $sch)
                                    <option value="{{ $sch->id }}" {{ old('scheme_id', $member->scheme_id) == $sch->id ? 'selected' : '' }}>
                                        {{ $sch->name_hindi }} ({{ $sch->code }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('scheme_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Applicable Age Slab (उम्र स्लैब)</label>
                                <select name="age_slab_id" id="ageSlabSelect" class="form-select @error('age_slab_id') is-invalid @enderror" onchange="onSlabChange()">
                                    <option value="">-- Select Age Slab --</option>
                                    @foreach($ageSlabs as $slab)
                                    <option value="{{ $slab->id }}" data-scheme="{{ $slab->scheme_id }}" data-joining="{{ $slab->joining_amount }}" data-support="{{ $slab->support_amount }}" {{ old('age_slab_id', $member->age_slab_id) == $slab->id ? 'selected' : '' }}>
                                        {{ $slab->slab_name ?? ($slab->min_age . '-' . $slab->max_age . ' Yrs') }} (₹{{ number_format($slab->support_amount) }}/कार्यक्रम)
                                    </option>
                                    @endforeach
                                </select>
                                @error('age_slab_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-semibold">अंशदान दर (प्रति कार्यक्रम सहयोग) / Support Per Event</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" name="monthly_support_amount" id="monthlySupportInput" class="form-control @error('monthly_support_amount') is-invalid @enderror" value="{{ old('monthly_support_amount', $member->monthly_support_amount) }}" step="1" required>
                                    <span class="input-group-text">/कार्यक्रम</span>
                                </div>
                                @error('monthly_support_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4 col-12">
                                <label class="form-label fw-semibold">Initial Joining Fee (प्रारंभिक शुल्क)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" name="joining_amount" id="joiningAmountInput" class="form-control @error('joining_amount') is-invalid @enderror" value="{{ old('joining_amount', $member->joining_amount) }}" step="1">
                                </div>
                                @error('joining_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4 col-12">
                                <label class="form-label fw-semibold">Assigned Agent (अधिकृत एजेंट)</label>
                                <select name="agent_id" class="form-select @error('agent_id') is-invalid @enderror">
                                    <option value="">HQ Direct / No Agent</option>
                                    @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ old('agent_id', $member->agent_id) == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }} ({{ $agent->agent_code }} - {{ $agent->district }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('agent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Nominees Details (Dual Nominee) -->
                @php
                    $nominee1 = $member->nominees->where('priority', 1)->first() ?? $member->nominees->first();
                    $nominee2 = $member->nominees->where('priority', 2)->first() ?? ($member->nominees->count() > 1 ? $member->nominees->skip(1)->first() : null);
                @endphp
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <span class="form-section-title mb-0 border-0 p-0">
                            <i class="fas fa-users fs-5"></i> 4. Nominees Details (नामांकित व्यक्ति विवरण)
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <!-- Nominee 1 (Primary) -->
                        <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user-tag me-1"></i> Primary Nominee (प्रथम नामांकित)</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Nominee 1 Name (पूरा नाम)</label>
                                <input type="text" name="nominee1_name" class="form-control" value="{{ old('nominee1_name', $nominee1?->name) }}" placeholder="e.g. Sunita Devi">
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold">Relation (संबंध)</label>
                                <input type="text" name="nominee1_relation" class="form-control" value="{{ old('nominee1_relation', $nominee1?->relation ?? 'Spouse') }}" placeholder="Spouse, Son, etc.">
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold">Share % (हिस्सा)</label>
                                <input type="number" name="nominee1_share" class="form-control" value="{{ old('nominee1_share', $nominee1?->percentage ?? 100) }}" min="1" max="100">
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Nominee 1 Mobile</label>
                                <input type="tel" name="nominee1_mobile" class="form-control" value="{{ old('nominee1_mobile', $nominee1?->mobile) }}" placeholder="Mobile Number">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Nominee 1 Aadhaar No</label>
                                <input type="text" name="nominee1_aadhaar" class="form-control" value="{{ old('nominee1_aadhaar', $nominee1?->aadhaar_no) }}" placeholder="Aadhaar Card No">
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Nominee 2 (Secondary - Optional) -->
                        <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-user-tag me-1"></i> Secondary Nominee (द्वितीय नामांकित - वैकल्पिक)</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Nominee 2 Name</label>
                                <input type="text" name="nominee2_name" class="form-control" value="{{ old('nominee2_name', $nominee2?->name) }}" placeholder="Optional second nominee">
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold">Relation</label>
                                <input type="text" name="nominee2_relation" class="form-control" value="{{ old('nominee2_relation', $nominee2?->relation ?? 'Son') }}">
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold">Share %</label>
                                <input type="number" name="nominee2_share" class="form-control" value="{{ old('nominee2_share', $nominee2?->percentage ?? 50) }}" min="0" max="100">
                            </div>
                        </div>
                        <div class="row g-3 mb-0">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Nominee 2 Mobile</label>
                                <input type="tel" name="nominee2_mobile" class="form-control" value="{{ old('nominee2_mobile', $nominee2?->mobile) }}">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold">Nominee 2 Aadhaar No</label>
                                <input type="text" name="nominee2_aadhaar" class="form-control" value="{{ old('nominee2_aadhaar', $nominee2?->aadhaar_no) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Documents Upload -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <span class="form-section-title mb-0 border-0 p-0">
                            <i class="fas fa-file-upload fs-5"></i> 5. Member KYC Documents (सदस्य दस्तावेज संलग्नक)
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-12 col-12">
                                <label class="form-label fw-semibold">Attach New KYC / Aadhaar / Document (नया दस्तावेज संलग्न करें)</label>
                                <div class="input-group mb-1">
                                    <select name="document_type" class="form-select" style="max-width: 160px;">
                                        <option value="Aadhaar">Aadhaar Card</option>
                                        <option value="Identity">Identity Proof</option>
                                        <option value="Address">Address Proof</option>
                                        <option value="Signature">Signature</option>
                                        <option value="Other">Other Document</option>
                                    </select>
                                    <input type="file" name="document_file" class="form-control @error('document_file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                </div>
                                <small class="text-muted">PDF or Image up to 10MB (सदस्य का पहचान पत्र / निवास प्रमाण)</small>
                                @error('document_file') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Existing Documents List -->
                        @if($member->documents->count() > 0)
                        <div class="border rounded p-3 bg-light">
                            <h6 class="fw-bold mb-2 small text-uppercase text-muted"><i class="fas fa-folder-open me-1"></i> Existing Documents (मौजूदा दस्तावेज)</h6>
                            <div class="row g-2">
                                @foreach($member->documents as $doc)
                                <div class="col-md-6 col-12">
                                    <div class="d-flex align-items-center justify-content-between p-2 bg-white rounded border">
                                        <div class="d-flex align-items-center gap-2 text-truncate">
                                            <i class="fas fa-file-alt text-primary"></i>
                                            <div class="text-truncate">
                                                <small class="fw-bold d-block text-truncate">{{ $doc->title ?? $doc->document_type }}</small>
                                                <small class="text-muted" style="font-size: 10px;">{{ $doc->file_name }}</small>
                                            </div>
                                        </div>
                                        <a href="{{ asset($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="View Document">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Submit Action Buttons -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <a href="{{ route('admin.members.show', $member->id) }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-1"></i> Cancel & Return
                        </a>
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold">
                            <i class="fas fa-save me-1"></i> Save & Update Member Details
                        </button>
                    </div>
                </div>

            </div>

            <!-- Right Summary Column -->
            <div class="col-lg-4 col-12">
                <!-- Member Profile Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 text-center">
                        <div class="avatar avatar-xl bg-label-primary mx-auto mb-2" style="width: 85px; height: 85px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 3px solid #e2e8f0;">
                            @if($member->photo_src)
                                <img id="sidebar_avatar_img" src="{{ $member->photo_src }}" alt="{{ $member->full_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                <i id="sidebar_avatar_icon" class="fas {{ $member->gender == 'Female' ? 'fa-female' : 'fa-user' }} fs-1" style="display: none;"></i>
                            @else
                                <img id="sidebar_avatar_img" src="" alt="{{ $member->full_name }}" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                <i id="sidebar_avatar_icon" class="fas {{ $member->gender == 'Female' ? 'fa-female' : 'fa-user' }} fs-1"></i>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill mb-2 px-3" onclick="document.getElementById('edit_member_photo').click()">
                            <i class="fas fa-camera me-1"></i> फोटो बदलें (Change Photo)
                        </button>
                        <h5 class="fw-bold mb-1">{{ $member->full_name }}</h5>
                        <p class="text-muted mb-2"><i class="fas fa-id-card me-1"></i> {{ $member->membership_no }}</p>
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <span class="badge {{ $member->status == 'Active' ? 'bg-success' : 'bg-secondary' }}">{{ $member->status }}</span>
                            <span class="badge bg-label-primary">{{ $member->scheme ? $member->scheme->name_hindi : 'N/A' }}</span>
                        </div>

                        <div class="border-top pt-3 text-start">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Paid:</span>
                                <strong class="text-success">₹{{ number_format($member->total_paid) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Pending Balance:</span>
                                <strong class="{{ $member->pending_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                    ₹{{ number_format($member->pending_amount) }}
                                </strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Assigned Agent:</span>
                                <span class="fw-semibold">{{ $member->agent ? $member->agent->name : 'HQ Direct' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Joined Date:</span>
                                <span>{{ $member->joining_date ? $member->joining_date->format('d M Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Links Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="card-title mb-0 fw-semibold"><i class="fas fa-link text-primary me-1"></i> Quick Member Actions</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.members.ledger.pdf', $member->id) }}" class="btn btn-outline-dark btn-sm text-start" target="_blank">
                                <i class="fas fa-file-invoice me-2 text-primary"></i> Download Ledger Card PDF
                            </a>
                            <a href="{{ route('admin.certificates.show', $member->id) }}" class="btn btn-outline-warning text-dark btn-sm text-start" target="_blank">
                                <i class="fas fa-certificate me-2 text-warning"></i> View Certificate
                            </a>
                            <a href="{{ route('admin.payments.create', ['member_id' => $member->id]) }}" class="btn btn-outline-success btn-sm text-start">
                                <i class="fas fa-rupee-sign me-2 text-success"></i> Collect / Record Payment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
const schemesData = @json($schemes);

function recalcAge() {
    const dobVal = document.getElementById('member_dob').value;
    if (dobVal) {
        const dob = new Date(dobVal);
        const diff = Date.now() - dob.getTime();
        const ageDate = new Date(diff);
        const calculatedAge = Math.abs(ageDate.getUTCFullYear() - 1970);
        if (!isNaN(calculatedAge)) {
            document.getElementById('member_age').value = calculatedAge;
        }
    }
}

function onSchemeChange() {
    const schemeId = document.getElementById('schemeSelect').value;
    const slabSelect = document.getElementById('ageSlabSelect');
    
    // Clear and filter slabs
    Array.from(slabSelect.options).forEach(opt => {
        if (!opt.value) return; // Keep placeholder
        if (!schemeId || opt.getAttribute('data-scheme') == schemeId) {
            opt.style.display = '';
            opt.disabled = false;
        } else {
            opt.style.display = 'none';
            opt.disabled = true;
        }
    });

    // If current selected option is disabled, reset
    if (slabSelect.selectedOptions.length && slabSelect.selectedOptions[0].disabled) {
        slabSelect.value = '';
    }
}

function onSlabChange() {
    const slabSelect = document.getElementById('ageSlabSelect');
    const selectedOpt = slabSelect.selectedOptions[0];
    if (selectedOpt && selectedOpt.value) {
        const support = selectedOpt.getAttribute('data-support');
        const joining = selectedOpt.getAttribute('data-joining');
        if (support) {
            document.getElementById('monthlySupportInput').value = support;
        }
        if (joining && !document.getElementById('joiningAmountInput').value) {
            document.getElementById('joiningAmountInput').value = joining;
        }
    }
}

const originalPhotoSrc = "{{ $member->photo_src ?? '' }}";

function previewEditMemberPhoto(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 5 * 1024 * 1024) {
            alert('फोटो का साइज 5MB से कम होना चाहिए (Photo size must be less than 5MB).');
            input.value = '';
            clearEditMemberPhoto();
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('edit_photo_preview');
            const placeholder = document.getElementById('edit_photo_placeholder');
            const clearBtn = document.getElementById('btn_clear_edit_photo');
            const sidebarImg = document.getElementById('sidebar_avatar_img');
            const sidebarIcon = document.getElementById('sidebar_avatar_icon');

            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            if (placeholder) {
                placeholder.style.display = 'none';
            }
            if (clearBtn) {
                clearBtn.style.display = 'inline-block';
            }
            if (sidebarImg) {
                sidebarImg.src = e.target.result;
                sidebarImg.style.display = 'block';
            }
            if (sidebarIcon) {
                sidebarIcon.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    }
}

function clearEditMemberPhoto() {
    const input = document.getElementById('edit_member_photo');
    const preview = document.getElementById('edit_photo_preview');
    const placeholder = document.getElementById('edit_photo_placeholder');
    const clearBtn = document.getElementById('btn_clear_edit_photo');
    const sidebarImg = document.getElementById('sidebar_avatar_img');
    const sidebarIcon = document.getElementById('sidebar_avatar_icon');

    if (input) input.value = '';
    if (clearBtn) clearBtn.style.display = 'none';

    if (originalPhotoSrc) {
        if (preview) {
            preview.src = originalPhotoSrc;
            preview.style.display = 'block';
        }
        if (sidebarImg) {
            sidebarImg.src = originalPhotoSrc;
            sidebarImg.style.display = 'block';
        }
        if (sidebarIcon) sidebarIcon.style.display = 'none';
        if (placeholder) placeholder.style.display = 'none';
    } else {
        if (preview) {
            preview.src = '';
            preview.style.display = 'none';
        }
        if (sidebarImg) {
            sidebarImg.src = '';
            sidebarImg.style.display = 'none';
        }
        if (sidebarIcon) sidebarIcon.style.display = 'inline-block';
        if (placeholder) placeholder.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    onSchemeChange();
});
</script>
@endsection
