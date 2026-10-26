<!DOCTYPE html>
<html lang="hi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt - {{ $payment->receipt_no }}</title>
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
        .receipt-outer-frame {
            border: 2px solid #1e3a8a;
            padding: 3px;
            border-radius: 5px;
            background: #ffffff;
        }
        .receipt-inner-frame {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            border-radius: 4px;
            background: #ffffff;
        }
        .top-meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-bottom: 2px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 3px;
            margin-bottom: 3px;
        }
        .society-title {
            font-size: 17px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            line-height: 1.15;
            letter-spacing: 0.5px;
        }
        .society-subtitle {
            font-size: 9.5px;
            font-weight: bold;
            color: #334155;
            margin-top: 1px;
            margin-bottom: 0;
        }
        .ganesh-mantra {
            font-size: 10.5px;
            font-weight: bold;
            color: #b91c1c;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }
        .scheme-row-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2px 0 4px 0;
        }
        .scheme-pill {
            display: inline-block;
            border: 1.5px solid #1e3a8a;
            padding: 2px 16px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            color: #1e3a8a;
            background: #f8fafc;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .info-table td {
            padding: 3px 2px;
            vertical-align: middle;
        }
        .field-label {
            color: #334155;
            font-weight: bold;
            white-space: nowrap;
            font-size: 9.5px;
        }
        .field-value {
            border-bottom: 1.5px dotted #64748b;
            color: #0f172a;
            font-weight: bold;
            font-size: 9.5px;
            padding: 0 4px;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .detail-table th {
            background: #1e3a8a;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 2.5px 4px;
            border: 1px solid #1e3a8a;
            text-align: center;
        }
        .detail-table td {
            border: 1px solid #94a3b8;
            font-size: 9px;
            padding: 2.5px 5px;
            vertical-align: middle;
        }
        .amount-summary-box {
            background: #f8fafc;
            border: 1.5px solid #1e3a8a;
            border-radius: 4px;
            padding: 4px 8px;
            margin-bottom: 4px;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .sig-block {
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            color: #1e3a8a;
            border-top: 1.5px dotted #475569;
            padding-top: 2px;
        }
    </style>
</head>
<body>
    <div class="receipt-outer-frame">
        <div class="receipt-inner-frame">
            <!-- Top Meta Bar: Reg No | SAN -->
            <table class="top-meta-table">
                <tr>
                    <td style="width: 50%; text-align: left; color: #1e3a8a;">
                        <strong>Reg. No. {{ $society['reg_no'] }}</strong>
                    </td>
                    <td style="width: 50%; text-align: right; color: #1e3a8a;">
                        <strong>SAN : {{ $sanCode }}</strong>
                    </td>
                </tr>
            </table>

            <!-- Header Section with Centered Title & Below Header Ganesh Mantra -->
            <table class="header-table">
                <tr>
                    <td style="width: 55px; vertical-align: middle; text-align: left; padding-bottom: 2px;">
                        @if(file_exists($logoPath))
                            <img src="{{ str_replace('\\', '/', $logoPath) }}" width="48" height="48" alt="Logo" style="display: block; border-radius: 24px;"/>
                        @endif
                    </td>
                    <td style="vertical-align: middle; text-align: center; padding-bottom: 2px;">
                        <div class="society-title">श्री श्याम वेलफेयर सोसाइटी, लोहिड़ी</div>
                        <div class="society-subtitle">ग्राम पंचायत लोहिड़ी, पंचायत समिति सिणधरी, जिला बालोतरा (राजस्थान)</div>
                        <div class="ganesh-mantra">श्री गणेशाय नमः</div>
                    </td>
                    <td style="width: 55px; vertical-align: middle; text-align: right; font-size: 8.5px; color: #1e3a8a; padding-bottom: 2px;">
                        <div style="font-weight: bold; line-height: 1.2;">मो.<br>{{ explode(',', $society['phone'])[0] ?? '9664090906' }}</div>
                    </td>
                </tr>
            </table>

            <!-- Scheme Row & Date -->
            <table class="scheme-row-table">
                <tr>
                    <td style="width: 28%; font-size: 9.5px; vertical-align: middle;">
                        <strong style="color: #334155;">क्र. सं.</strong> <span style="font-weight: bold; color: #0f172a; border-bottom: 1px dotted #475569; padding: 0 4px;">{{ $payment->receipt_no }}</span>
                    </td>
                    <td class="text-center" style="width: 44%; vertical-align: middle; text-align: center;">
                        <div class="scheme-pill">
                            {{ $member && $member->scheme ? $member->scheme->name_hindi : 'बुजुर्ग सम्मान योजना' }}
                        </div>
                    </td>
                    <td style="width: 28%; font-size: 9.5px; vertical-align: middle; text-align: right;">
                        <strong style="color: #334155;">दिनांक</strong> <span style="font-weight: bold; color: #0f172a; border-bottom: 1px dotted #475569; padding: 0 4px;">{{ $paymentDate }}</span>
                    </td>
                </tr>
            </table>

            <!-- Member Info 5-Row Section with Dotted Underline (Direct 4-Column Table) -->
            <table class="info-table">
                <!-- Row 1: Member Name & Spouse/Father Name -->
                <tr>
                    <td class="field-label" style="width: 8%;">श्री</td>
                    <td class="field-value" style="width: 44%;">{{ $member ? $member->full_name : '-' }}</td>
                    <td class="field-label" style="width: 20%; padding-left: 10px;">पति / पत्नी का नाम</td>
                    <td class="field-value" style="width: 28%;">{{ $member ? $member->father_spouse_name : '-' }}</td>
                </tr>

                <!-- Row 2: Address & Gotra -->
                <tr>
                    <td class="field-label">निवासी</td>
                    <td class="field-value">{{ $member ? ($member->address ?: ($member->village ? $member->village . ', ' . $member->district : $member->district)) : '-' }}</td>
                    <td class="field-label" style="padding-left: 10px;">गोत्र</td>
                    <td class="field-value">{{ $member ? ($member->gotra ?? ($member->caste ?? '-')) : '-' }}</td>
                </tr>

                <!-- Row 3: Mobile Number & Kisht Amount -->
                <tr>
                    <td class="field-label">मोबाइल नंबर</td>
                    <td class="field-value">{{ $member ? $member->mobile : '-' }}</td>
                    <td class="field-label" style="padding-left: 10px;">किस्त</td>
                    <td class="field-value">₹{{ number_format($kishtRate, 0) }}</td>
                </tr>

                <!-- Row 4: Agent Name & Agent Mobile -->
                <tr>
                    <td class="field-label">कार्यकर्ता का नाम</td>
                    <td class="field-value">{{ $agentName }}</td>
                    <td class="field-label" style="padding-left: 10px;">मो.</td>
                    <td class="field-value">{{ $agentMobile }}</td>
                </tr>

                <!-- Row 5: Nominee Name & Joining Date -->
                <tr>
                    <td class="field-label">वारिसदार का नाम</td>
                    <td class="field-value">{{ $nomineeName }}</td>
                    <td class="field-label" style="padding-left: 10px;">जुड़ने की तिथि</td>
                    <td class="field-value">{{ $joiningDate }}</td>
                </tr>
            </table>

            <!-- Detail Table: Name / Description | Event Date | Payment Date | Agent -->
            <table class="detail-table">
                <thead>
                    <tr>
                        <th style="width: 45%;">नाम / विवरण</th>
                        <th style="width: 18%;">कार्यक्रम की दिनांक</th>
                        <th style="width: 18%;">भुगतान की तिथि</th>
                        <th style="width: 19%;">कार्यकर्ता का नाम</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background: #ffffff;">
                        <td><strong>{{ $description }}</strong></td>
                        <td style="text-align: center;">{{ $eventDate }}</td>
                        <td style="text-align: center; color: #166534; font-weight: bold;">{{ $paymentDate }}</td>
                        <td style="text-align: center;">{{ $agentName }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Financial Box: Amount Received, In Words & Remaining Dues -->
            <div class="amount-summary-box">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-size: 11px; font-weight: bold; color: #1e3a8a;">
                            प्राप्त राशि: <span style="font-size: 13px; color: #166534;">₹{{ number_format($payment->amount, 2) }}</span>
                            <span style="font-size: 9px; color: #475569; font-weight: normal; margin-left: 6px;">({{ $payment->payment_mode }}{{ $payment->reference_no ? ' - Ref: ' . $payment->reference_no : '' }})</span>
                        </td>
                        <td style="text-align: right; font-size: 10.5px; font-weight: bold; color: #b91c1c;">
                            बाकी राशि: ₹{{ number_format($member ? $member->pending_amount : 0, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 9px; color: #334155; padding-top: 2px;">
                            <strong>शब्दों में:</strong> {{ $payment->amount_in_words }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Signatures Table -->
            <table class="footer-table">
                <tr>
                    <td style="width: 32%; text-align: center; vertical-align: bottom;">
                        <div style="font-size: 8px; color: #64748b; margin-bottom: 1px;">SAN Digital Verification</div>
                        <div style="border-top: 1.5px dotted #64748b; font-size: 8.5px; font-weight: bold; color: #1e3a8a; padding-top: 2px;">
                            एस.ए.एन. सत्यापन
                        </div>
                    </td>
                    <td style="width: 36%; text-align: center; vertical-align: bottom;">
                        <div style="margin-bottom: 12px; font-size: 9px; color: #334155; font-weight: bold;">{{ $agentName }}</div>
                        <div class="sig-block">कार्यकर्ता का नाम / हस्ताक्षर</div>
                    </td>
                    <td style="width: 32%; text-align: right; vertical-align: bottom;">
                        <div style="display: inline-block; text-align: center; width: 120px;">
                            <div style="margin-bottom: 12px; font-size: 8.5px; color: #64748b;">श्री श्याम वेलफेयर सोसाइटी</div>
                            <div class="sig-block">हस्ताक्षर</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
