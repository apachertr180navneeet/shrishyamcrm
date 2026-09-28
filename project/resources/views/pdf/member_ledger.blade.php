<!DOCTYPE html>
<html lang="hi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Member Ledger Card - {{ $member->membership_no }}</title>
    <style>
        @page {
            margin: 18px 20px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #111;
            font-size: 11px;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .card-container {
            border: 2px solid #222;
            padding: 12px 16px;
            background: #fff;
            position: relative;
        }
        .top-meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 2px;
        }
        .header {
            text-align: center;
            border-bottom: 1.5px solid #222;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .society-title {
            font-size: 18px;
            font-weight: bold;
            color: #000;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .society-subtitle {
            font-size: 10.5px;
            font-weight: bold;
            color: #222;
            margin: 2px 0;
        }
        .society-phones {
            font-size: 10px;
            font-weight: bold;
            color: #333;
            margin: 1px 0;
        }
        .scheme-row-table {
            width: 100%;
            margin: 5px 0 7px 0;
            border-collapse: collapse;
        }
        .scheme-pill {
            display: inline-block;
            border: 1.5px solid #222;
            padding: 2px 16px;
            border-radius: 14px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            background: #f8f8f8;
        }
        .member-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 10.5px;
        }
        .member-info-table td {
            padding: 3px 2px;
            vertical-align: bottom;
        }
        .dotted-val {
            border-bottom: 1px dotted #333;
            padding: 0 4px;
            font-weight: bold;
            color: #000;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 10px;
        }
        .ledger-table th {
            border: 1px solid #000;
            background: #f0f0f0;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 3px;
            text-align: center;
        }
        .ledger-table td {
            border: 1px solid #000;
            font-size: 9.5px;
            padding: 3.5px 4px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .pending-text {
            color: #b91c1c;
            font-weight: bold;
        }
        .paid-text {
            color: #15803d;
            font-weight: bold;
        }
        .footer-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .total-box {
            border: 1.5px solid #000;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
            background: #fafafa;
        }
        .rupee-icon {
            font-size: 16px;
            font-weight: bold;
            margin-right: 4px;
        }
        .signature-cell {
            text-align: right;
            vertical-align: bottom;
            padding-right: 15px;
            font-size: 11px;
            font-weight: bold;
        }
        .bottom-note {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px dashed #666;
            font-size: 9.5px;
            color: #333;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <!-- Top Meta Table: Reg No & SAN -->
        <table class="top-meta-table">
            <tr>
                <td class="text-start" style="width: 50%;">
                    <strong>Reg. No. {{ $society['reg_no'] }}</strong>
                </td>
                <td class="text-end" style="width: 50%;">
                    <strong>SAN : {{ $sanCode }}</strong>
                </td>
            </tr>
        </table>

        <!-- Center Header -->
        <div class="header">
            <div class="society-title">{{ $society['name_hindi'] }}</div>
            <div class="society-subtitle">{{ $society['address'] }}</div>
            <div class="society-phones">मो. {{ $society['phone'] }}</div>
        </div>

        <!-- Scheme Row & Date -->
        <table class="scheme-row-table">
            <tr>
                <td style="width: 25%; font-size: 10px;">
                    <strong>क्र.सं.</strong> <span class="dotted-val">{{ $member->membership_no }}</span>
                </td>
                <td class="text-center" style="width: 50%;">
                    <div class="scheme-pill">
                        {{ $member->scheme ? $member->scheme->name_hindi : 'बुजुर्ग सम्मान योजना' }}
                    </div>
                </td>
                <td class="text-end" style="width: 25%; font-size: 10px;">
                    <strong>दिनांक :-</strong> <span class="dotted-val">{{ date('d.m.Y') }}</span>
                </td>
            </tr>
        </table>

        <!-- Member Info Section -->
        <table class="member-info-table">
            <tr>
                <td style="width: 55%;">
                    श्री/श्रीमती: <span class="dotted-val">{{ $member->full_name }}</span>
                </td>
                <td style="width: 45%;">
                    पिता/पत्नी श्री: <span class="dotted-val">{{ $member->father_spouse_name ?: '-' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    निवासी: <span class="dotted-val">{{ $member->address ?: ($member->village ? $member->village . ', ' . $member->district : $member->district) }}</span>
                </td>
                <td>
                    गौत्र: <span class="dotted-val">{{ $member->gotra ?? ($member->caste ?? 'घुंघेटा') }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 38%; padding: 0;">
                                मोबाईल नम्बर: <span class="dotted-val">{{ $member->mobile }}</span>
                            </td>
                            <td style="width: 42%; padding: 0;">
                                वारिसदार: <span class="dotted-val">{{ $nomineeName }}</span>
                            </td>
                            <td style="width: 20%; padding: 0; text-align: right;">
                                किश्त: <span class="dotted-val">₹{{ number_format($kishtRate, 0) }}</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="width: 60%;">
                    कार्यकर्ता का नाम: <span class="dotted-val">{{ $member->agent ? $member->agent->name : ($member->agent_name ?? 'बुधराम') }}</span>
                </td>
                <td style="width: 40%;">
                    मो.: <span class="dotted-val">{{ $member->agent ? $member->agent->mobile : '9783049650' }}</span>
                </td>
            </tr>
        </table>

        <!-- Events & Contributions Table -->
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width: 6%;">क्र.सं.</th>
                    <th style="width: 44%; text-align: left;">नाम / विवरण</th>
                    <th style="width: 16%;">जुड़ने की तिथी</th>
                    <th style="width: 18%;">भुगतान तिथि</th>
                    <th style="width: 16%;">कार्यकर्ता नाम</th>
                </tr>
            </thead>
            <tbody>
                @php $sr = 1; @endphp
                @foreach($tableRows as $row)
                <tr>
                    <td class="text-center font-weight-bold">{{ $sr++ }}</td>
                    <td>
                        <strong>{{ $row['description'] }}</strong>
                    </td>
                    <td class="text-center">{{ $row['event_date'] }}</td>
                    <td class="text-center">
                        @if($row['is_paid'])
                            <span class="paid-text">{{ $row['payment_date'] }}</span>
                        @else
                            <span class="pending-text">{{ $row['payment_date'] }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $row['agent_name'] }}</td>
                </tr>
                @endforeach

                <!-- Blank Fill Rows to match authentic printed card grid -->
                @for($i = 0; $i < $blankRowsCount; $i++)
                <tr>
                    <td class="text-center" style="color: #999;">{{ $sr++ }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endfor
            </tbody>
        </table>

        <!-- Footer Summary Section -->
        <table class="footer-summary-table">
            <tr>
                <td style="width: 60%; vertical-align: middle;">
                    <div class="total-box">
                        <span class="rupee-icon">₹</span>
                        {{ number_format($totalPaid, 0) }} + {{ number_format($totalPending, 0) }} = {{ number_format($totalExpected, 0) }}
                    </div>
                    <div style="font-size: 8.5px; color: #555; margin-top: 3px;">
                        (जमा राशि: ₹{{ number_format($totalPaid, 0) }} | बकाया राशि: ₹{{ number_format($totalPending, 0) }} | कुल देय: ₹{{ number_format($totalExpected, 0) }})
                    </div>
                </td>
                <td class="signature-cell" style="width: 40%;">
                    <div style="margin-bottom: 25px;"></div>
                    <div>......................................</div>
                    <div>हस्ताक्षर / Authorized Signatory</div>
                </td>
            </tr>
        </table>

        <!-- Bottom Detailed Breakdown Note -->
        <div class="bottom-note">
            <strong>किस्त विवरण:</strong>
            कुल देय ₹{{ number_format($totalExpected, 0) }} &nbsp;|&nbsp;
            कुल जमा ₹{{ number_format($totalPaid, 0) }} &nbsp;|&nbsp;
            <strong>शेष बकाया ₹{{ number_format($totalPending, 0) }}</strong>
        </div>
    </div>
</body>
</html>
