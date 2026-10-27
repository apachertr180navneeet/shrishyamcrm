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
            padding: 14px 18px;
            background: #ffffff;
            position: relative;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
        }
        .society-title {
            font-size: 19px;
            font-weight: bold;
            color: #1B365D;
            line-height: 1.2;
            margin: 0;
        }
        .society-sub {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 2px;
        }
        .ganesh-mantra {
            font-size: 12.5px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .badge-official {
            background-color: #6366f1;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: bold;
            padding: 3px 10px;
            border-radius: 4px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .reg-text {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 4px;
        }
        .san-text {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 2px;
        }
        .divider-line {
            border-bottom: 1px solid #e2e8f0;
            margin: 6px 0 10px 0;
        }
        .meta-box-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #ffffff;
            margin-bottom: 10px;
        }
        .meta-box-table td {
            padding: 6px 12px;
            vertical-align: middle;
        }
        .scheme-pill {
            background-color: #e0f2fe;
            color: #0284c7;
            font-size: 11px;
            font-weight: bold;
            padding: 4px 14px;
            border-radius: 4px;
            display: inline-block;
        }
        .info-grid-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            margin-bottom: 10px;
        }
        .info-grid-table th,
        .info-grid-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 9.5px;
            vertical-align: middle;
        }
        .info-grid-table th {
            background-color: #f8fafc;
            color: #334155;
            font-weight: bold;
            text-align: left;
        }
        .info-grid-table td {
            background-color: #ffffff;
            color: #0f172a;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            margin-bottom: 10px;
        }
        .details-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: bold;
            padding: 7px 8px;
            border: 1px solid #334155;
        }
        .details-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            font-size: 9.5px;
            vertical-align: middle;
        }
        .date-badge {
            background-color: #22c55e;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        .amount-summary-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #fafafa;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .amount-summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .amount-summary-table td {
            padding: 0;
            vertical-align: top;
        }
        .amount-received-val {
            font-size: 17px;
            font-weight: bold;
            color: #16a34a;
            margin: 2px 0;
        }
        .amount-pending-val {
            font-size: 17px;
            font-weight: bold;
            color: #ea580c;
            margin: 2px 0;
        }
        .mode-badge {
            background-color: #e0f2fe;
            color: #0284c7;
            padding: 1px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
        }
        .in-words-row {
            border-top: 1px solid #e2e8f0;
            margin-top: 6px;
            padding-top: 6px;
            font-size: 9.5px;
        }
        .footer-signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .footer-signatures-table td {
            vertical-align: bottom;
            text-align: center;
            padding: 0;
        }
        .sig-line {
            border-top: 1px solid #94a3b8;
            width: 75%;
            margin: 0 auto;
            padding-top: 3px;
        }
        .qr-icon-box {
            display: inline-block;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 3px;
            background-color: #ffffff;
            margin-bottom: 2px;
        }
        .qr-dots {
            width: 22px;
            height: 22px;
            border-collapse: separate;
            border-spacing: 2px;
            margin: 0 auto;
        }
        .qr-dot {
            width: 9px;
            height: 9px;
            background-color: #2563eb;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Top Society Header -->
        <table class="header-table">
            <tr>
                <td style="width: 65px; text-align: left;">
                    @if(file_exists($logoPath))
                        <img src="{{ $logoPath }}" style="width: 58px; height: 58px; border-radius: 50%; object-fit: contain;">
                    @endif
                </td>
                <td style="text-align: center; padding: 0 8px;">
                    <div class="ganesh-mantra">श्री गणेशाय नमः</div>
                    <div class="society-title">{{ $society['name_hindi'] }}</div>
                    <div class="society-sub">{{ $society['address'] }}</div>
                </td>
                <td style="width: 170px; text-align: right;">
                    <div class="badge-official">OFFICIAL RECEIPT</div>
                    <div class="reg-text">Reg No: <strong style="color: #334155;">{{ $society['reg_no'] }}</strong></div>
                    <div class="san-text">SAN: <strong style="color: #334155;">{{ $sanCode }}</strong></div>
                </td>
            </tr>
        </table>

        <!-- Divider Line -->
        <div class="divider-line"></div>

        <!-- Receipt No, Scheme, Date Box -->
        <table class="meta-box-table">
            <tr>
                <td style="width: 32%;">
                    <div style="font-size: 9px; color: #64748b;">क्र. सं. (Receipt No):</div>
                    <div style="font-size: 13px; font-weight: bold; color: #2563eb; margin-top: 1px;">{{ $payment->receipt_no }}</div>
                </td>
                <td style="width: 36%; text-align: center;">
                    <div class="scheme-pill">
                        {{ $member && $member->scheme ? $member->scheme->name_hindi : 'बुजुर्ग सम्मान योजना' }}
                    </div>
                </td>
                <td style="width: 32%; text-align: right;">
                    <div style="font-size: 9px; color: #64748b;">दिनांक (Date):</div>
                    <div style="font-size: 13px; font-weight: bold; color: #1e293b; margin-top: 1px;">{{ $paymentDate }}</div>
                </td>
            </tr>
        </table>

        <!-- 5-Row Member Info Grid -->
        <table class="info-grid-table">
            <!-- Row 1: Member Name & Spouse/Father Name -->
            <tr>
                <th style="width: 20%;">श्री</th>
                <td style="width: 30%;"><strong>{{ $member ? $member->full_name : '-' }}</strong></td>
                <th style="width: 24%;">पति / पत्नी का नाम</th>
                <td style="width: 26%;">{{ $member ? $member->father_spouse_name : '-' }}</td>
            </tr>

            <!-- Row 2: Address & Gotra -->
            <tr>
                <th>निवासी</th>
                <td>{{ $member ? ($member->address ?: ($member->village ? $member->village . ', ' . $member->district : $member->district)) : '-' }}</td>
                <th>गोत्र</th>
                <td>{{ $member ? ($member->gotra ?? ($member->caste ?? '-')) : '-' }}</td>
            </tr>

            <!-- Row 3: Mobile Number & Kisht Amount -->
            <tr>
                <th>मोबाइल नंबर</th>
                <td>{{ $member ? $member->mobile : '-' }}</td>
                <th>किस्त</th>
                <td><strong style="color: #2563eb; font-size: 11px;">₹{{ number_format($kishtRate, 0) }}</strong></td>
            </tr>

            <!-- Row 4: Agent Name & Agent Mobile -->
            <tr>
                <th>कार्यकर्ता का नाम</th>
                <td>{{ $agentName }}</td>
                <th>मो.</th>
                <td>{{ $agentMobile }}</td>
            </tr>

            <!-- Row 5: Nominee Name & Joining Date -->
            <tr>
                <th>वारिसदार का नाम</th>
                <td>{{ $nomineeName }}</td>
                <th>जुड़ने की तिथि</th>
                <td>{{ $joiningDate }}</td>
            </tr>
        </table>

        <!-- Event / Contribution Details Table (Dark Header) -->
        <table class="details-table">
            <thead>
                <tr>
                    <th style="width: 44%; text-align: left;">नाम / विवरण</th>
                    <th style="width: 18%; text-align: center;">कार्यक्रम की दिनांक</th>
                    <th style="width: 18%; text-align: center;">भुगतान की तिथि</th>
                    <th style="width: 20%; text-align: center;">कार्यकर्ता का नाम</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight: bold; color: #334155;">{{ $description }}</td>
                    <td style="text-align: center; color: #334155;">{{ $eventDate }}</td>
                    <td style="text-align: center;">
                        <span class="date-badge">{{ $paymentDate }}</span>
                    </td>
                    <td style="text-align: center; color: #334155;">{{ $agentName }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Financial Summary Box -->
        <div class="amount-summary-box">
            <table class="amount-summary-table">
                <tr>
                    <td>
                        <div style="font-size: 9.5px; color: #64748b;">प्राप्त राशि (Amount Received):</div>
                        <div class="amount-received-val">₹{{ number_format($payment->amount, 2) }}</div>
                        <div style="font-size: 9px; color: #64748b; margin-top: 1px;">
                            माध्यम: <span class="mode-badge">{{ $payment->payment_mode }}</span>
                            @if($payment->reference_no)
                                <span style="margin-left: 3px;">(Ref: {{ $payment->reference_no }})</span>
                            @endif
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <div style="font-size: 9.5px; color: #64748b;">बाकी राशि (Pending Dues):</div>
                        <div class="amount-pending-val">₹{{ number_format($member ? $member->pending_amount : 0, 2) }}</div>
                    </td>
                </tr>
            </table>
            <div class="in-words-row">
                <span style="color: #64748b;">शब्दों में (In Words):</span>
                <span style="color: #2563eb; font-weight: bold; font-style: italic; margin-left: 3px;">{{ $payment->amount_in_words }}</span>
            </div>
        </div>

        <!-- Footer Signatures -->
        <table class="footer-signatures-table">
            <tr>
                <td style="width: 32%;">
                    <div class="qr-icon-box">
                        <table class="qr-dots">
                            <tr>
                                <td class="qr-dot"></td>
                                <td class="qr-dot"></td>
                            </tr>
                            <tr>
                                <td class="qr-dot"></td>
                                <td class="qr-dot"></td>
                            </tr>
                        </table>
                    </div>
                    <div style="font-size: 8px; color: #64748b;">SAN Digital Verification</div>
                </td>
                <td style="width: 36%;">
                    <div style="font-size: 9px; color: #64748b; margin-bottom: 3px;">{{ $agentName }}</div>
                    <div class="sig-line">
                        <div style="font-size: 9px; font-weight: bold; color: #334155;">कार्यकर्ता का नाम / हस्ताक्षर</div>
                    </div>
                </td>
                <td style="width: 32%;">
                    <div style="height: 14px;"></div>
                    <div class="sig-line">
                        <div style="font-size: 10px; font-weight: bold; color: #1e3a8a;">हस्ताक्षर</div>
                        <div style="font-size: 8px; color: #64748b; margin-top: 1px;">श्री श्याम वेलफेयर सोसाइटी</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
