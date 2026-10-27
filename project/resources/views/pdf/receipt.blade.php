<!DOCTYPE html>
<html lang="hi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Official Payment Receipt - {{ $payment->receipt_no }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'mangal', 'aparajita', 'freesans', sans-serif;
            color: #0f172a;
            font-size: 10px;
            line-height: 1.25;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        .receipt-container {
            border: 2px solid #1B365D;
            border-radius: 8px;
            padding: 12px 16px;
            background: #ffffff;
        }
        .header-area {
            text-align: center;
            margin-bottom: 4px;
        }
        .society-name {
            font-size: 18px;
            font-weight: bold;
            color: #1B365D;
            margin: 0;
            line-height: 1.15;
            letter-spacing: 0.3px;
        }
        .society-subtitle {
            font-size: 10.5px;
            color: #475569;
            margin-top: 2px;
        }
        .ganesh-mantra {
            font-size: 11px;
            font-weight: bold;
            color: #b91c1c;
            margin-top: 2px;
        }
        .society-meta {
            font-size: 9px;
            color: #475569;
            margin-top: 3px;
        }
        .receipt-badge {
            display: inline-block;
            background: #1B365D;
            color: #ffffff;
            padding: 3px 18px;
            border-radius: 16px;
            font-size: 10.5px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }
        .divider-line {
            border-bottom: 2px solid #b45309;
            margin-top: 6px;
            margin-bottom: 8px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .info-table td {
            padding: 4px 2px;
            vertical-align: middle;
            font-size: 9.5px;
        }
        .info-label {
            font-weight: bold;
            color: #1e293b;
            white-space: nowrap;
        }
        .info-value {
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px dotted #94a3b8;
        }
        .amount-box {
            background: #eff6ff;
            border: 1.5px solid #3b82f6;
            border-radius: 5px;
            padding: 6px 10px;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .sig-block {
            border-top: 1px dotted #334155;
            font-size: 8.5px;
            font-weight: bold;
            color: #1e3a8a;
            padding-top: 2px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Top Society Header matching authentic receipt template -->
        <div class="header-area">
            <div class="society-name">श्री श्याम वेलफेयर सोसाइटी, लोहिड़ी</div>
            <div class="society-subtitle">ग्राम पंचायत लोहिड़ी, पंचायत समिति सिणधरी, जिला बालोतरा (राजस्थान)</div>
            <div class="ganesh-mantra">श्री गणेशाय नमः</div>
            <div class="society-meta">
                Reg No: <strong>{{ $society['reg_no'] }}</strong> | Helpline: {{ $society['phone'] }}
            </div>
            <div>
                <div class="receipt-badge">OFFICIAL PAYMENT RECEIPT / भुगतान रसीद</div>
            </div>
        </div>

        <!-- Orange / Amber Divider Line -->
        <div class="divider-line"></div>

        <!-- 2-Column Info Grid matching user screenshot -->
        <table class="info-table">
            <!-- Row 1: Receipt No & Payment Date -->
            <tr>
                <td class="info-label" style="width: 22%;">क्र. सं. (Receipt No):</td>
                <td class="info-value" style="width: 26%;">{{ $payment->receipt_no }}</td>
                <td class="info-label" style="width: 24%; padding-left: 10px;">दिनांक (Date):</td>
                <td class="info-value" style="width: 28%;">{{ $paymentDate }}</td>
            </tr>

            <!-- Row 2: SAN Code & Membership No -->
            <tr>
                <td class="info-label">SAN Code (एस.ए.एन. कोड):</td>
                <td class="info-value">{{ $sanCode }}</td>
                <td class="info-label" style="padding-left: 10px;">सदस्यता क्रमांक (Mem. No):</td>
                <td class="info-value">{{ $member ? $member->membership_no : '-' }}</td>
            </tr>

            <!-- Row 3: Member Name & Spouse/Father Name -->
            <tr>
                <td class="info-label">श्री (सदस्य का नाम):</td>
                <td class="info-value">{{ $member ? $member->full_name : '-' }}</td>
                <td class="info-label" style="padding-left: 10px;">पति / पत्नी का नाम:</td>
                <td class="info-value">{{ $member ? $member->father_spouse_name : '-' }}</td>
            </tr>

            <!-- Row 4: Scheme & Mobile Number -->
            <tr>
                <td class="info-label">योजना (Scheme):</td>
                <td class="info-value">{{ $member && $member->scheme ? $member->scheme->name_hindi : 'बुजुर्ग सम्मान योजना' }}</td>
                <td class="info-label" style="padding-left: 10px;">मोबाइल नंबर:</td>
                <td class="info-value">{{ $member ? $member->mobile : '-' }}</td>
            </tr>

            <!-- Row 5: Payment Mode & Agent Name -->
            <tr>
                <td class="info-label">भुगतान माध्यम (Mode):</td>
                <td class="info-value">{{ $payment->payment_mode }}{{ $payment->reference_no ? ' (Ref: ' . $payment->reference_no . ')' : '' }}</td>
                <td class="info-label" style="padding-left: 10px;">कार्यकर्ता का नाम:</td>
                <td class="info-value">{{ $agentName }}</td>
            </tr>
        </table>

        <!-- Amount Box matching user screenshot -->
        <div class="amount-box">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="font-size: 11.5px; font-weight: bold; color: #1e3a8a;">
                        प्राप्त राशि (Amount Received): <span style="font-size: 14px; color: #d97706; font-weight: bold;">₹{{ number_format($payment->amount, 2) }}</span>
                    </td>
                    <td style="text-align: right; font-size: 10.5px; font-weight: bold; color: #334155;">
                        बाकी राशि: ₹{{ number_format($member ? $member->pending_amount : 0, 2) }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="font-size: 9px; color: #334155; padding-top: 3px;">
                        <strong>शब्दों में (In Words):</strong> {{ $payment->amount_in_words }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer Signatures -->
        <table class="footer-table">
            <tr>
                <td style="width: 32%; text-align: center; vertical-align: bottom;">
                    <div style="font-size: 8px; color: #64748b; margin-bottom: 1px;">SAN Digital Verification</div>
                    <div class="sig-block">एस.ए.एन. सत्यापन</div>
                </td>
                <td style="width: 36%; text-align: center; vertical-align: bottom;">
                    <div style="margin-bottom: 8px; font-size: 8.5px; color: #334155; font-weight: bold;">{{ $agentName }}</div>
                    <div class="sig-block">कार्यकर्ता का नाम / हस्ताक्षर</div>
                </td>
                <td style="width: 32%; text-align: right; vertical-align: bottom;">
                    <div style="display: inline-block; text-align: center; width: 130px;">
                        <div style="margin-bottom: 8px; font-size: 8px; color: #64748b;">श्री श्याम वेलफेयर सोसाइटी</div>
                        <div class="sig-block">हस्ताक्षर</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
