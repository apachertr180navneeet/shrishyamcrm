<!DOCTYPE html>
<html lang="hi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>प्रमाण पत्र - {{ $member->membership_no }} - {{ $member->full_name }}</title>
    <style>
        @page {
            margin: 0;
            size: 297mm 210mm landscape;
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
        html, body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
            background-color: #ffffff;
        }
        .pdf-container {
            position: relative;
            width: 297mm;
            height: 210mm;
        }
        .pdf-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            z-index: 1;
        }
        .pdf-field {
            position: absolute;
            z-index: 10;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
        }
        .pf-mem-no {
            top: 66.8mm;
            left: 46.0mm;
            max-width: 74mm;
            font-size: 16px;
            color: #1B365D;
        }
        .pf-date {
            top: 66.8mm;
            left: 248.0mm;
            max-width: 40mm;
            font-size: 14px;
        }
        .pf-name {
            top: 88.2mm;
            left: 25.2mm;
            max-width: 65mm;
            font-size: 16px;
            color: #700D18;
        }
        .pf-father {
            top: 88.2mm;
            left: {{ !empty($isSeniorScheme) ? '141.0mm' : '124.7mm' }};
            max-width: {{ !empty($isSeniorScheme) ? '74mm' : '86mm' }};
            font-size: 14px;
        }
        .pf-caste {
            top: 88.2mm;
            left: 224.2mm;
            max-width: 53mm;
            font-size: 14px;
        }
        .pf-age {
            top: 104.1mm;
            left: 25.2mm;
            max-width: 50mm;
            font-size: 14px;
        }
        .pf-nominee {
            top: 104.1mm;
            left: 81.6mm;
            max-width: 142mm;
            font-size: 14px;
            color: #1B365D;
        }
        .pf-village {
            top: 119.3mm;
            left: 25.2mm;
            max-width: 59mm;
            font-size: 14px;
        }
        .pf-mobile {
            top: 135.4mm;
            left: 34.1mm;
            max-width: 112mm;
            font-size: 14px;
        }
        .pf-agent {
            top: 135.4mm;
            left: 173.7mm;
            max-width: 104mm;
            font-size: 14px;
        }
        .pf-kisht {
            top: 164.8mm;
            left: 52.0mm;
            max-width: 41mm;
            font-size: 15px;
        }
        .pf-scheme-title {
            top: 49.0mm;
            left: 90.0mm;
            right: 90.0mm;
            text-align: center;
            background-color: #ffffff;
            color: #243382;
            font-size: 22px;
            padding: 1mm 4mm;
        }
        .pdf-photo-box {
            position: absolute;
            top: 104.8mm;
            left: 231.6mm;
            width: 38.9mm;
            height: 54.1mm;
            z-index: 10;
            overflow: hidden;
            text-align: center;
        }
        .pdf-photo-box img {
            width: 38.9mm;
            height: 54.1mm;
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        <!-- 100% Authentic Master Background Template Image -->
        <img src="{{ $bgImagePath }}" alt="Certificate Background" class="pdf-bg">

        <!-- Dynamic Member Overlays -->
        <div class="pdf-field pf-mem-no">{{ $member->membership_no }}</div>
        <div class="pdf-field pf-date">{{ $member->joining_date ? $member->joining_date->format('d/m/Y') : date('d/m/Y') }}</div>
        <div class="pdf-field pf-name">{{ $member->full_name }}</div>
        <div class="pdf-field pf-father">{{ $member->father_spouse_name ?: '-' }}</div>
        <div class="pdf-field pf-caste">{{ $member->caste ?: ($member->gotra ?: '-') }}</div>
        <div class="pdf-field pf-age">{{ $member->age }} वर्ष</div>
        <div class="pdf-field pf-nominee">{{ $nomineeName }}</div>
        <div class="pdf-field pf-village">{{ $member->address ?: '-' }}</div>
        <div class="pdf-field pf-mobile">{{ $member->mobile ?: '-' }}</div>
        <div class="pdf-field pf-agent">{{ $member->agent ? $member->agent->name . ($member->agent->mobile ? ' (' . $member->agent->mobile . ')' : '') : 'HQ Direct' }}</div>
        <div class="pdf-field pf-kisht">{{ number_format($kishtRate, 0) }}/-</div>

        @if(empty($isSeniorScheme) && $schemeHeading !== 'विवाह योजना प्रमाण पत्र')
            <div class="pdf-field pf-scheme-title">{{ $schemeHeading }}</div>
        @endif

        <!-- Member Photo -->
        <div class="pdf-photo-box">
            @if($photoPath && (str_starts_with($photoPath, 'http') || str_starts_with($photoPath, 'data:image/') || file_exists($photoPath)))
                <img src="{{ $photoPath }}" alt="Photo">
            @endif
        </div>
    </div>
</body>
</html>
