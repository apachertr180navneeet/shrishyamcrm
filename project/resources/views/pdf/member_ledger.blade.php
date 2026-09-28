<!DOCTYPE html>
<html lang="hi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Member Ledger Card - {{ $member->membership_no }}</title>
    <style>
        @page {
            margin: 10px 12px;
        }
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

        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Mangal', 'Aparajita', 'DejaVu Sans', sans-serif;
            color: #0f172a;
            font-size: 10.5px;
            line-height: 1.25;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .card-outer-frame {
            border: 2px solid #1e3a8a;
            padding: 4px;
            background: #ffffff;
            border-radius: 6px;
        }
        .card-inner-frame {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            background: #ffffff;
            border-radius: 4px;
        }
        .top-meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 3px;
        }
        .top-meta-table td {
            padding: 0;
            vertical-align: middle;
        }
        .ganesh-mantra {
            color: #b91c1c;
            font-weight: bold;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 4px;
        }
        .society-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            letter-spacing: 0.5px;
            line-height: 1.15;
        }
        .society-subtitle {
            font-size: 10.5px;
            font-weight: bold;
            color: #334155;
            margin-top: 2px;
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
            margin-top: 5px;
        }
        .total-box-table {
            border: 1.8px solid #1e3a8a;
            border-radius: 5px;
            border-collapse: separate;
            border-spacing: 0;
            background: #f8fafc;
            display: inline-table;
        }
    </style>
