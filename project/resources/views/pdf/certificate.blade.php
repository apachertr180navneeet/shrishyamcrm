<!DOCTYPE html>
<html lang="hi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Certificate - {{ $member->membership_no }}</title>
    <style>
        @page { margin: 20px; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }
        .cert-border-outer {
            border: 5px double #D97706;
            padding: 20px;
            background: #fffdfa;
            border-radius: 8px;
            position: relative;
        }
        .cert-border-inner {
            border: 1px solid #1B365D;
            padding: 25px;
            border-radius: 4px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .main-title {
            font-size: 24px;
            font-weight: bold;
            color: #1B365D;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .hindi-title {
            font-size: 18px;
            font-weight: bold;
            color: #D97706;
            margin: 5px 0;
        }
        .meta-info {
            font-size: 11px;
            color: #666;
        }
        .cert-heading {
            font-size: 16px;
            font-weight: bold;
            color: #1B365D;
            background: #fef3c7;
            display: inline-block;
            padding: 6px 25px;
            border-radius: 20px;
            border: 1px solid #D97706;
            margin-top: 10px;
        }
        .content {
            font-size: 14px;
            line-height: 1.8;
            margin: 25px 0;
            text-align: justify;
        }
        .highlight {
            font-weight: bold;
            color: #1B365D;
            text-decoration: underline;
        }
        .details-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .details-grid td {
            padding: 6px 10px;
            font-size: 13px;
        }
        .footer-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .sig-box {
            text-align: center;
            font-size: 12px;
            color: #333;
            width: 30%;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="cert-border-outer">
        <div class="cert-border-inner">
            <div class="header">
                <h1 class="main-title">{{ $society['name'] }}</h1>
                <h2 class="hindi-title">{{ $society['name_hindi'] }}</h2>
                <div class="meta-info">
                    Reg No: <strong>{{ $society['reg_no'] }}</strong> | {{ $society['address'] }}
                </div>
                <div class="cert-heading">MEMBERSHIP CERTIFICATE / सदस्यता प्रमाण पत्र</div>
            </div>

            <div class="content">
                यह प्रमाणित किया जाता है कि <span class="highlight">{{ $member->full_name }}</span> 
                सुपुत्र/पत्नी <span class="highlight">{{ $member->father_spouse_name ?: 'श्री ' . $member->full_name }}</span>, 
                निवासी <span class="highlight">{{ $member->address ?: 'लोहीकी' }}, {{ $member->district }} ({{ $member->state }})</span>, 
                श्री श्याम वेलफेयर सोसायटी की 
                <span class="highlight">{{ $member->scheme ? $member->scheme->name_hindi : 'कल्याण योजना' }}</span> 
                के अधिकृत आजीवन सदस्य के रूप में पंजीकृत हैं।
            </div>

            <table class="details-grid">
                <tr>
                    <td style="width: 25%; font-weight: bold;">Membership No (सदस्यता नं):</td>
                    <td style="width: 25%; color: #D97706; font-weight: bold;">{{ $member->membership_no }}</td>
                    <td style="width: 25%; font-weight: bold;">Joining Date (पंजीकरण तिथि):</td>
                    <td style="width: 25%;">{{ $member->joining_date ? $member->joining_date->format('d-M-Y') : date('d-M-Y') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Scheme (योजना):</td>
                    <td>{{ $member->scheme ? $member->scheme->name_hindi : '-' }}</td>
                    <td style="font-weight: bold;">Age / Gender (आयु/लिंग):</td>
                    <td>{{ $member->age }} वर्ष / {{ $member->gender }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Primary Nominee (वारिसदार):</td>
                    <td>{{ $member->nominees->first() ? $member->nominees->first()->name . ' (' . $member->nominees->first()->relation . ')' : '-' }}</td>
                    <td style="font-weight: bold;">Assigned Agent (प्रतिनिधि):</td>
                    <td>{{ $member->agent ? $member->agent->name : 'HQ Direct' }}</td>
                </tr>
            </table>

            <table class="footer-table">
                <tr>
                    <td class="sig-box">
                        General Secretary<br>(महासचिव)
                    </td>
                    <td style="width: 40%; text-align: center; font-size: 11px; color: #666;">
                        Date of Issue: {{ date('d-m-Y') }}<br>
                        <em>लोहीकी, हरियाणा</em>
                    </td>
                    <td class="sig-box">
                        President<br>(अध्यक्ष)
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
