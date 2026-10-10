<!DOCTYPE html>
<html lang="hi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>प्रमाण पत्र - {{ $member->membership_no }} - {{ $member->full_name }}</title>
    <style>
        @page {
            margin: 8px;
            size: A4 landscape;
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
            margin: 0;
            padding: 0;
            font-family: 'Mangal', 'Aparajita', 'DejaVu Sans', sans-serif;
        }
        body {
            background-color: #ffffff;
            color: #1a1a1a;
            padding: 2px;
        }
        .cert-card {
            width: 100%;
            height: 98.5%;
            border: 8px solid #700D18;
            position: relative;
            background: #ffffff;
            padding: 10px 18px 8px 18px;
            overflow: hidden;
        }

        /* Top-Left Golden Arc Swoosh */
        .corner-tl-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 120px;
            height: 95px;
            background: #EAA023;
            border-bottom-right-radius: 110px 85px;
            z-index: 1;
        }
        .corner-tl-logo {
            position: absolute;
            top: 6px;
            left: 10px;
            width: 70px;
            height: 70px;
            z-index: 3;
        }
        .corner-tl-logo img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
        }

        /* Top-Right Golden Swoosh */
        .corner-tr-bg {
            position: absolute;
            top: 0;
            right: 0;
            width: 140px;
            height: 50px;
            background: #EAA023;
            border-bottom-left-radius: 120px 45px;
            z-index: 1;
        }

        /* Bottom-Right Golden Swoosh */
        .corner-br-bg {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 120px;
            height: 85px;
            background: #EAA023;
            border-top-left-radius: 110px 80px;
            z-index: 1;
        }

        /* Center Watermark */
        .watermark-container {
            position: absolute;
            top: 50%;
            left: 45%;
            transform: translate(-50%, -50%);
            width: 270px;
            height: 270px;
            opacity: 0.12;
            z-index: 1;
            text-align: center;
        }
        .watermark-container img {
            width: 270px;
            height: 270px;
        }

        /* Content Container */
        .cert-inner {
            position: relative;
            z-index: 2;
        }

        /* Top Bar Table */
        .top-bar-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .top-pill-box {
            background: #EEF5FC;
            border: 1px solid #D1E3F6;
            border-radius: 18px;
            padding: 3px 14px;
            display: inline-block;
        }
        .top-pill-reg {
            color: #1A365D;
            font-weight: 700;
            font-size: 12px;
        }
        .top-pill-ganesh {
            color: #C02626;
            font-weight: 700;
            font-size: 12px;
            margin-left: 14px;
        }
        .top-virtues {
            color: #700D18;
            font-weight: 700;
            font-size: 12.5px;
            text-align: right;
            padding-right: 15px;
        }
        .top-san {
            color: #1A365D;
            font-weight: 700;
            font-size: 12.5px;
            text-align: right;
            padding-right: 15px;
            margin-top: 2px;
        }

        /* Main Society Heading */
        .society-heading {
            text-align: center;
            color: #700D18;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin: 1px 0 2px 0;
            line-height: 1.1;
        }

        /* Scheme Certificate Heading */
        .scheme-heading {
            text-align: center;
            color: #243382;
            font-size: 24px;
            font-weight: 700;
            margin: 2px 0 8px 0;
            line-height: 1.1;
        }

        /* Form Grid Layout */
        .form-section {
            width: 100%;
            margin-top: 2px;
        }
        .form-table {
            width: 100%;
            border-collapse: collapse;
        }
        .form-table td {
            padding: 3.5px 0;
            vertical-align: middle;
            font-size: 15px;
        }
        .lbl {
            font-weight: 700;
            color: #700D18;
            white-space: nowrap;
        }
        .val {
            font-weight: 700;
            color: #000000;
            border-bottom: 1.5px dotted #B91C1C;
            padding: 0 4px;
            display: inline-block;
        }
        .dots-filler {
            border-bottom: 1.5px dotted #B91C1C;
            display: inline-block;
            height: 12px;
        }

        /* Photo Box */
        .photo-box {
            width: 105px;
            height: 135px;
            border: 1.5px solid #4B5563;
            background: #ffffff;
            text-align: center;
            vertical-align: middle;
            margin: 0 auto;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-placeholder {
            padding-top: 45px;
            color: #9CA3AF;
            font-size: 11px;
            font-weight: 700;
        }

        /* Signature */
        .signature-area {
            text-align: center;
            margin-top: 4px;
        }
        .signature-img-box {
            height: 38px;
            text-align: center;
        }
        .signature-img-box img {
            height: 36px;
        }
        .founder-sign-lbl {
            color: #1A365D;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            margin-top: 1px;
        }

        /* Thank you & Support rate */
        .thank-you-line {
            color: #700D18;
            font-size: 15px;
            font-weight: 700;
            margin-top: 6px;
            margin-bottom: 3px;
        }
        .rate-line {
            color: #700D18;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .rate-amount {
            color: #000000;
            font-weight: 700;
            border-bottom: 1.5px dotted #B91C1C;
            padding: 0 6px;
        }

        /* Bottom Section */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .footer-black-box {
            background: #000000;
            color: #ffffff;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 11px;
            line-height: 1.35;
        }
        .footer-black-box .wish-line {
            font-weight: 700;
            font-size: 11.5px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.7);
            padding-bottom: 2px;
            margin-bottom: 3px;
        }
        .footer-black-box .office-line {
            font-size: 11px;
            font-weight: 600;
        }
        .footer-phone-line {
            font-size: 12.5px;
            font-weight: 700;
            color: #000000;
            margin-top: 4px;
        }

        /* Bottom Yellow Policy Ribbon */
        .policy-strip {
            background: #EAA023;
            color: #000000;
            font-size: 11.5px;
            font-weight: 700;
            text-align: center;
            padding: 4px 10px;
            border-radius: 12px;
            display: inline-block;
            width: 95%;
        }
    </style>
