@extends('admin.layouts.app')

@section('style')
<style>
    .receipt-container {
        max-width: 780px;
        margin: 0 auto;
        background: #FFFFFF;
        border: 2px solid #1B365D;
        border-radius: 12px;
        padding: 30px;
        position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .receipt-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.04;
        width: 320px;
        pointer-events: none;
    }
    .receipt-header-title {
        color: #1B365D;
        font-family: 'Hind', sans-serif;
        font-weight: 700;
        font-size: 1.6rem;
        line-height: 1.2;
    }
    .receipt-table th, .receipt-table td {
        padding: 8px 12px;
        border: 1px solid #E2E8F0;
    }
    @media print {
        body * {
            visibility: hidden;
        }
        .receipt-container, .receipt-container * {
            visibility: visible;
        }
        .receipt-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: 2px solid #000;
            box-shadow: none;
            padding: 20px;
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
    <div class="d-flex justify-content-between align-items-center mb-4 no-print" style="max-width: 780px; margin: 0 auto 1.5rem;">
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Payments
        </a>
        <div class="d-flex gap-2">
            <a href="{{ $whatsappData['url'] ?? '#' }}" target="_blank" class="btn btn-success">
                <i class="fab fa-whatsapp me-1"></i> Send on WhatsApp
            </a>
            <a href="{{ route('admin.payments.receipt.pdf', $payment->id) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf me-1"></i> Download PDF Receipt
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Receipt
            </button>
        </div>
    </div>

    <!-- Printable Receipt Layout -->
    <div class="receipt-container mb-5">
        <img src="{{ asset('assets/logo.svg') }}" alt="Watermark" class="receipt-watermark">

        <!-- Top Society Header -->
        <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('assets/logo.svg') }}" alt="Logo" style="width: 65px; height: 65px; object-fit: contain;">
                <div>
                    <h3 class="receipt-header-title mb-0">श्री श्याम वेलफेयर सोसायटी (रजि.)</h3>
                    <small class="text-muted d-block fw-semibold">Shri Shyam Welfare Society, Lohki</small>
                    <small class="text-muted d-block">ग्राम व पोस्ट: लोहीकी, तहसील: नारनौल, जिला: महेंद्रगढ़ (हरियाणा) - 123001</small>
                </div>
            </div>
            <div class="text-end">
                <span class="badge bg-primary px-3 py-2 fs-6 mb-1 d-inline-block">OFFICIAL RECEIPT</span>
                <small class="d-block text-muted">Reg No: <strong>HR/019/2021/04582</strong></small>
            </div>
        </div>

        <!-- Receipt Meta Bar -->
        <div class="row g-2 mb-3 bg-light p-2 rounded border">
            <div class="col-6 col-md-3">
                <small class="text-muted d-block">Receipt No:</small>
                <strong class="text-primary">{{ $payment->receipt_no }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <small class="text-muted d-block">SAN Code:</small>
                <strong class="text-dark">{{ $payment->san_code ?? 'SAN-LOH-'.$payment->member_id }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <small class="text-muted d-block">Date of Issue:</small>
                <strong>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '' }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <small class="text-muted d-block">Payment Mode:</small>
                <span class="badge bg-success">{{ $payment->payment_mode }}</span>
            </div>
        </div>

        <!-- Main Details Table -->
        <table class="table receipt-table mb-4">
            <tbody>
                <tr>
                    <th style="width: 30%;" class="bg-light">Member Name (सदस्य का नाम)</th>
                    <td><strong>{{ $payment->member ? $payment->member->full_name : 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th class="bg-light">Membership No (सदस्यता क्रमांक)</th>
                    <td><strong class="text-primary">{{ $payment->member ? $payment->member->membership_no : 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th class="bg-light">Scheme Enrolled (योजना)</th>
                    <td><strong>{{ $payment->member && $payment->member->scheme ? $payment->member->scheme->name_hindi : 'N/A' }}</strong> ({{ $payment->member && $payment->member->scheme ? $payment->member->scheme->name : '' }})</td>
                </tr>
                <tr>
                    <th class="bg-light">Father / Spouse Name</th>
                    <td>{{ $payment->member ? $payment->member->father_spouse_name : 'N/A' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Payment Purpose (उद्देश्य)</th>
                    <td><span class="badge bg-label-primary fs-6">{{ $payment->payment_type }}</span> ({{ $payment->month_year ?? 'Standard Contribution' }})</td>
                </tr>
                @if($payment->reference_no)
                <tr>
                    <th class="bg-light">Transaction / UTR Reference</th>
                    <td><code>{{ $payment->reference_no }}</code></td>
                </tr>
                @endif
                <tr>
                    <th class="bg-light">Amount Received (प्राप्त राशि)</th>
                    <td>
                        <h4 class="fw-bold text-success mb-0">₹{{ number_format($payment->amount, 2) }}</h4>
                    </td>
                </tr>
                <tr>
                    <th class="bg-light">Amount in Words (शब्दों में)</th>
                    <td class="fst-italic fw-semibold text-primary">
                        {{ $payment->amount_in_words }}
                    </td>
                </tr>
                <tr>
                    <th class="bg-light">Remaining Pending Dues (शेष बकाया)</th>
                    <td class="fw-bold text-danger">
                        ₹{{ number_format($payment->member ? $payment->member->pending_amount : 0, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Signatures & Stamp -->
        <div class="row pt-4 mt-4 border-top align-items-end">
            <div class="col-4 text-center">
                <div class="border p-2 d-inline-block rounded bg-light mb-1">
                    <i class="fas fa-qrcode fs-1 text-primary"></i>
                </div>
                <small class="d-block text-muted" style="font-size: 11px;">SAN Digital Verification</small>
            </div>
            <div class="col-4 text-center">
                <small class="text-muted d-block">Collected By:</small>
                <strong>{{ $payment->collected_by ?? 'HQ Administration' }}</strong>
            </div>
            <div class="col-4 text-center">
                <div style="height: 35px;"></div>
                <div class="border-top pt-1">
                    <small class="fw-bold d-block text-heading">Authorized Signatory</small>
                    <small class="text-muted" style="font-size: 11px;">Shri Shyam Welfare Society</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
