@extends('admin.layouts.app')

@section('style')
<style>
    .receipt-container {
        max-width: 820px;
        margin: 0 auto;
        background: #FFFFFF;
        border: 2px solid #1B365D;
        border-radius: 12px;
        padding: 24px 28px;
        position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .receipt-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.035;
        width: 320px;
        pointer-events: none;
    }
    .receipt-header-title {
        color: #1B365D;
        font-family: 'Hind', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-weight: 700;
        font-size: 1.55rem;
        line-height: 1.2;
    }
    .receipt-table th, .receipt-table td {
        padding: 6px 10px;
        font-size: 0.9rem;
        vertical-align: middle;
        border: 1px solid #E2E8F0;
    }
    .receipt-table th {
        font-weight: 600;
        color: #334155;
    }

    @media print {
        html, body {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .layout-navbar,
        .layout-menu,
        .content-footer,
        .no-print,
        .btn,
        nav,
        aside {
            display: none !important;
        }
        .content-wrapper,
        .layout-page,
        .layout-container,
        .container-fluid,
        .container-p-y {
            padding: 0 !important;
            margin: 0 !important;
        }
        .receipt-container {
            border: 2px solid #1B365D !important;
            border-radius: 8px !important;
            box-shadow: none !important;
            margin: 0 auto !important;
            padding: 16px 20px !important;
            width: 100% !important;
            max-width: 100% !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }
</style>
@endsection

@section('content')
@php
    $member = $payment->member;
    $primaryNominee = $member && $member->nominees ? $member->nominees->first() : null;
    $nomineeName = $primaryNominee ? $primaryNominee->name . ($primaryNominee->relation ? ' (' . $primaryNominee->relation . ')' : '') : '-';

    $kishtRate = $member && (float)$member->monthly_support_amount > 0
        ? (float)$member->monthly_support_amount
        : (float)$payment->amount;

    $agentName = $payment->agent
        ? $payment->agent->name
        : ($member && $member->agent ? $member->agent->name : ($payment->collected_by ?: 'HQ Administration'));

    $agentMobile = $payment->agent
        ? $payment->agent->mobile
        : ($member && $member->agent ? $member->agent->mobile : '9664090906');

    $joiningDate = $member && $member->joining_date
        ? $member->joining_date->format('d.m.Y')
        : ($member && $member->created_at ? $member->created_at->format('d.m.Y') : '-');

    $eventDate = $payment->event && $payment->event->event_date
        ? $payment->event->event_date->format('d.m.Y')
        : ($payment->payment_date ? $payment->payment_date->format('d.m.Y') : '-');

    $paymentDate = $payment->payment_date ? $payment->payment_date->format('d.m.Y') : date('d.m.Y');

    $description = '';
    if ($payment->event) {
        $ev = $payment->event;
        $description = $ev->girl_name . ($ev->father_name ? ' / ' . $ev->father_name : '') . ($ev->venue ? ' ' . $ev->venue : '');
    } else {
        $description = $payment->payment_type . ($payment->remarks ? ' - ' . $payment->remarks : ($payment->month_year ? ' (' . $payment->month_year . ')' : ''));
    }
@endphp

<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print" style="max-width: 820px; margin: 0 auto 1.5rem;">
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Payments
        </a>
        <div class="d-flex gap-2">
            @if($member && $member->status === 'Active' && !($whatsappData['disabled'] ?? false))
            <a href="{{ $whatsappData['url'] ?? '#' }}" target="_blank" class="btn btn-success">
                <i class="fab fa-whatsapp me-1"></i> Send on WhatsApp
            </a>
            @else
            <button type="button" class="btn btn-outline-secondary disabled" title="Inactive सदस्य को WhatsApp संदेश नहीं भेजा जा सकता" disabled>
                <i class="fab fa-whatsapp me-1"></i> WhatsApp (Inactive)
            </button>
            @endif
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
        <img src="{{ file_exists(public_path('assets/society_logo.png')) ? asset('assets/society_logo.png') : asset('assets/logo.svg') }}" alt="Watermark" class="receipt-watermark">

        <!-- Top Society Header -->
        <div class="text-center border-bottom pb-3 mb-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <img src="{{ file_exists(public_path('assets/society_logo.png')) ? asset('assets/society_logo.png') : asset('assets/logo.svg') }}" alt="Logo" style="width: 62px; height: 62px; border-radius: 50%; object-fit: contain;">
                <div class="flex-grow-1 text-center px-2">
                    <div class="text-danger fw-bold mb-1" style="font-size: 13.5px; letter-spacing: 0.5px;">श्री गणेशाय नमः</div>
                    <h3 class="receipt-header-title mb-1">श्री श्याम वेलफेयर सोसाइटी, लोहिड़ी</h3>
                    <div class="text-secondary fw-semibold small">ग्राम पंचायत लोहिड़ी, पंचायत समिति सिणधरी, जिला बालोतरा (राजस्थान)</div>
                </div>
                <div class="text-end" style="min-width: 90px;">
                    <span class="badge bg-primary px-2 py-1 fs-7 mb-1 d-inline-block">OFFICIAL RECEIPT</span>
                    <small class="d-block text-muted" style="font-size: 10px;">Reg No: <strong>COOP/2025/BALOTARA/500219</strong></small>
                    <small class="d-block text-muted" style="font-size: 10px;">SAN: <strong>{{ $payment->san_code ?? '0878920000000014' }}</strong></small>
                </div>
            </div>
        </div>

        <!-- Scheme Row & Date -->
        <div class="row g-2 mb-3 bg-light p-2 rounded border align-items-center text-center text-md-start">
            <div class="col-4 col-md-3">
                <small class="text-muted d-block">क्र. सं. (Receipt No):</small>
                <strong class="text-primary fs-6">{{ $payment->receipt_no }}</strong>
            </div>
            <div class="col-4 col-md-6 text-center">
                <span class="badge bg-label-primary px-3 py-2 fs-6">
                    {{ $member && $member->scheme ? $member->scheme->name_hindi : 'बुजुर्ग सम्मान योजना' }}
                </span>
            </div>
            <div class="col-4 col-md-3 text-md-end">
                <small class="text-muted d-block">दिनांक (Date):</small>
                <strong>{{ $paymentDate }}</strong>
            </div>
        </div>

        <!-- 5-Row Member Info Grid -->
        <table class="table table-sm receipt-table mb-3">
            <tbody>
                <!-- Row 1: Member Name & Spouse/Father Name -->
                <tr>
                    <th class="bg-light" style="width: 22%;">श्री</th>
                    <td style="width: 28%;"><strong>{{ $member ? $member->full_name : '-' }}</strong></td>
                    <th class="bg-light" style="width: 24%;">पति / पत्नी का नाम</th>
                    <td style="width: 26%;">{{ $member ? $member->father_spouse_name : '-' }}</td>
                </tr>

                <!-- Row 2: Address & Gotra -->
                <tr>
                    <th class="bg-light">निवासी</th>
                    <td>{{ $member ? ($member->address ?: ($member->village ? $member->village . ', ' . $member->district : $member->district)) : '-' }}</td>
                    <th class="bg-light">गोत्र</th>
                    <td>{{ $member ? ($member->gotra ?? ($member->caste ?? '-')) : '-' }}</td>
                </tr>

                <!-- Row 3: Mobile Number & Kisht Amount -->
                <tr>
                    <th class="bg-light">मोबाइल नंबर</th>
                    <td class="text-nowrap">{{ $member ? $member->mobile : '-' }}</td>
                    <th class="bg-light">किस्त</th>
                    <td><strong class="text-primary">₹{{ number_format($kishtRate, 0) }}</strong></td>
                </tr>

                <!-- Row 4: Agent Name & Agent Mobile -->
                <tr>
                    <th class="bg-light">कार्यकर्ता का नाम</th>
                    <td>{{ $agentName }}</td>
                    <th class="bg-light">मो.</th>
                    <td class="text-nowrap">{{ $agentMobile }}</td>
                </tr>

                <!-- Row 5: Nominee Name & Joining Date -->
                <tr>
                    <th class="bg-light">वारिसदार का नाम</th>
                    <td>{{ $nomineeName }}</td>
                    <th class="bg-light">जुड़ने की तिथि</th>
                    <td>{{ $joiningDate }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Event / Contribution Details Table -->
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-sm align-middle mb-0">
                <thead class="table-dark" style="background: #1B365D;">
                    <tr>
                        <th style="width: 45%;">नाम / विवरण</th>
                        <th style="width: 18%;" class="text-center">कार्यक्रम की दिनांक</th>
                        <th style="width: 18%;" class="text-center">भुगतान की तिथि</th>
                        <th style="width: 19%;" class="text-center">कार्यकर्ता का नाम</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>{{ $description }}</strong></td>
                        <td class="text-center">{{ $eventDate }}</td>
                        <td class="text-center"><span class="badge bg-success">{{ $paymentDate }}</span></td>
                        <td class="text-center">{{ $agentName }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Financial Summary Box: Amount Received, In Words & Remaining Dues -->
        <div class="p-3 bg-light rounded border mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <small class="text-muted d-block">प्राप्त राशि (Amount Received):</small>
                    <h4 class="fw-bold text-success mb-0">₹{{ number_format($payment->amount, 2) }}</h4>
                    <small class="text-muted">माध्यम: <span class="badge bg-label-info">{{ $payment->payment_mode }}</span> {{ $payment->reference_no ? '(Ref: ' . $payment->reference_no . ')' : '' }}</small>
                </div>
                <div class="text-end">
                    <small class="text-muted d-block">बाकी राशि (Pending Dues):</small>
                    <h4 class="fw-bold text-danger mb-0">₹{{ number_format($member ? $member->pending_amount : 0, 2) }}</h4>
                </div>
            </div>
            <div class="border-top pt-2 mt-2">
                <small class="text-muted">शब्दों में (In Words):</small>
                <span class="fst-italic fw-semibold text-primary ms-1">{{ $payment->amount_in_words }}</span>
            </div>
        </div>

        <!-- Signatures & Verification -->
        <div class="row pt-2 align-items-end">
            <div class="col-4 text-center">
                <div class="border p-2 d-inline-block rounded bg-white mb-1 shadow-sm">
                    <i class="fas fa-qrcode fs-2 text-primary"></i>
                </div>
                <small class="d-block text-muted" style="font-size: 11px;">SAN Digital Verification</small>
            </div>
            <div class="col-4 text-center">
                <div style="height: 35px;"></div>
                <div class="border-top pt-1">
                    <small class="text-muted d-block">{{ $agentName }}</small>
                    <small class="fw-bold d-block text-heading">कार्यकर्ता का नाम / हस्ताक्षर</small>
                </div>
            </div>
            <div class="col-4 text-center">
                <div style="height: 35px;"></div>
                <div class="border-top pt-1">
                    <small class="fw-bold d-block text-heading">हस्ताक्षर</small>
                    <small class="text-muted" style="font-size: 11px;">श्री श्याम वेलफेयर सोसाइटी</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