</head>
<body>
    <div class="cert-card">
        <!-- Top Left Golden Corner Arc & Society Logo -->
        <div class="corner-tl-bg"></div>
        <div class="corner-tl-logo">
            @if($logoPath && file_exists($logoPath))
                <img src="{{ $logoPath }}" alt="Logo">
            @else
                <img src="{{ public_path('assets/society_logo.png') }}" alt="Logo">
            @endif
        </div>

        <!-- Top Right Golden Corner Arc -->
        <div class="corner-tr-bg"></div>

        <!-- Bottom Right Corner Golden Accent -->
        <div class="corner-br-bg"></div>

        <!-- Center Watermark -->
        <div class="watermark-container">
            @if($logoPath && file_exists($logoPath))
                <img src="{{ $logoPath }}" alt="Watermark">
            @else
                <img src="{{ public_path('assets/society_logo.png') }}" alt="Watermark">
            @endif
        </div>

        <div class="cert-inner">
            <!-- 1. Top Meta Info Bar -->
            <table class="top-bar-table">
                <tr>
                    <td style="width: 58%; padding-left: 80px; vertical-align: middle;">
                        <div class="top-pill-box">
                            <span class="top-pill-reg">Reg. No. {{ $society['reg_no'] }}</span>
                            <span class="top-pill-ganesh">!! श्री गणेशाय नम:</span>
                        </div>
                    </td>
                    <td style="width: 42%; vertical-align: top;">
                        <div class="top-virtues">
                            सहयोग &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; सेवा &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; विश्वास
                        </div>
                        <div class="top-san">
                            SAN : {{ $society['san_code'] }}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- 2. Society Main Heading -->
            <h1 class="society-heading">{{ $society['name_hindi'] }}</h1>

            <!-- 3. Scheme Certificate Heading (विवाह योजना प्रमाण पत्र / कन्यादान विवाह योजना प्रमाण पत्र) -->
            <h2 class="scheme-heading">{{ $schemeHeading }}</h2>

            <!-- 4. Member Form Data Grid -->
            <div class="form-section">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <!-- Left Data Columns -->
                        <td style="width: 80%; vertical-align: top;">
                            <table class="form-table">
                                <!-- Row 1: Member No & Date -->
                                <tr>
                                    <td style="width: 14%;"><span class="lbl">सदस्य क्रमांक :</span></td>
                                    <td style="width: 40%;"><span class="val" style="color: #000000; font-size: 15px; min-width: 180px;">{{ $member->membership_no }}</span></td>
                                    <td style="width: 8%; text-align: right;"><span class="lbl">दिनांक :</span></td>
                                    <td style="width: 38%;"><span class="val" style="min-width: 140px;">{{ $member->joining_date ? $member->joining_date->format('d/m/Y') : date('d/m/Y') }}</span></td>
                                </tr>

                                <!-- Row 2: Name, Father's Name, Caste -->
                                <tr>
                                    <td><span class="lbl">नाम</span></td>
                                    <td><span class="val" style="font-size: 15px; min-width: 180px;">{{ $member->full_name }}</span></td>
                                    <td colspan="2">
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="width: 60%;">
                                                    <span class="lbl">{{ $fatherSpouseLabel ?? 'पिता/पति का नाम' }}</span>
                                                    <span class="val" style="min-width: 150px;">{{ $member->father_spouse_name ?: '-' }}</span>
                                                </td>
                                                <td style="width: 40%; text-align: right;">
                                                    <span class="lbl">जाति</span>
                                                    <span class="val" style="min-width: 90px;">{{ $member->caste ?: ($member->gotra ?: '-') }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Row 3: Age & Nominee -->
                                <tr>
                                    <td><span class="lbl">उम्र</span></td>
                                    <td><span class="val" style="min-width: 180px;">{{ $member->age }} वर्ष</span></td>
                                    <td colspan="2">
                                        <span class="lbl">वारिसदार</span>
                                        <span class="val" style="min-width: 280px;">{{ $nomineeName }}</span>
                                    </td>
                                </tr>

                                <!-- Row 4: Village, District, State -->
                                <tr>
                                    <td><span class="lbl">गांव</span></td>
                                    <td><span class="val" style="min-width: 180px;">{{ $member->address ?: '-' }}</span></td>
                                    <td colspan="2">
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="width: 50%;">
                                                    <span class="lbl">जिला</span>
                                                    <span class="val" style="min-width: 110px;">{{ $member->district ?: 'बालोतरा' }}</span>
                                                </td>
                                                <td style="width: 50%; text-align: right;">
                                                    <span class="lbl">राज्य</span>
                                                    <span class="val" style="min-width: 110px;">{{ $member->state ?: 'राजस्थान' }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Row 5: Mobile & Agent -->
                                <tr>
                                    <td><span class="lbl">फोन नं.</span></td>
                                    <td><span class="val" style="min-width: 180px;">{{ $member->mobile ?: '-' }}</span></td>
                                    <td colspan="2">
                                        <span class="lbl">कार्यकर्ता</span>
                                        <span class="val" style="min-width: 280px;">{{ $member->agent ? $member->agent->name . ($member->agent->mobile ? ' (' . $member->agent->mobile . ')' : '') : 'HQ Direct' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Right Column: Member Photo & Founder Signature -->
                        <td style="width: 20%; vertical-align: top; text-align: center;">
                            <div class="photo-box">
                                @if($photoPath && (str_starts_with($photoPath, 'http') || str_starts_with($photoPath, 'data:image/') || file_exists($photoPath)))
                                    <img src="{{ $photoPath }}" alt="Member Photo">
                                @else
                                    <div class="photo-placeholder">
                                        फोटो<br>(PHOTO)
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- 5. Gratitude & Support Rate Notes -->
            <div class="thank-you-line">संस्था से जुड़ने पर धन्यवाद।</div>
            <div class="rate-line">
                सहयोग राशि : रु <span class="rate-amount">{{ number_format($kishtRate, 0) }}/-</span> रूपये प्रत्येक कार्यक्रम पर लागू।
            </div>

            <!-- 6. Bottom Notice Box, Founder Signature, and Yellow Policy Strip -->
            <table class="footer-table">
                <tr>
                    <!-- Left: Black Box & Phone Number -->
                    <td style="width: 65%; vertical-align: bottom;">
                        <div class="footer-black-box">
                            <div class="wish-line">संस्था आपके स्वास्थ्य, उज्ज्वल व गौरवमयी भविष्य की मंगल कामना करती है।</div>
                            <div class="office-line">कार्यालय :- {{ $society['address'] }}</div>
                        </div>
                        <div class="footer-phone-line">
                            मो. {{ $society['phone'] }}
                        </div>
                    </td>

                    <!-- Right: Founder Signature & Policy Ribbon -->
                    <td style="width: 35%; text-align: center; vertical-align: bottom;">
                        <div class="signature-area">
                            <div class="signature-img-box">
                                @if($signaturePath && file_exists($signaturePath))
                                    <img src="{{ $signaturePath }}" alt="लादुराम">
                                @else
                                    <img src="{{ public_path('assets/signature_laduram.svg') }}" alt="लादुराम">
                                @endif
                            </div>
                            <div class="founder-sign-lbl">हस्ताक्षर संस्थापक</div>
                        </div>
                        <div style="margin-top: 3px;">
                            <div class="policy-strip" style="{{ !empty($isSeniorScheme) ? 'line-height: 1.25; font-size: 10px; padding: 3px 6px;' : '' }}">
                                @if(!empty($isSeniorScheme))
                                    1–6 माह तक दुर्घटना होने पर 51000रु व<br>6माह बाद सदस्यानुसार भुगतान किया जायेगा
                                @else
                                    {{ $policyNote }}
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
