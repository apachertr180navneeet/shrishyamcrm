<!DOCTYPE html>
<html lang="hi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt - {{ $payment->receipt_no }}</title>
    <style>
        @page { margin: 15px; }
        @font-face {
            font-family: 'Mangal';
            font-style: normal;
            font-weight: 400;
            src: url('{{ $mangalPath }}') format('truetype');
        }
        @font-face {
            font-family: 'Mangal';
            font-style: normal;
            font-weight: 700;
            src: url('{{ $mangalbPath }}') format('truetype');
        }
        @font-face {
            font-family: 'Aparajita';
            font-style: normal;
            font-weight: 400;
            src: url('{{ $aparajPath }}') format('truetype');
        }
        @font-face {
            font-family: 'Aparajita';
            font-style: normal;
            font-weight: 700;
            src: url('{{ $aparajbPath }}') format('truetype');
        }
        body {
            font-family: 'Mangal', 'Aparajita', 'DejaVu Sans', sans-serif;
            color: #1a1a1a;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .receipt-container {
            border: 2px solid #1B365D;
            padding: 15px;
            background: #fff;
            border-radius: 6px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #D97706;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .society-title {
            font-size: 18px;
            font-weight: bold;
            color: #1B365D;
            margin: 0;
        }
        .society-subtitle {
            font-size: 13px;
            font-weight: bold;
            color: #D97706;
            margin: 3px 0;
        }
        .society-meta {
            font-size: 10px;
            color: #555;
        }
        .badge {
            background: #1B365D;
            color: #fff;
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 11px;
            display: inline-block;
            margin-top: 5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #333;
            width: 25%;
        }
        .value {
            color: #111;
            border-bottom: 1px dotted #ccc;
        }
        .amount-box {
            background: #f0f7ff;
            border: 1px solid #2563EB;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .amount-words {
            font-size: 11px;
            color: #1B365D;
            font-style: italic;
            margin-top: 4px;
        }
        .footer-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .signature-box {
            text-align: center;
            font-size: 11px;
            color: #444;
            padding-top: 20px;
            border-top: 1px solid #666;
            width: 30%;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1 class="society-title">श्री श्याम वेलफेयर सोसाइटी, लोहिड़ी</h1>
            <div class="society-subtitle" style="color: #4b5563; font-weight: 600; font-size: 11px;">ग्राम पंचायत लोहिड़ी, पंचायत समिति सिणधरी, जिला बालोतरा (राजस्थान)</div>
            <div style="color: #b91c1c; font-weight: bold; font-size: 11px; margin: 2px 0;">श्री गणेशाय नमः</div>
            <div class="society-meta">
                Reg No: <strong>{{ $society['reg_no'] }}</strong> | Helpline: {{ $society['phone'] }}
            </div>
            <div class="badge">OFFICIAL PAYMENT RECEIPT / भुगतान रसीद</div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">क्र. सं. (Receipt No):</td>
                <td class="value"><strong>{{ $payment->receipt_no }}</strong></td>
                <td class="label">दिनांक (Date):</td>
                <td class="value"><strong>{{ $payment->payment_date->format('d-M-Y') }}</strong></td>
            </tr>
            <tr>
                <td class="label">SAN Code (एस.ए.एन. कोड):</td>
                <td class="value">{{ $payment->san_code ?: 'SAN-LOH-'.$payment->member_id }}</td>
                <td class="label">सदस्यता क्रमांक (Mem. No):</td>
                <td class="value"><strong>{{ $payment->member->membership_no }}</strong></td>
            </tr>
            <tr>
                <td class="label">श्री (सदस्य का नाम):</td>
                <td class="value"><strong>{{ $payment->member->full_name }}</strong></td>
                <td class="label">पति / पत्नी का नाम:</td>
                <td class="value">{{ $payment->member->father_spouse_name ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">योजना (Scheme):</td>
                <td class="value">{{ $payment->member->scheme ? $payment->member->scheme->name_hindi : 'बुजुर्ग सम्मान योजना' }}</td>
                <td class="label">मोबाइल नंबर:</td>
                <td class="value">{{ $payment->member->mobile }}</td>
            </tr>
            <tr>
                <td class="label">भुगतान माध्यम (Mode):</td>
                <td class="value">{{ $payment->payment_mode }} (Ref: {{ $payment->reference_no ?: 'N/A' }})</td>
                <td class="label">कार्यकर्ता का नाम:</td>
                <td class="value">{{ $payment->agent ? $payment->agent->name : ($payment->collected_by ?: 'HQ Office') }}</td>
            </tr>
        </table>

        <div class="amount-box">
            <table style="width: 100%;">
                <tr>
                    <td style="font-size: 14px; font-weight: bold; color: #1B365D;">
                        प्राप्त राशि (Amount Received): <span style="font-size: 16px; color: #D97706;">₹{{ number_format($payment->amount, 2) }}</span>
                    </td>
                    <td style="text-align: right; font-size: 12px; font-weight: bold; color: #555;">
                        बाकी राशि: ₹{{ number_format($payment->member->pending_amount, 2) }}
                    </td>
                </tr>
            </table>
            <div class="amount-words">
                <strong>शब्दों में:</strong> {{ $payment->amount_in_words }}
            </div>
        </div>

        <table class="footer-table">
            <tr>
                <td class="signature-box">
                    हस्ताक्षर<br>(सदस्य)
                </td>
                <td style="width: 40%; text-align: center; font-size: 9px; color: #777;">
                    Computer generated receipt valid without physical seal.<br>
                    ई-रसीद समाज सेवा एवं रिकॉर्ड हेतु मान्य है।
                </td>
                <td class="signature-box">
                    हस्ताक्षर<br>({{ $society['president'] }})
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
