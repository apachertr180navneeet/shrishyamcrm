@extends('admin.layouts.app')

@section('style')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hind:wght@400;500;600;700&family=Yatra+One&display=swap" rel="stylesheet">
<style>
    .cert-screen-wrapper {
        max-width: 960px;
        margin: 0 auto;
    }
    .cert-card-container {
        width: 100%;
        background: #ffffff;
        border: 7px solid #6E0D1B;
        position: relative;
        padding: 20px 26px 14px 26px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        overflow: hidden;
        border-radius: 4px;
        font-family: 'Hind', sans-serif;
    }

    /* Top Left Golden Ribbon */
    .corner-tl-ribbon {
        position: absolute;
        top: 0;
        left: 0;
        width: 0;
        height: 0;
        border-top: 110px solid #EAA023;
        border-right: 155px solid transparent;
        z-index: 1;
    }
    .corner-tl-logo-box {
        position: absolute;
        top: 8px;
        left: 12px;
        width: 72px;
        height: 72px;
        z-index: 2;
        border-radius: 50%;
        background: #ffffff;
        padding: 2px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    .corner-tl-logo-box img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Top Right Golden Ribbon */
    .corner-tr-ribbon {
        position: absolute;
        top: 0;
        right: 0;
        width: 0;
        height: 0;
        border-top: 55px solid #EAA023;
        border-left: 320px solid transparent;
        z-index: 1;
    }
    .corner-tr-words {
        position: absolute;
        top: 4px;
        right: 20px;
        font-size: 14px;
        font-weight: 700;
        color: #6E0D1B;
        z-index: 2;
        letter-spacing: 1px;
    }

    /* Bottom Right Corner Accent */
    .corner-br-accent {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 0;
        height: 0;
        border-bottom: 85px solid #EAA023;
        border-left: 125px solid transparent;
        z-index: 1;
    }

    /* Center Watermark */
    .cert-watermark-bg {
        position: absolute;
        top: 50%;
        left: 46%;
        transform: translate(-50%, -50%);
        width: 280px;
        height: 280px;
        opacity: 0.10;
        pointer-events: none;
        z-index: 1;
    }

    .cert-content-layer {
        position: relative;
        z-index: 3;
    }

    .society-title-text {
        color: #6E0D1B;
        font-size: 2.2rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-align: center;
        margin: 2px 0 4px;
        line-height: 1.2;
    }

    .scheme-title-text {
        color: #1B365D;
        font-size: 1.6rem;
        font-weight: 700;
        text-align: center;
        margin: 4px 0 14px;
        line-height: 1.2;
    }

    .field-lbl {
        color: #6E0D1B;
        font-weight: 700;
        font-size: 1.05rem;
        white-space: nowrap;
    }
    .field-val {
        color: #000000;
        font-weight: 700;
        font-size: 1.05rem;
        border-bottom: 1.5px dotted #DC2626;
        padding: 0 6px;
        display: inline-block;
    }

    .member-photo-frame {
        width: 110px;
        height: 138px;
        border: 1.5px solid #64748B;
        background: #F8FAFC;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 2px;
    }
    .member-photo-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .footer-black-box {
        background: #000000;
        color: #ffffff;
        padding: 8px 14px;
        border-radius: 4px;
        font-size: 0.85rem;
        line-height: 1.4;
    }

    .signature-script {
        font-family: 'Brush Script MT', 'Segoe Script', cursive, sans-serif;
        color: #DC2626;
        font-size: 1.5rem;
        font-weight: bold;
        font-style: italic;
        line-height: 1;
    }

    .policy-yellow-strip {
        background: #EAA023;
        color: #000000;
        font-size: 0.78rem;
        font-weight: 700;
        text-align: center;
        padding: 4px 8px;
        border-radius: 3px;
        margin-top: 4px;
    }

    @media print {
        body * {
            visibility: hidden;
        }
        .cert-card-container, .cert-card-container * {
            visibility: visible;
        }
        .cert-card-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 15px 20px;
            border: 6px solid #6E0D1B;
            box-shadow: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .no-print {
            display: none !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Action Bar -->
    <div class="cert-screen-wrapper mb-4 no-print">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <a href="{{ route('admin.certificates.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Certificates List
            </a>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.certificates.pdf', $member->id) }}" class="btn btn-danger shadow-sm">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF Certificate
                </a>
                <button type="button" class="btn btn-primary shadow-sm" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print Certificate (प्रिंट निकालें)
                </button>
                <a href="{{ route('admin.members.show', $member->id) }}" class="btn btn-outline-dark">
                    <i class="fas fa-user me-1"></i> Member Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Official Certificate View -->
    <div class="cert-screen-wrapper">
        <div class="cert-card-container">
            <!-- Top Left Golden Ribbon & Society Logo -->
            <div class="corner-tl-ribbon"></div>
            <div class="corner-tl-logo-box">
                <img src="{{ asset('assets/society_logo.png') }}" onerror="this.onerror=null; this.src='{{ asset('assets/society_logo.jpg') }}';" alt="Logo">
            </div>

            <!-- Top Right Golden Ribbon & Virtues -->
            <div class="corner-tr-ribbon"></div>
            <div class="corner-tr-words">
                सहयोग &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; सेवा &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; विश्वास
            </div>

            <!-- Bottom Right Corner Accent -->
            <div class="corner-br-accent"></div>

            <!-- Center Watermark -->
            <img src="{{ asset('assets/society_logo.png') }}" onerror="this.onerror=null; this.src='{{ asset('assets/society_logo.jpg') }}';" alt="Watermark" class="cert-watermark-bg">

            <div class="cert-content-layer">
                <!-- 1. Top Meta Info Bar -->
                <div class="d-flex justify-content-between align-items-center mb-1" style="padding-left: 75px; padding-right: 10px;">
                    <div style="color: #1B365D; font-weight: 700; font-size: 0.95rem;">
                        Reg. No. {{ $society['reg_no'] }}
                    </div>
                    <div style="color: #DC2626; font-weight: 700; font-size: 1rem;">
                        !! श्री गणेशाय नमः !!
                    </div>
                    <div style="color: #1B365D; font-weight: 700; font-size: 0.95rem;">
                        SAN : {{ $society['san_code'] }}
                    </div>
                </div>

                <!-- 2. Society Heading -->
                <h1 class="society-title-text">{{ $society['name_hindi'] }}</h1>

                <!-- 3. Scheme Certificate Title -->
                <h2 class="scheme-title-text">{{ $member->scheme ? ($member->scheme->name_hindi ?: $member->scheme->name) : 'बुजुर्ग सम्मान' }} योजना प्रमाण पत्र</h2>

                <!-- 4. Form Data Section -->
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-9">
                        <!-- Row 1: Member No & Date -->
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-6">
                                <span class="field-lbl">सदस्य क्रमांक :</span>
                                <span class="field-val" style="color: #1B365D; font-size: 1.15rem;">{{ $member->membership_no }}</span>
                            </div>
                            <div class="col-6 text-end">
                                <span class="field-lbl">दिनांक :</span>
                                <span class="field-val">{{ $member->joining_date ? $member->joining_date->format('d/m/Y') : date('d/m/Y') }}</span>
                            </div>
                        </div>

                        <!-- Row 2: Name, Father/Spouse, Caste -->
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-5">
                                <span class="field-lbl">नाम</span>
                                <span class="field-val" style="color: #6E0D1B; font-size: 1.15rem;">{{ $member->full_name }}</span>
                            </div>
                            <div class="col-4">
                                <span class="field-lbl">पिता/पति का नाम</span>
                                <span class="field-val">{{ $member->father_spouse_name ?: '-' }}</span>
                            </div>
                            <div class="col-3 text-end">
                                <span class="field-lbl">जाति</span>
                                <span class="field-val">{{ $member->caste ?: ($member->gotra ?: '-') }}</span>
                            </div>
                        </div>

                        <!-- Row 3: Age & Nominee -->
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-4">
                                <span class="field-lbl">उम्र</span>
                                <span class="field-val">{{ $member->age }} वर्ष</span>
                            </div>
                            <div class="col-8">
                                <span class="field-lbl">वारिसदार</span>
                                <span class="field-val" style="color: #1B365D;">{{ $nomineeName }}</span>
                            </div>
                        </div>

                        <!-- Row 4: Village, District, State -->
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-4">
                                <span class="field-lbl">गांव</span>
                                <span class="field-val">{{ $member->address ?: '-' }}</span>
                            </div>
                            <div class="col-4">
                                <span class="field-lbl">जिला</span>
                                <span class="field-val">{{ $member->district ?: 'बालोतरा' }}</span>
                            </div>
                            <div class="col-4 text-end">
                                <span class="field-lbl">राज्य</span>
                                <span class="field-val">{{ $member->state ?: 'राजस्थान' }}</span>
                            </div>
                        </div>

                        <!-- Row 5: Mobile & Agent -->
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-4">
                                <span class="field-lbl">फोन नं.</span>
                                <span class="field-val">{{ $member->mobile ?: '-' }}</span>
                            </div>
                            <div class="col-8">
                                <span class="field-lbl">कार्यकर्ता</span>
                                <span class="field-val">{{ $member->agent ? $member->agent->name . ($member->agent->mobile ? ' (' . $member->agent->mobile . ')' : '') : 'HQ Direct' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Member Photo -->
                    <div class="col-3 text-center">
                        <div class="member-photo-frame">
                            @if($photoDoc && file_exists(public_path($photoDoc->file_path)))
                                <img src="{{ asset($photoDoc->file_path) }}" alt="Member Photo">
                            @else
                                <div class="text-muted p-3 text-center">
                                    <i class="fas fa-camera fs-3 text-secondary d-block mb-1"></i>
                                    <small class="fw-bold" style="font-size: 0.75rem;">फोटो<br>(PHOTO)</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 5. Gratitude & Support Rate -->
                <div class="mb-1 fw-bold" style="color: #6E0D1B; font-size: 1.05rem;">
                    संस्था से जुड़ने पर धन्यवाद।
                </div>
                <div class="mb-3 fw-bold" style="color: #6E0D1B; font-size: 1.05rem;">
                    सहयोग राशि : रु <span class="field-val" style="color: #000;">{{ number_format($kishtRate, 0) }}/-</span> रुपये प्रत्येक कार्यक्रम पर लागू।
                </div>

                <!-- 6. Bottom Notice Bar & Founder Signature -->
                <div class="row g-2 align-items-center">
                    <div class="col-7">
                        <div class="footer-black-box">
                            <div class="fw-bold">संस्था आपके स्वास्थ्य, उज्ज्वल व गौरवमयी भविष्य की मंगल कामना करती है।</div>
                            <div>कार्यालय :- {{ $society['address'] }}</div>
                            <div>मो. {{ $society['phone'] }}</div>
                        </div>
                    </div>
                    <div class="col-5 text-center">
                        <div class="signature-script">Ladu Ram</div>
                        <div class="fw-bold" style="color: #1B365D; font-size: 0.95rem;">हस्ताक्षर संस्थापक</div>
                        <div class="policy-yellow-strip">
                            1-6 माह तक दुर्घटना होने पर 51000रु व 6 माह बाद नियमानुसार भुगतान किया जायेगा
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
