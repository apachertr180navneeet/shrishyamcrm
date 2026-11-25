@extends('admin.layouts.app')

@section('style')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700;800;900&family=Hind:wght@500;600;700&family=Mukta:wght@500;600;700;800&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
<style>
    @font-face {
        font-family: 'MangalLocal';
        src: local('Mangal'), url('{{ asset("fonts/mangal.ttf") }}') format('truetype'), url('{{ url("fonts/mangal.ttf") }}') format('truetype');
        font-weight: 400;
        font-style: normal;
    }
    @font-face {
        font-family: 'MangalLocal';
        src: local('Mangal Bold'), local('Mangal'), url('{{ asset("fonts/mangalb.ttf") }}') format('truetype'), url('{{ url("fonts/mangalb.ttf") }}') format('truetype');
        font-weight: 700;
        font-style: normal;
    }
    @font-face {
        font-family: 'MangalLocal';
        src: local('Mangal Bold'), local('Mangal'), url('{{ asset("fonts/mangalb.ttf") }}') format('truetype'), url('{{ url("fonts/mangalb.ttf") }}') format('truetype');
        font-weight: 800;
        font-style: normal;
    }
    @font-face {
        font-family: 'MangalLocal';
        src: local('Mangal Bold'), local('Mangal'), url('{{ asset("fonts/mangalb.ttf") }}') format('truetype'), url('{{ url("fonts/mangalb.ttf") }}') format('truetype');
        font-weight: 900;
        font-style: normal;
    }
    @font-face {
        font-family: 'AparajitaLocal';
        src: local('Aparajita'), url('{{ asset("fonts/aparaj.ttf") }}') format('truetype'), url('{{ url("fonts/aparaj.ttf") }}') format('truetype');
        font-weight: 400;
        font-style: normal;
    }
    @font-face {
        font-family: 'AparajitaLocal';
        src: local('Aparajita Bold'), local('Aparajita'), url('{{ asset("fonts/aparajb.ttf") }}') format('truetype'), url('{{ url("fonts/aparajb.ttf") }}') format('truetype');
        font-weight: 700;
        font-style: normal;
    }

    .cert-screen-wrapper {
        max-width: 1024px;
        margin: 0 auto;
    }

    /* 1. Official Authentic Certificate Container */
    .cert-official-card {
        position: relative;
        width: 100%;
        aspect-ratio: 1024 / 723;
        background-size: 100% 100%;
        background-repeat: no-repeat;
        background-position: center;
        box-shadow: 0 12px 36px rgba(0,0,0,0.18);
        border-radius: 4px;
        overflow: visible; /* Ensure top matras are never cut off */
        font-family: 'Noto Sans Devanagari', 'Hind', 'MangalLocal', 'Mangal', 'Nirmala UI', 'Mukta', 'AparajitaLocal', 'Aparajita', sans-serif;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        font-feature-settings: "kern" 1, "liga" 1, "calt" 1, "pres" 1, "abvs" 1, "blws" 1, "psts" 1, "haln" 1;
        font-variant-ligatures: contextual normal;
    }

    /* Dynamic overlay fields with full headroom for Hindi matras */
    .cert-overlay-field {
        position: absolute;
        font-family: 'Noto Sans Devanagari', 'Hind', 'MangalLocal', 'Mangal', 'Nirmala UI', 'Mukta', 'AparajitaLocal', 'Aparajita', sans-serif;
        font-weight: 700;
        font-size: clamp(12.5px, 1.5vw, 16.5px);
        color: #111827;
        white-space: nowrap;
        overflow: visible !important;
        line-height: 1.45;
        padding-top: 2px;
        padding-bottom: 2px;
        z-index: 5;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        font-feature-settings: "kern" 1, "liga" 1, "calt" 1, "pres" 1, "abvs" 1, "blws" 1, "psts" 1, "haln" 1;
        font-variant-ligatures: contextual normal;
    }

    .ov-mem-no {
        top: 31.8%;
        left: 15.5%;
        max-width: 25%;
        color: #1B365D;
        font-size: clamp(13px, 1.55vw, 17px);
        letter-spacing: 0.5px;
    }
    .ov-date {
        top: 31.8%;
        left: 83.5%;
        max-width: 14%;
        color: #111827;
        font-size: clamp(12px, 1.4vw, 15px);
    }
    /* Member Name - Same font size and consistent alignment with other fields */
    .ov-name {
        top: 42.0%;
        left: 8.5%;
        max-width: 28%;
        color: #700D18;
        font-size: clamp(12.5px, 1.5vw, 16.5px);
        font-weight: 700;
        letter-spacing: 0.2px;
    }
    .ov-father {
        top: 42.0%;
        left: {{ !empty($isSeniorScheme) ? '47.5%' : '42.0%' }};
        max-width: {{ !empty($isSeniorScheme) ? '25%' : '29%' }};
        color: #111827;
    }
    .ov-caste {
        top: 42.0%;
        left: 75.5%;
        max-width: 18%;
        color: #111827;
    }
    .ov-age {
        top: 49.6%;
        left: 8.5%;
        max-width: 17%;
        color: #111827;
    }
    .ov-nominee {
        top: 49.6%;
        left: 27.5%;
        max-width: 48%;
        color: #1B365D;
        font-size: clamp(12.5px, 1.5vw, 16.5px);
    }
    .ov-village {
        top: 56.8%;
        left: 8.5%;
        max-width: 20%;
        color: #111827;
    }
    .ov-district {
        top: 56.8%;
        left: 35.0%;
        max-width: 17%;
        color: #111827;
    }
    .ov-state {
        top: 56.8%;
        left: 59.5%;
        max-width: 17%;
        color: #111827;
    }
    .ov-mobile {
        top: 64.5%;
        left: 11.5%;
        max-width: 38%;
        color: #111827;
    }
    .ov-agent {
        top: 64.5%;
        left: 57.2%;
        max-width: 20.2%;
        font-size: clamp(9.5px, 1.1vw, 12.0px);
        letter-spacing: -0.2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #111827;
    }
    .ov-kisht {
        top: 78.5%;
        left: 17.5%;
        max-width: 14%;
        font-size: clamp(13px, 1.55vw, 17px);
        color: #111827;
    }
    .ov-scheme-title {
        top: 21.2%;
        left: 50%;
        transform: translateX(-50%);
        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        color: #1B365D;
        font-size: clamp(14px, 1.8vw, 19px);
        font-weight: 700;
        letter-spacing: 0.4px;
        white-space: nowrap;
        text-align: center;
        line-height: 1.2;
    }

    .ov-photo-box {
        position: absolute;
        top: 49.9%;
        left: 78.0%;
        width: 13.1%;
        height: 25.8%;
        z-index: 6;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
    }
    .ov-photo-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Print styling */
    @media print {
        @page {
            size: A4 landscape;
            margin: 0;
        }
        body * {
            visibility: hidden !important;
        }
        .cert-print-area, .cert-print-area * {
            visibility: visible !important;
        }
        .cert-print-area {
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: transparent !important;
        }
        .cert-official-card {
            width: 297mm !important;
            height: 210mm !important;
            aspect-ratio: auto !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            overflow: visible !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .cert-overlay-field {
            overflow: visible !important;
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

    <!-- Official Authentic Certificate View (100% exact replica matching original template) -->
    <div class="cert-screen-wrapper cert-print-area">
        <div class="cert-official-card" style="background-image: url('{{ asset($bgImage) }}?v={{ filemtime(public_path($bgImage)) }}');">
            <!-- Dynamic Values Overlaid onto Authentic Template Background -->
            <div class="cert-overlay-field ov-mem-no">{{ $member->memberID ?: $member->membership_no }}</div>
            <div class="cert-overlay-field ov-date">{{ $member->joining_date ? $member->joining_date->format('d/m/Y') : date('d/m/Y') }}</div>
            <div class="cert-overlay-field ov-name">{{ $member->full_name }}</div>
            <div class="cert-overlay-field ov-father">{{ $member->father_spouse_name ?: '-' }}</div>
            <div class="cert-overlay-field ov-caste">{{ $member->caste ?: ($member->gotra ?: '-') }}</div>
            <div class="cert-overlay-field ov-age">{{ $member->age }} वर्ष</div>
            <div class="cert-overlay-field ov-nominee">{{ $nomineeName }}</div>
            <div class="cert-overlay-field ov-village" title="{{ $member->address }}">{{ $member->address ?: '-' }}</div>
            <div class="cert-overlay-field ov-district">{{ $member->district ?: 'बालोतरा' }}</div>
            <div class="cert-overlay-field ov-state">{{ $member->state ?: 'राजस्थान' }}</div>
            <div class="cert-overlay-field ov-mobile">{{ $member->mobile ?: '-' }}</div>
            <div class="cert-overlay-field ov-agent" title="{{ $member->agent ? $member->agent->name : '' }}">{{ $member->agent ? $member->agent->name . ($member->agent->mobile ? ' (' . $member->agent->mobile . ')' : '') : 'HQ Direct' }}</div>
            <div class="cert-overlay-field ov-kisht">{{ number_format($kishtRate, 0) }}/-</div>

            @if(empty($isSeniorScheme) && $schemeHeading !== 'विवाह योजना प्रमाण पत्र')
                <div class="cert-overlay-field ov-scheme-title">{{ $schemeHeading }}</div>
            @endif

            <!-- Member Photo Overlay (Inside the exact photo frame) -->
            <div class="ov-photo-box">
                @if($photoSrc)
                    <img src="{{ $photoSrc }}" alt="{{ $member->full_name }}">
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
