<!DOCTYPE html>
<html lang="hi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Member Ledger Card - {{ $member->membership_no }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'mangal', 'aparajita', 'freesans', sans-serif;
            color: #0f172a;
            font-size: 10px;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @media print {
            body {
                background: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            .card-outer-frame {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
        .card-outer-frame {
            border: 2px solid #1e3a8a;
            padding: 3px;
            background: #ffffff;
            border-radius: 5px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .card-inner-frame {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            background: #ffffff;
            border-radius: 4px;
        }
        .top-meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-bottom: 2px;
        }
        .top-meta-table td {
            padding: 0;
            vertical-align: middle;
        }
        .ganesh-mantra {
            color: #b91c1c;
            font-weight: bold;
            font-size: 10.5px;
            letter-spacing: 0.5px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 3px;
        }
        .society-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            letter-spacing: 0.5px;
            line-height: 1.15;
        }
        .society-subtitle {
            font-size: 10px;
            font-weight: bold;
            color: #334155;
            margin-top: 1px;
            margin-bottom: 0;
        }
        .society-phones {
            font-size: 10px;
            font-weight: bold;
            color: #1e293b;
            margin-top: 1px;
            margin-bottom: 0;
        }
        .scheme-row-table {
            width: 100%;
            margin: 4px 0 6px 0;
            border-collapse: collapse;
        }
        .scheme-pill {
            display: inline-block;
            border: 1.8px solid #1e3a8a;
            padding: 2px 20px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            color: #1e3a8a;
            background: #f8fafc;
        }
        .member-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 10.5px;
        }
        .field-label {
            color: #334155;
            font-weight: bold;
            white-space: nowrap;
            padding-right: 4px;
            font-size: 10.5px;
        }
        .field-value {
            border-bottom: 1.5px dotted #475569;
            width: 100%;
            font-weight: bold;
            color: #0f172a;
            font-size: 10.5px;
            padding: 0 2px;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
            margin-bottom: 6px;
        }
        .ledger-table th {
            border: 1px solid #1e3a8a;
            background: #1e3a8a;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            padding: 3.5px 2px;
            text-align: center;
        }
        .ledger-table td {
            border: 1px solid #334155;
            font-size: 9.5px;
            padding: 2.5px 3px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .pending-text {
            color: #dc2626;
            font-weight: bold;
        }
        .paid-text {
            color: #166534;
            font-weight: bold;
        }
        .footer-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .summary-box {
            border: 1.5px solid #1e3a8a;
            border-radius: 4px;
            background: #f8fafc;
            border-collapse: separate;
            border-spacing: 0;
            display: inline-table;
        }
        .summary-box-header {
            padding: 2px 6px 1px 6px;
            text-align: center;
            font-size: 8.5px;
            font-weight: bold;
            border-bottom: 1px solid #cbd5e1;
            background: #e2e8f0;
            color: #1e293b;
        }
        .summary-box-val {
            padding: 3px 8px;
            text-align: center;
            font-size: 13.5px;
            font-weight: bold;
            color: #1e3a8a;
            line-height: 1.1;
        }
        .summary-box-due {
            border-color: #dc2626;
        }
        .summary-box-due .summary-box-header {
            background: #fee2e2;
            border-bottom-color: #fecaca;
            color: #991b1b;
        }
        .summary-box-due .summary-box-val {
            color: #b91c1c;
        }
        .summary-box-total {
            border-color: #1e3a8a;
            background: #1e3a8a;
        }
        .summary-box-total .summary-box-header {
            background: #1e3a8a;
            border-bottom: 1px solid #3b82f6;
            color: #93c5fd;
        }
        .summary-box-total .summary-box-val {
            color: #ffffff;
            font-size: 14.5px;
        }
    </style>
</head>
<body>
    <div class="card-outer-frame">
        <div class="card-inner-frame">
            <!-- Top Meta Bar: Reg No | SAN -->
            <table class="top-meta-table">
                <tr>
                    <td class="text-start" style="width: 50%; color: #1e3a8a;">
                        <strong>Reg. No. {{ $society['reg_no'] }}</strong>
                    </td>
                    <td class="text-end" style="width: 50%; color: #1e3a8a;">
                        <strong>SAN : {{ $sanCode }}</strong>
                    </td>
                </tr>
            </table>

            <!-- Center Header with Left Logo Emblem -->
            <table class="header-table">
                <tr>
                    <td style="width: 65px; vertical-align: middle; text-align: left; padding-bottom: 2px;">
                        @php
                            $logoImg = $logoPath ?? public_path('assets/society_logo.jpg');
                            if (!file_exists($logoImg)) {
                                $logoImg = public_path('assets/society_logo.png');
                            }
                        @endphp
                        @if(file_exists($logoImg))
                            <img src="{{ str_replace('\\', '/', $logoImg) }}" width="55" height="55" alt="Logo" style="display: block; border-radius: 28px;"/>
                        @endif
                    </td>
                    <td style="vertical-align: middle; text-align: center; padding-bottom: 2px;">
                        <div class="society-title">श्री श्याम वेलफेयर सोसाइटी, लोहिड़ी</div>
                        <div class="society-subtitle">ग्राम पंचायत लोहिड़ी, पंचायत समिति सिणधरी, जिला बालोतरा (राजस्थान)</div>
                        <div class="ganesh-mantra" style="font-size: 11px; font-weight: bold; color: #b91c1c; margin-top: 2px; letter-spacing: 0.5px;">श्री गणेशाय नमः</div>
                    </td>
                    <td style="width: 65px; vertical-align: middle; text-align: right; font-size: 9px; color: #1e3a8a; padding-bottom: 2px;">
                        <div style="font-weight: bold; line-height: 1.25;">मो.<br>{{ explode(',', $society['phone'])[0] ?? '9664090906' }}</div>
                    </td>
                </tr>
            </table>

            <!-- Scheme Row & Date -->
            <table class="scheme-row-table">
                <tr>
                    <td style="width: 26%; font-size: 10px; vertical-align: middle;">
                        <strong style="color: #334155;">क्र. सं.</strong> <span style="font-weight: bold; color: #0f172a; border-bottom: 1px dotted #475569; padding: 0 4px;">{{ $member->membership_no }}</span>
                    </td>
                    <td class="text-center" style="width: 48%; vertical-align: middle;">
                        <div class="scheme-pill">
                            {{ $member->scheme ? $member->scheme->name_hindi : 'बुजुर्ग सम्मान योजना' }}
                        </div>
                    </td>
                    <td class="text-end" style="width: 26%; font-size: 10px; vertical-align: middle;">
                        <strong style="color: #334155;">दिनांक</strong> <span style="font-weight: bold; color: #0f172a; border-bottom: 1px dotted #475569; padding: 0 4px;">{{ date('d.m.Y') }}</span>
                    </td>
                </tr>
            </table>

            <!-- Member Info Section with 5 Complete Dotted Underline Rows -->
            <table class="member-info-table">
                <!-- Row 1: Member Name & Spouse/Father Name -->
                <tr>
                    <td class="field-label" style="width: 8%;">श्री</td>
                    <td class="field-value" style="width: 44%;">{{ $member->full_name }}</td>
                    <td class="field-label" style="width: 20%; padding-left: 10px;">पति / पत्नी का नाम</td>
                    <td class="field-value" style="width: 28%;">{{ $member->father_spouse_name ?: '-' }}</td>
                </tr>

                <!-- Row 2: Address & Gotra -->
                <tr>
                    <td class="field-label">निवासी</td>
                    <td class="field-value">{{ $member->address ?: ($member->village ? $member->village . ', ' . $member->district : $member->district) }}</td>
                    <td class="field-label" style="padding-left: 10px;">गोत्र</td>
                    <td class="field-value">{{ $member->gotra ?? ($member->caste ?? '-') }}</td>
                </tr>

                <!-- Row 3: Mobile Number & Kisht Amount -->
                <tr>
                    <td class="field-label">मोबाइल नंबर</td>
                    <td class="field-value">{{ $member->mobile }}</td>
                    <td class="field-label" style="padding-left: 10px;">किस्त</td>
                    <td class="field-value">₹{{ number_format($kishtRate, 0) }}</td>
                </tr>

                <!-- Row 4: Agent Name & Agent Mobile -->
                <tr>
                    <td class="field-label">कार्यकर्ता का नाम</td>
                    <td class="field-value">{{ $member->agent ? $member->agent->name : ($member->agent_name ?? '-') }}</td>
                    <td class="field-label" style="padding-left: 10px;">मो.</td>
                    <td class="field-value">{{ $member->agent ? $member->agent->mobile : ($society['phone'] ? explode(',', $society['phone'])[0] : '') }}</td>
                </tr>

                <!-- Row 5: Nominee Name & Joining Date -->
                <tr>
                    <td class="field-label">वारिसदार का नाम</td>
                    <td class="field-value">{{ $nomineeName }}</td>
                    <td class="field-label" style="padding-left: 10px;">जुड़ने की तिथि</td>
                    <td class="field-value">{{ $member->joining_date ? $member->joining_date->format('d.m.Y') : ($member->created_at ? $member->created_at->format('d.m.Y') : '-') }}</td>
                </tr>
            </table>

            <!-- Contributions Ledger 20-Row Solid Border Table -->
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th style="width: 6%;">क्र. सं.</th>
                        <th style="width: 44%;">नाम / विवरण</th>
                        <th style="width: 16%;">कार्यक्रम की दिनांक</th>
                        <th style="width: 18%;">भुगतान की तिथि</th>
                        <th style="width: 16%;">कार्यकर्ता का नाम</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sr = 1; @endphp
                    @foreach($tableRows as $row)
                    <tr style="height: 17px; background: #ffffff;">
                        <td class="text-center font-weight-bold" style="color: #1e3a8a;">{{ $sr++ }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $row['description'] }}</strong>
                        </td>
                        <td class="text-center" style="color: #334155;">{{ $row['event_date'] }}</td>
                        <td class="text-center">
                            @if($row['is_paid'])
                                <span class="paid-text">{{ $row['payment_date'] }}</span>
                            @else
                                <span class="pending-text">{{ $row['payment_date'] }}</span>
                            @endif
                        </td>
                        <td class="text-center" style="color: #334155;">{{ $row['agent_name'] }}</td>
                    </tr>
                    @endforeach

                    <!-- Blank Fill Rows to form 20-row authentic printed ledger grid -->
                    @for($i = 0; $i < $blankRowsCount; $i++)
                    <tr style="height: 17px; background: #ffffff;">
                        <td class="text-center">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    @endfor
                </tbody>
            </table>

            <!-- Footer Summary Section: 3-Box Formula [This Month] + [Remaining Dues / बाकी राशि] = [Total] & Signature Line -->
            <table class="footer-summary-table">
                <tr>
                    <td style="width: 72%; vertical-align: middle;">
                        <table style="border-collapse: separate; border-spacing: 0;">
                            <tr>
                                <!-- Box 1: This Month -->
                                <td style="vertical-align: middle;">
                                    <table class="summary-box" style="min-width: 95px;">
                                        <tr>
                                            <td class="summary-box-header">इस माह (This m.)</td>
                                        </tr>
                                        <tr>
                                            <td class="summary-box-val">
                                                ₹{{ number_format($thisMonthAmount, 0) }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <td style="padding: 0 6px; font-size: 16px; font-weight: bold; color: #1e3a8a; vertical-align: middle; text-align: center;">
                                    +
                                </td>

                                <!-- Box 2: Previous Due / बाकी राशि -->
                                <td style="vertical-align: middle;">
                                    <table class="summary-box summary-box-due" style="min-width: 95px;">
                                        <tr>
                                            <td class="summary-box-header">बाकी राशि</td>
                                        </tr>
                                        <tr>
                                            <td class="summary-box-val">
                                                ₹{{ number_format($previousDue, 0) }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <td style="padding: 0 6px; font-size: 16px; font-weight: bold; color: #1e3a8a; vertical-align: middle; text-align: center;">
                                    =
                                </td>

                                <!-- Box 3: Total -->
                                <td style="vertical-align: middle;">
                                    <table class="summary-box summary-box-total" style="min-width: 105px;">
                                        <tr>
                                            <td class="summary-box-header">कुल योग (Total)</td>
                                        </tr>
                                        <tr>
                                            <td class="summary-box-val">
                                                ₹{{ number_format($totalDue, 0) }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 28%; text-align: right; vertical-align: bottom;">
                        <div style="display: inline-block; text-align: center; width: 120px;">
                            <div style="margin-bottom: 20px;"></div>
                            <div style="border-top: 1.5px dotted #334155; padding-top: 2px; font-size: 9.5px; font-weight: bold; color: #1e3a8a;">
                                हस्ताक्षर
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
