@extends('admin.layouts.app')

@section('style')
<style>
    .certificate-wrapper {
        max-width: 860px;
        margin: 0 auto;
        padding: 25px;
        background: #FDFBF7;
        border: 12px double #D97706;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        position: relative;
        border-radius: 8px;
        color: #1E293B;
    }
    .cert-inner-border {
        border: 2px solid #D97706;
        padding: 35px 30px;
        position: relative;
        background: #FFFFFF;
    }
    .cert-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 380px;
        opacity: 0.05;
        pointer-events: none;
    }
    .cert-title-hi {
        font-family: 'Hind', sans-serif;
        font-weight: 700;
        font-size: 2.2rem;
        color: #1B365D;
        letter-spacing: 1px;
    }
    .cert-sub-title {
        font-size: 1.1rem;
        letter-spacing: 2px;
        color: #D97706;
        font-weight: 700;
        text-transform: uppercase;
    }
    .cert-member-name {
        font-family: 'Hind', sans-serif;
        font-size: 1.8rem;
        font-weight: 700;
        color: #1B365D;
        border-bottom: 2px dotted #D97706;
        display: inline-block;
        padding: 0 30px;
        margin: 10px 0;
    }
    .cert-text {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #334155;
    }
    @media print {
        body * {
            visibility: hidden;
        }
        .certificate-wrapper, .certificate-wrapper * {
            visibility: visible;
        }
        .certificate-wrapper {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: 10px double #D97706;
            box-shadow: none;
            margin: 0;
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
    <div class="d-flex justify-content-between align-items-center mb-4 no-print" style="max-width: 860px; margin: 0 auto 1.5rem;">
        <a href="{{ route('admin.certificates.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Certificates
        </a>
        <button type="button" class="btn btn-primary btn-lg shadow" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Official Certificate
        </button>
    </div>

    <!-- Official Gold-Bordered Certificate -->
    <div class="certificate-wrapper mb-5">
        <div class="cert-inner-border">
            <img src="{{ asset('assets/logo.svg') }}" alt="Watermark" class="cert-watermark">

            <!-- Certificate Header -->
            <div class="text-center mb-4">
                <img src="{{ asset('assets/logo.svg') }}" alt="Logo" style="width: 85px; height: 85px; object-fit: contain;" class="mb-2">
                <h1 class="cert-title-hi mb-1">श्री श्याम वेलफेयर सोसायटी (रजि.)</h1>
                <div class="cert-sub-title mb-1">Shri Shyam Welfare Society, Lohki</div>
                <small class="text-muted d-block fw-semibold">पंजीकरण संख्या (Reg No.): <strong>HR/NNL/2021/04582</strong> | ग्राम व पोस्ट: लोहीकी (नारनौल), हरियाणा</small>
                <hr style="border-top: 2px solid #D97706; width: 60%; margin: 15px auto;">
                <h3 class="fw-bold text-dark text-uppercase tracking-wide" style="font-family: 'Hind', sans-serif; letter-spacing: 1px;">
                    सदस्यता प्रमाण-पत्र (CERTIFICATE OF MEMBERSHIP)
                </h3>
            </div>

            <!-- Certificate Body -->
            <div class="text-center my-4">
                <p class="cert-text mb-2">
                    प्रमाणित किया जाता है कि (This is to certify that)
                </p>
                <div class="cert-member-name">
                    {{ $member->full_name }}
                </div>
                <p class="cert-text mt-3 mb-2">
                    सुपुत्र / सुपुत्री / धर्मपत्नी श्री <strong>{{ $member->father_spouse_name ?? 'N/A' }}</strong>, निवासी <strong>{{ $member->address }}, जिला: {{ $member->district }} (हरियाणा)</strong>,
                </p>
                <p class="cert-text mb-4">
                    श्री श्याम वेलफेयर सोसायटी की <strong>{{ $member->scheme ? $member->scheme->name_hindi : 'कल्याणकारी योजना' }}</strong> के अंतर्गत आजीवन पंजीकृत सदस्य हैं।
                </p>

                <div class="row justify-content-center g-3 my-3">
                    <div class="col-md-4 col-6">
                        <div class="bg-light p-2 rounded border">
                            <small class="text-muted d-block">सदस्यता संख्या (Membership No.)</small>
                            <strong class="fs-5 text-primary">{{ $member->membership_no }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="bg-light p-2 rounded border">
                            <small class="text-muted d-block">पंजीकरण तिथि (Joining Date)</small>
                            <strong class="fs-5 text-dark">{{ $member->joining_date ? $member->joining_date->format('d M Y') : date('d M Y') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certificate Footer / Signatures -->
            <div class="row pt-5 mt-4 align-items-end">
                <div class="col-4 text-center">
                    <div class="border p-2 d-inline-block rounded bg-light mb-1">
                        <i class="fas fa-qrcode fs-1 text-warning"></i>
                    </div>
                    <small class="d-block text-muted" style="font-size: 11px;">Digital Verification Stamp</small>
                </div>
                <div class="col-4 text-center">
                    <div style="height: 40px;"></div>
                    <div class="border-top pt-1 border-dark">
                        <strong class="d-block text-heading">सचिव / Secretary</strong>
                        <small class="text-muted" style="font-size: 11px;">श्री श्याम वेलफेयर सोसायटी</small>
                    </div>
                </div>
                <div class="col-4 text-center">
                    <div style="height: 40px;"></div>
                    <div class="border-top pt-1 border-dark">
                        <strong class="d-block text-heading">अध्यक्ष / President</strong>
                        <small class="text-muted" style="font-size: 11px;">श्री श्याम वेलफेयर सोसायटी</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
