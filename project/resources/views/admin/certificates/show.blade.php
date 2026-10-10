@extends('admin.layouts.app')

@section('style')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hind:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .cert-screen-wrapper {
        max-width: 980px;
        margin: 0 auto;
    }
    .cert-card-container {
        width: 100%;
        background: #ffffff;
        border: 8px solid #700D18;
        position: relative;
        padding: 18px 24px 14px 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        overflow: hidden;
        font-family: 'Hind', sans-serif;
    }

    /* Top-Left Golden Arc Swoosh */
    .corner-tl-swoop {
        position: absolute;
        top: 0;
        left: 0;
        width: 140px;
        height: 105px;
        background: #EAA023;
        border-bottom-right-radius: 130px 100px;
        z-index: 1;
    }
    .corner-tl-logo-box {
        position: absolute;
        top: 8px;
        left: 12px;
        width: 76px;
        height: 76px;
        z-index: 3;
        border-radius: 50%;
        background: #ffffff;
        padding: 2px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.18);
    }
    .corner-tl-logo-box img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Top-Right Golden Swoosh */
    .corner-tr-swoop {
        position: absolute;
        top: 0;
        right: 0;
        width: 160px;
        height: 55px;
        background: #EAA023;
        border-bottom-left-radius: 140px 50px;
        z-index: 1;
    }

    /* Bottom-Right Golden Swoosh */
    .corner-br-swoop {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 140px;
        height: 95px;
        background: #EAA023;
        border-top-left-radius: 130px 90px;
        z-index: 1;
    }

    /* Center Watermark */
    .cert-watermark-bg {
        position: absolute;
        top: 50%;
        left: 45%;
        transform: translate(-50%, -50%);
        width: 280px;
        height: 280px;
        opacity: 0.12;
        pointer-events: none;
        z-index: 1;
    }

    .cert-content-layer {
        position: relative;
        z-index: 2;
    }

    /* Top Header Bar */
    .top-meta-capsule {
        background: #EEF5FC;
        border: 1px solid #D1E3F6;
        border-radius: 20px;
        padding: 4px 16px;
        display: inline-flex;
        align-items: center;
        gap: 16px;
    }
    .top-reg-no {
        color: #1A365D;
        font-weight: 700;
        font-size: 0.92rem;
    }
    .top-ganesh {
        color: #C02626;
        font-weight: 700;
        font-size: 0.95rem;
    }
    .top-virtues-text {
        color: #700D18;
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
        text-align: right;
    }
    .top-san-text {
        color: #1A365D;
        font-weight: 700;
        font-size: 0.95rem;
        text-align: right;
    }

    /* Society & Scheme Headings */
    .society-title-text {
        color: #700D18;
        font-size: 2.25rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-align: center;
        margin: 2px 0 2px;
        line-height: 1.2;
    }
    .scheme-title-text {
        color: #243382;
        font-size: 1.65rem;
        font-weight: 700;
        text-align: center;
        margin: 2px 0 12px;
        line-height: 1.2;
    }

    /* Field Labels & Dotted Line Values */
    .field-lbl {
        color: #700D18;
        font-weight: 700;
        font-size: 1.05rem;
        white-space: nowrap;
    }
    .field-val-dotted {
        color: #000000;
        font-weight: 700;
        font-size: 1.05rem;
        border-bottom: 1.5px dotted #B91C1C;
        padding: 0 4px;
        display: inline-block;
    }

    /* Photo Frame */
    .member-photo-frame {
        width: 110px;
        height: 140px;
        border: 1.5px solid #4B5563;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        overflow: hidden;
    }
    .member-photo-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Founder Signature */
    .signature-container {
        text-align: center;
        margin-top: 4px;
    }
    .signature-graphic {
        height: 40px;
        display: inline-block;
    }
    .founder-sign-title {
        color: #1A365D;
        font-weight: 700;
        font-size: 0.95rem;
        margin-top: 2px;
    }

    /* Bottom Black Badge */
    .footer-black-box {
        background: #000000;
        color: #ffffff;
        padding: 6px 14px;
        border-radius: 4px;
        font-size: 0.85rem;
        line-height: 1.4;
    }
    .footer-wish-line {
        font-weight: 700;
        border-bottom: 1px dashed rgba(255, 255, 255, 0.7);
        padding-bottom: 2px;
        margin-bottom: 3px;
    }
    .footer-office-line {
        font-weight: 600;
        font-size: 0.82rem;
    }
    .footer-phone-text {
        color: #000000;
        font-weight: 700;
        font-size: 0.95rem;
        margin-top: 4px;
    }

    /* Yellow Policy Strip */
    .policy-yellow-ribbon {
        background: #EAA023;
        color: #000000;
        font-size: 0.82rem;
        font-weight: 700;
        text-align: center;
        padding: 5px 12px;
        border-radius: 14px;
        display: block;
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
            padding: 14px 20px;
            border: 8px solid #700D18;
            box-shadow: none;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
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
                <i class="fas fa-arrow-left me-1"></i> प्रमाण-पत्र सूची (Certificates List)
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

    <!-- Official Certificate Card View -->
    <div class="cert-screen-wrapper">
        <div class="cert-card-container">
            <!-- Top-Left Golden Arc Swoosh & Society Logo -->
            <div class="corner-tl-swoop"></div>
            <div class="corner-tl-logo-box">
                <img src="{{ asset('assets/society_logo.png') }}" onerror="this.onerror=null; this.src='{{ asset('assets/society_logo.jpg') }}';" alt="Logo">
            </div>

            <!-- Top-Right Golden Swoosh -->
            <div class="corner-tr-swoop"></div>

            <!-- Bottom-Right Golden Swoosh -->
            <div class="corner-br-swoop"></div>

            <!-- Center Watermark -->
            <img src="{{ asset('assets/society_logo.png') }}" onerror="this.onerror=null; this.src='{{ asset('assets/society_logo.jpg') }}';" alt="Watermark" class="cert-watermark-bg">

            <div class="cert-content-layer">
                <!-- 1. Top Meta Info Bar -->
                <div class="d-flex justify-content-between align-items-center mb-1" style="padding-left: 82px; padding-right: 12px;">
                    <div class="top-meta-capsule">
                        <span class="top-reg-no">Reg. No. {{ $society['reg_no'] }}</span>
                        <span class="top-ganesh">!! श्री गणेशाय नम:</span>
                    </div>
                    <div>
                        <div class="top-virtues-text">
                            सहयोग &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; सेवा &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; विश्वास
                        </div>
                        <div class="top-san-text">
                            SAN : {{ $society['san_code'] }}
                        </div>
                    </div>
                </div>

                <!-- 2. Society Heading -->
                <h1 class="society-title-text">{{ $society['name_hindi'] }}</h1>

                <!-- 3. Scheme Certificate Title (विवाह योजना प्रमाण पत्र / कन्यादान विवाह योजना प्रमाण पत्र) -->
                <h2 class="scheme-title-text">{{ $schemeHeading }}</h2>

                <!-- 4. Form Data Section -->
                <div class="row g-2 align-items-start mb-2">
                    <!-- Left Form Fields -->
                    <div class="col-9">
                        <!-- Row 1: Member No & Date -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="field-lbl">सदस्य क्रमांक :</span>
                                <span class="field-val-dotted" style="min-width: 160px;">{{ $member->membership_no }}</span>
                            </div>
                            <div style="padding-right: 20px;">
                                <span class="field-lbl">दिनांक :</span>
                                <span class="field-val-dotted" style="min-width: 140px;">{{ $member->joining_date ? $member->joining_date->format('d/m/Y') : date('d/m/Y') }}</span>
                            </div>
                        </div>

                        <!-- Row 2: Name, Father's Name, Caste -->
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-4">
                                <span class="field-lbl">नाम</span>
                                <span class="field-val-dotted" style="min-width: 140px;">{{ $member->full_name }}</span>
                            </div>
                            <div class="col-5">
                                <span class="field-lbl">{{ $fatherSpouseLabel ?? 'पिता/पति का नाम' }}</span>
                                <span class="field-val-dotted" style="min-width: 140px;">{{ $member->father_spouse_name ?: '-' }}</span>
                            </div>
                            <div class="col-3 text-end" style="padding-right: 20px;">
                                <span class="field-lbl">जाति</span>
                                <span class="field-val-dotted" style="min-width: 90px;">{{ $member->caste ?: ($member->gotra ?: '-') }}</span>
                            </div>
                        </div>

                        <!-- Row 3: Age & Nominee -->
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-4">
                                <span class="field-lbl">उम्र</span>
                                <span class="field-val-dotted" style="min-width: 140px;">{{ $member->age }} वर्ष</span>
                            </div>
                            <div class="col-8" style="padding-right: 20px;">
                                <span class="field-lbl">वारिसदार</span>
                                <span class="field-val-dotted" style="min-width: 250px;">{{ $nomineeName }}</span>
                            </div>
                        </div>

                        <!-- Row 4: Village, District, State -->
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-4">
                                <span class="field-lbl">गांव</span>
                                <span class="field-val-dotted" style="min-width: 140px;">{{ $member->address ?: '-' }}</span>
                            </div>
                            <div class="col-4">
                                <span class="field-lbl">जिला</span>
                                <span class="field-val-dotted" style="min-width: 110px;">{{ $member->district ?: 'बालोतरा' }}</span>
                            </div>
                            <div class="col-4 text-end" style="padding-right: 20px;">
                                <span class="field-lbl">राज्य</span>
                                <span class="field-val-dotted" style="min-width: 110px;">{{ $member->state ?: 'राजस्थान' }}</span>
                            </div>
                        </div>

                        <!-- Row 5: Mobile & Agent -->
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-4">
                                <span class="field-lbl">फोन नं.</span>
                                <span class="field-val-dotted" style="min-width: 140px;">{{ $member->mobile ?: '-' }}</span>
                            </div>
                            <div class="col-8" style="padding-right: 20px;">
                                <span class="field-lbl">कार्यकर्ता</span>
                                <span class="field-val-dotted" style="min-width: 250px;">{{ $member->agent ? $member->agent->name . ($member->agent->mobile ? ' (' . $member->agent->mobile . ')' : '') : 'HQ Direct' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Member Photo -->
                    <div class="col-3 text-center">
                        <div class="member-photo-frame">
                            @php
                                $photoSrc = $member->photo_src ?: ($photoDoc && !empty($photoDoc->file_path) ? asset($photoDoc->file_path) : null);
                            @endphp
                            @if($photoSrc)
                                <img src="{{ $photoSrc }}" alt="{{ $member->full_name }}">
                            @else
                                <div class="text-muted p-2 text-center">
                                    <small class="fw-bold" style="font-size: 0.78rem; color: #9CA3AF;">फोटो<br>(PHOTO)</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 5. Gratitude & Support Rate -->
                <div class="mb-1 fw-bold" style="color: #700D18; font-size: 1.05rem;">
                    संस्था से जुड़ने पर धन्यवाद।
                </div>
                <div class="mb-2 fw-bold" style="color: #700D18; font-size: 1.05rem;">
                    सहयोग राशि : रु <span class="field-val-dotted" style="color: #000;">{{ number_format($kishtRate, 0) }}/-</span> रूपये प्रत्येक कार्यक्रम पर लागू।
                </div>

                <!-- 6. Bottom Notice Bar, Founder Signature, and Yellow Ribbon -->
                <div class="row g-2 align-items-end mt-1">
                    <!-- Left: Black Box & Phone Number -->
                    <div class="col-7">
                        <div class="footer-black-box">
                            <div class="footer-wish-line">संस्था आपके स्वास्थ्य, उज्ज्वल व गौरवमयी भविष्य की मंगल कामना करती है।</div>
                            <div class="footer-office-line">कार्यालय :- {{ $society['address'] }}</div>
                        </div>
                        <div class="footer-phone-text">
                            मो. {{ $society['phone'] }}
                        </div>
                    </div>

                    <!-- Right: Founder Signature & Policy Strip -->
                    <div class="col-5 text-center">
                        <div class="signature-container">
                            <img src="{{ asset('assets/signature_laduram.png') }}" onerror="this.onerror=null; this.src='{{ asset('assets/signature_laduram.svg') }}';" alt="हस्ताक्षर" class="signature-graphic">
                            <div class="founder-sign-title">हस्ताक्षर संस्थापक</div>
                        </div>
                        <div class="policy-yellow-ribbon" style="{{ !empty($isSeniorScheme) ? 'line-height: 1.25; font-size: 0.76rem; padding: 3px 8px;' : '' }}">
                            @if(!empty($isSeniorScheme))
                                1–6 माह तक दुर्घटना होने पर 51000रु व<br>6माह बाद सदस्यानुसार भुगतान किया जायेगा
                            @else
                                {{ $policyNote }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
