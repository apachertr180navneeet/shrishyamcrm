<!DOCTYPE html>
<html lang="hi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>प्रमाण पत्र - {{ $member->membership_no }} - {{ $member->full_name }}</title>
    <style>
        @page {
            margin: 10px;
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
            padding: 5px;
        }
        .cert-card {
            width: 100%;
            height: 98%;
            border: 6px solid #6E0D1B;
            position: relative;
            background: #ffffff;
            padding: 14px 20px 10px 20px;
            overflow: hidden;
        }

        /* Diagonal corner banners */
        .corner-tl-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            border-top: 100px solid #EAA023;
            border-right: 140px solid transparent;
            z-index: 1;
        }
        .corner-tl-logo {
            position: absolute;
            top: 6px;
            left: 10px;
            width: 65px;
            height: 65px;
            z-index: 2;
        }
        .corner-tl-logo img {
            width: 65px;
            height: 65px;
            border-radius: 50%;
        }

        .corner-tr-bg {
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-top: 50px solid #EAA023;
            border-left: 280px solid transparent;
            z-index: 1;
        }
        .corner-tr-text {
            position: absolute;
            top: 4px;
            right: 15px;
            font-size: 13px;
            font-weight: 700;
            color: #6E0D1B;
            z-index: 2;
            letter-spacing: 1px;
        }

        .corner-br-bg {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 0;
            height: 0;
            border-bottom: 75px solid #EAA023;
            border-left: 110px solid transparent;
            z-index: 1;
        }

        /* Center Watermark */
        .watermark-container {
            position: absolute;
            top: 48%;
            left: 45%;
            transform: translate(-50%, -50%);
            width: 250px;
            height: 250px;
            opacity: 0.12;
            z-index: 1;
            text-align: center;
        }
        .watermark-container img {
            width: 250px;
            height: 250px;
        }

        /* Main Content Structure */
        .cert-inner {
            position: relative;
            z-index: 3;
        }

        /* Top Meta line */
        .top-meta-table {
            width: 100%;
            margin-bottom: 4px;
            padding-left: 70px;
            padding-right: 10px;
        }
        .top-meta-table td {
            font-size: 12px;
            font-weight: 700;
        }
        .meta-reg {
            color: #1B365D;
            text-align: left;
            width: 40%;
        }
        .meta-ganesh {
            color: #C02626;
            text-align: center;
            width: 25%;
            font-size: 13px;
        }
        .meta-san {
            color: #1B365D;
            text-align: right;
            width: 35%;
        }

        /* Society Heading */
        .society-heading {
            text-align: center;
            color: #6E0D1B;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin: 2px 0 4px 0;
            line-height: 1.1;
        }

        /* Scheme Certificate Heading */
        .scheme-heading {
            text-align: center;
            color: #1B365D;
            font-size: 24px;
            font-weight: 700;
            margin: 4px 0 12px 0;
            line-height: 1.1;
        }

        /* Form Grid Layout */
        .form-section {
            width: 100%;
            margin-top: 4px;
        }
        .form-table {
            width: 100%;
            border-collapse: collapse;
        }
        .form-table td {
            padding: 4px 0;
            vertical-align: middle;
            font-size: 15px;
        }
        .lbl {
            font-weight: 700;
            color: #6E0D1B;
            white-space: nowrap;
        }
        .val {
            font-weight: 700;
            color: #000000;
            border-bottom: 1px dotted #DC2626;
            padding: 0 4px;
            display: inline-block;
        }
        .dots {
            border-bottom: 1px dotted #DC2626;
            display: inline-block;
            height: 14px;
        }

        /* Photo Box */
        .photo-box {
            width: 105px;
            height: 130px;
            border: 1.5px solid #64748B;
            background: #FAFAFA;
            text-align: center;
            vertical-align: middle;
            position: relative;
            margin-left: 10px;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-placeholder {
            padding-top: 45px;
            color: #94A3B8;
            font-size: 11px;
            font-weight: 600;
        }

        /* Thank you & Support rate */
        .thank-you-line {
            color: #6E0D1B;
            font-size: 15px;
            font-weight: 700;
            margin-top: 8px;
            margin-bottom: 4px;
        }
        .rate-line {
            color: #6E0D1B;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .rate-amount {
            color: #000000;
            font-weight: 700;
            border-bottom: 1px dotted #DC2626;
            padding: 0 8px;
        }

        /* Bottom Footer Bar */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .footer-black-box {
            background: #000000;
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 11px;
            line-height: 1.35;
            width: 65%;
            vertical-align: middle;
        }
        .footer-black-box .wish-line {
            font-weight: 700;
            font-size: 11.5px;
        }
        .footer-sign-area {
            width: 35%;
            text-align: center;
            vertical-align: bottom;
            padding-left: 10px;
        }
        .signature-img {
            font-family: 'Brush Script MT', 'Segoe Script', cursive, sans-serif;
            color: #DC2626;
            font-size: 20px;
            font-weight: bold;
            font-style: italic;
            display: block;
            margin-bottom: 2px;
        }
        .founder-sign-lbl {
            color: #1B365D;
            font-size: 13px;
            font-weight: 700;
        }

        /* Bottom Yellow Policy Strip */
        .policy-strip {
            background: #EAA023;
            color: #000000;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            padding: 3px 6px;
            border-radius: 2px;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="cert-card">
        <!-- Top Left Golden Corner Banner & Logo -->
        <div class="corner-tl-bg"></div>
        <div class="corner-tl-logo">
            @if($logoPath && file_exists($logoPath))
                <img src="{{ $logoPath }}" alt="Logo">
            @else
                <img src="{{ public_path('assets/society_logo.jpg') }}" alt="Logo">
            @endif
        </div>

        <!-- Top Right Golden Corner Triangle & Virtues -->
        <div class="corner-tr-bg"></div>
        <div class="corner-tr-text">
            सहयोग &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; सेवा &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; विश्वास
        </div>

        <!-- Bottom Right Corner Accent -->
        <div class="corner-br-bg"></div>

        <!-- Center Watermark -->
        <div class="watermark-container">
            @if($logoPath && file_exists($logoPath))
                <img src="{{ $logoPath }}" alt="Watermark">
            @else
                <img src="{{ public_path('assets/society_logo.jpg') }}" alt="Watermark">
            @endif
        </div>

        <div class="cert-inner">
            <!-- 1. Top Meta Info Bar -->
            <table class="top-meta-table">
                <tr>
                    <td class="meta-reg">Reg. No. {{ $society['reg_no'] }}</td>
                    <td class="meta-ganesh">!! श्री गणेशाय नमः !!</td>
                    <td class="meta-san">SAN : {{ $society['san_code'] }}</td>
                </tr>
            </table>

            <!-- 2. Society Main Heading -->
            <h1 class="society-heading">{{ $society['name_hindi'] }}</h1>

            <!-- 3. Scheme Certificate Heading -->
            <h2 class="scheme-heading">{{ $member->scheme ? ($member->scheme->name_hindi ?: $member->scheme->name) : 'बुजुर्ग सम्मान' }} योजना प्रमाण पत्र</h2>

            <!-- 4. Member Form Data Grid -->
            <div class="form-section">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <!-- Left Data Columns -->
                        <td style="width: 82%; vertical-align: top;">
                            <table class="form-table">
                                <!-- Row 1: Member No & Date -->
                                <tr>
                                    <td style="width: 15%;"><span class="lbl">सदस्य क्रमांक :</span></td>
                                    <td style="width: 38%;"><span class="val" style="color: #1B365D; font-size: 16px;">{{ $member->membership_no }}</span></td>
                                    <td style="width: 10%; text-align: right;"><span class="lbl">दिनांक :</span></td>
                                    <td style="width: 37%;"><span class="val">{{ $member->joining_date ? $member->joining_date->format('d/m/Y') : date('d/m/Y') }}</span></td>
                                </tr>

                                <!-- Row 2: Name, Father/Spouse, Caste -->
                                <tr>
                                    <td><span class="lbl">नाम :</span></td>
                                    <td><span class="val" style="color: #6E0D1B; font-size: 16px;">{{ $member->full_name }}</span></td>
                                    <td colspan="2">
                                        <table style="width: 100%;">
                                            <tr>
                                                <td style="width: 40%;"><span class="lbl">पिता/पति का नाम :</span> <span class="val">{{ $member->father_spouse_name ?: '-' }}</span></td>
                                                <td style="width: 25%; text-align: right;"><span class="lbl">जाति :</span> <span class="val">{{ $member->caste ?: ($member->gotra ?: '-') }}</span></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Row 3: Age & Nominee -->
                                <tr>
                                    <td><span class="lbl">उम्र :</span></td>
                                    <td><span class="val">{{ $member->age }} वर्ष</span></td>
                                    <td colspan="2">
                                        <span class="lbl">वारिसदार :</span> <span class="val" style="color: #1B365D;">{{ $nomineeName }}</span>
                                    </td>
                                </tr>

                                <!-- Row 4: Village, District, State -->
                                <tr>
                                    <td><span class="lbl">गांव :</span></td>
                                    <td><span class="val">{{ $member->address ?: '-' }}</span></td>
                                    <td colspan="2">
                                        <table style="width: 100%;">
                                            <tr>
                                                <td style="width: 45%;"><span class="lbl">जिला :</span> <span class="val">{{ $member->district ?: 'बालोतरा' }}</span></td>
                                                <td style="width: 55%;"><span class="lbl">राज्य :</span> <span class="val">{{ $member->state ?: 'राजस्थान' }}</span></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Row 5: Mobile & Agent -->
                                <tr>
                                    <td><span class="lbl">फोन नं. :</span></td>
                                    <td><span class="val">{{ $member->mobile ?: '-' }}</span></td>
                                    <td colspan="2">
                                        <span class="lbl">कार्यकर्ता :</span> <span class="val">{{ $member->agent ? $member->agent->name . ($member->agent->mobile ? ' (' . $member->agent->mobile . ')' : '') : 'HQ Direct' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Right Photo Box -->
                        <td style="width: 18%; vertical-align: middle; text-align: center;">
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
                सहयोग राशि : रु <span class="rate-amount">{{ number_format($kishtRate, 0) }}/-</span> रुपये प्रत्येक कार्यक्रम पर लागू।
            </div>

            <!-- 6. Bottom Notice Box & Founder Signature -->
            <table class="footer-table">
                <tr>
                    <td class="footer-black-box">
                        <div class="wish-line">संस्था आपके स्वास्थ्य, उज्ज्वल व गौरवमयी भविष्य की मंगल कामना करती है।</div>
                        <div>कार्यालय :- {{ $society['address'] }}</div>
                        <div>मो. {{ $society['phone'] }}</div>
                    </td>
                    <td class="footer-sign-area">
                        <div class="signature-img">Ladu Ram</div>
                        <div class="founder-sign-lbl">हस्ताक्षर संस्थापक</div>
                        <div class="policy-strip">
                            1-6 माह तक दुर्घटना होने पर 51000रु व 6 माह बाद नियमानुसार भुगतान किया जायेगा
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