</head>
<body>
    <div class="card-outer-frame">
        <div class="card-inner-frame">
            <!-- Top Meta Bar: Reg No | Ganesh Inscription | SAN -->
            <table class="top-meta-table">
                <tr>
                    <td class="text-start" style="width: 38%; color: #1e3a8a;">
                        <strong>Reg. No. {{ $society['reg_no'] }}</strong>
                    </td>
                    <td class="text-center ganesh-mantra" style="width: 24%;">
                        ॥ श्री गणेशाय नमः ॥
                    </td>
                    <td class="text-end" style="width: 38%; color: #1e3a8a;">
                        <strong>SAN : {{ $sanCode }}</strong>
                    </td>
                </tr>
            </table>

            <!-- Center Header with Left Logo Emblem -->
            <table class="header-table">
                <tr>
                    <td style="width: 60px; vertical-align: middle; text-align: left; padding-bottom: 4px;">
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
                    <td style="vertical-align: middle; text-align: center; padding-bottom: 4px;">
                        <div class="society-title">{{ $society['name_hindi'] }}</div>
                        <div class="society-subtitle">{{ $society['address'] }}</div>
                        <div class="society-phones">मो. {{ $society['phone'] }}</div>
                    </td>
                    <td style="width: 60px; padding-bottom: 4px;">&nbsp;</td>
                </tr>
            </table>

            <!-- Scheme Row & Date -->
            <table class="scheme-row-table">
                <tr>
                    <td style="width: 26%; font-size: 10px; vertical-align: middle;">
                        <strong style="color: #334155;">क्र.सं.</strong> <span style="font-weight: bold; color: #0f172a; border-bottom: 1px dotted #475569; padding: 0 4px;">{{ $member->membership_no }}</span>
                    </td>
                    <td class="text-center" style="width: 48%; vertical-align: middle;">
                        <div class="scheme-pill">
                            {{ $member->scheme ? $member->scheme->name_hindi : 'कन्यादान योजना' }}
                        </div>
                    </td>
                    <td class="text-end" style="width: 26%; font-size: 10px; vertical-align: middle;">
                        <strong style="color: #334155;">दिनांक :-</strong> <span style="font-weight: bold; color: #0f172a; border-bottom: 1px dotted #475569; padding: 0 4px;">{{ date('d.m.Y') }}</span>
                    </td>
                </tr>
            </table>

            <!-- Member Info Section with 4 Complete Dotted Underline Rows -->
            <table class="member-info-table">
                <!-- Row 1: Member Name & Father/Spouse Name -->
                <tr>
                    <td style="width: 54%; padding: 1.5px 5px 1.5px 0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="field-label">श्री</td>
                                <td class="field-value">
                                    {{ $member->full_name }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 46%; padding: 1.5px 0 1.5px 5px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="field-label">पिता/पत्नी/पुत्री</td>
                                <td class="field-value">
                                    {{ $member->father_spouse_name ?: '-' }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Row 2: Address & Gotra -->
                <tr>
                    <td style="width: 65%; padding: 1.5px 5px 1.5px 0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="field-label">निवासी</td>
                                <td class="field-value">
                                    {{ $member->address ?: ($member->village ? $member->village . ', ' . $member->district : $member->district) }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 35%; padding: 1.5px 0 1.5px 5px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="field-label">गौत्र</td>
                                <td class="field-value">
                                    {{ $member->gotra ?? ($member->caste ?? '-') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Row 3: Mobile Number & Kisht Amount -->
                <tr>
                    <td style="width: 60%; padding: 1.5px 5px 1.5px 0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="field-label">मोबाईल नम्बर</td>
                                <td class="field-value">
                                    {{ $member->mobile }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 40%; padding: 1.5px 0 1.5px 5px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="field-label">किस्त</td>
                                <td class="field-value">
                                    {{ number_format($kishtRate, 0) }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Row 4: Agent Name & Agent Mobile -->
                <tr>
                    <td style="width: 55%; padding: 1.5px 5px 1.5px 0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="field-label">कार्यकर्ता का नाम</td>
                                <td class="field-value">
                                    {{ $member->agent ? $member->agent->name : ($member->agent_name ?? '-') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 45%; padding: 1.5px 0 1.5px 5px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="field-label">मो.</td>
                                <td class="field-value">
                                    {{ $member->agent ? $member->agent->mobile : ($society['phone'] ? explode(',', $society['phone'])[0] : '') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Contributions Ledger 20-Row Solid Border Table -->
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th style="width: 6%;">क्र.सं.</th>
                        <th style="width: 44%;">नाम / विवरण</th>
                        <th style="width: 16%;">जुड़ने की तिथी</th>
                        <th style="width: 18%;">भुगतान तिथि</th>
                        <th style="width: 16%;">कार्यकर्ता नाम</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sr = 1; @endphp
                    @foreach($tableRows as $row)
                    <tr style="height: 18px; background: #ffffff;">
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
                    <tr style="height: 18px; background: #ffffff;">
                        <td class="text-center">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    @endfor
                </tbody>
            </table>

            <!-- Footer Summary Section: Single Total Box & Signature Line -->
            <table class="footer-summary-table">
                <tr>
                    <td style="width: 50%; vertical-align: middle;">
                        <table class="total-box-table">
                            <tr>
                                <td style="padding: 3px 6px 3px 5px; vertical-align: middle;">
                                    @php
                                        $rupeeImg = $rupeeIconPath ?? public_path('assets/rupee_icon.png');
                                    @endphp
                                    @if(file_exists($rupeeImg))
                                        <img src="{{ str_replace('\\', '/', $rupeeImg) }}" width="22" height="22" alt="₹" style="display: block; vertical-align: middle;"/>
                                    @endif
                                </td>
                                <td style="padding: 3px 14px 3px 0; vertical-align: middle; font-size: 16px; font-weight: bold; color: #1e3a8a; line-height: 1;">
                                    {{ number_format($totalPaid ?: $totalExpected, 0) }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; text-align: right; vertical-align: bottom;">
                        <div style="display: inline-block; text-align: center; width: 130px;">
                            <div style="margin-bottom: 22px;"></div>
                            <div style="border-top: 1.5px dotted #334155; padding-top: 2px; font-size: 10.5px; font-weight: bold; color: #1e3a8a;">
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
