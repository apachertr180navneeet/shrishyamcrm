<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>अंशदान मास्टर लिस्ट - {{ $event->event_code }} - {{ $event->title }}</title>
    <!-- Google Fonts Hind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Hind', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        body {
            background-color: #f4f6f9;
            color: #1a202c;
            padding: 20px;
            font-size: 13px;
        }
        .print-page {
            max-width: 1080px;
            margin: 0 auto;
            background: #ffffff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        .no-print-bar {
            max-width: 1080px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-primary { background: #1B365D; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn:hover { opacity: 0.9; }

        .society-header {
            text-align: center;
            border-bottom: 2px solid #1B365D;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .society-name {
            font-size: 24px;
            font-weight: 700;
            color: #1B365D;
            letter-spacing: 0.5px;
        }
        .society-sub {
            font-size: 12px;
            color: #4a5568;
            margin-top: 2px;
        }
        .report-title-badge {
            display: inline-block;
            background: #1B365D;
            color: #fff;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            margin-top: 6px;
        }

        .event-info-box {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 16px;
        }
        .info-group small {
            display: block;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
        }
        .info-group strong {
            color: #0f172a;
            font-size: 13px;
        }

        .summary-kpi-box {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
            text-align: center;
        }
        .kpi-card.kpi-collected {
            border-color: #16a34a;
            background: #f0fdf4;
        }
        .kpi-card.kpi-pending {
            border-color: #dc2626;
            background: #fef2f2;
        }
        .kpi-card small {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
        }
        .kpi-card .amount {
            font-size: 18px;
            font-weight: 700;
            margin-top: 2px;
        }

        table.master-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 20px;
        }
        table.master-table th, table.master-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        table.master-table th {
            background: #1B365D;
            color: #ffffff;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }
        table.master-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .badge-paid {
            color: #166534;
            font-weight: 700;
            background: #dcfce7;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            display: inline-block;
        }
        .badge-pending {
            color: #991b1b;
            font-weight: 700;
            background: #fee2e2;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            display: inline-block;
        }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }

        .sign-area {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            padding: 0 30px;
        }
        .sign-block {
            text-align: center;
            border-top: 1px dashed #64748b;
            width: 180px;
            padding-top: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .print-page {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            table.master-table th {
                background: #1B365D !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .kpi-card.kpi-collected, .kpi-card.kpi-pending, tr:nth-child(even) {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <a href="{{ route('admin.events.contributions', $event->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> वापस जाएं (Back)
        </a>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> प्रिंट निकालें (Print Master List)
            </button>
        </div>
    </div>

    <div class="print-page">
        <!-- Header -->
        <div class="society-header">
            <h1 class="society-name">श्री श्याम वेलफेयर सोसायटी, लोहीकी</h1>
            <p class="society-sub">पंजीकरण संख्या: 00812 | प्रधान कार्यालय: लोहीकी (महेन्द्रगढ़, हरियाणा)</p>
            <div class="report-title-badge">
                कार्यक्रम अंशदान मास्टर लिस्ट (Event Collection Master Sheet)
            </div>
        </div>

        <!-- Event Information Box -->
        <div class="event-info-box">
            <div class="info-group">
                <small>कार्यक्रम कोड (Code)</small>
                <strong>{{ $event->event_code }}</strong>
            </div>
            <div class="info-group">
                <small>कन्या / लाभार्थी (Beneficiary)</small>
                <strong>{{ $event->girl_name }} {{ $event->father_name ? '(पि: ' . $event->father_name . ')' : '' }}</strong>
            </div>
            <div class="info-group">
                <small>कार्यक्रम दिनांक (Date)</small>
                <strong>{{ $event->event_date ? $event->event_date->format('d/m/Y') : 'N/A' }}</strong>
            </div>
            <div class="info-group">
                <small>स्थल (Venue)</small>
                <strong>{{ $event->venue }}</strong>
            </div>
            <div class="info-group">
                <small>कार्यक्रम शीर्षक (Title)</small>
                <strong>{{ $event->title }}</strong>
            </div>
            <div class="info-group">
                <small>योजना (Scheme)</small>
                <strong>{{ $event->scheme ? $event->scheme->name_hindi : 'समस्त योजनाएं' }}</strong>
            </div>
            <div class="info-group">
                <small>सहायता राशि (Grant)</small>
                <strong style="color: #16a34a;">₹{{ number_format($event->target_amount, 2) }}</strong>
            </div>
            <div class="info-group">
                <small>रिपोर्ट दिनांक (Report Date)</small>
                <strong>{{ date('d/m/Y H:i') }}</strong>
            </div>
        </div>

        <!-- Financial Summary KPIs -->
        <div class="summary-kpi-box">
            <div class="kpi-card">
                <small>कुल अपेक्षित सदस्य (Total Members)</small>
                <div class="amount" style="color: #1B365D;">{{ $stats['total_members'] }}</div>
            </div>
            <div class="kpi-card">
                <small>कुल अपेक्षित राशि (Expected Total)</small>
                <div class="amount" style="color: #0f172a;">₹{{ number_format($stats['total_expected'], 2) }}</div>
            </div>
            <div class="kpi-card kpi-collected">
                <small style="color: #166534;">प्राप्त कलेक्शन (Collected)</small>
                <div class="amount" style="color: #166534;">₹{{ number_format($stats['total_collected'], 2) }}</div>
                <small style="color: #166534;">{{ $stats['paid_count'] }} सदस्य ({{ $stats['collection_percentage'] }}%)</small>
            </div>
            <div class="kpi-card kpi-pending">
                <small style="color: #991b1b;">शेष बकाया (Pending Due)</small>
                <div class="amount" style="color: #991b1b;">₹{{ number_format($stats['total_pending'], 2) }}</div>
                <small style="color: #991b1b;">{{ $stats['pending_count'] }} सदस्य ({{ 100 - $stats['collection_percentage'] }}%)</small>
            </div>
        </div>

        <!-- Master List Table -->
        <table class="master-table">
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">#</th>
                    <th style="width: 90px;">सदस्यता क्र.</th>
                    <th>सदस्य का नाम</th>
                    <th>पिता/पति</th>
                    <th>मोबाइल</th>
                    <th>आयु वर्ग</th>
                    <th>एजेंट</th>
                    <th class="text-right" style="width: 75px;">अंशदान ₹</th>
                    <th class="text-center" style="width: 75px;">स्थिति</th>
                    <th class="text-right" style="width: 75px;">प्राप्त ₹</th>
                    <th style="width: 85px;">रसीद क्र.</th>
                    <th style="width: 75px;">दिनांक</th>
                    <th style="width: 90px;" class="text-center">हस्ताक्षर</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contributions as $idx => $c)
                @php
                    $isPaid = $c->payment_status === 'Paid';
                    $mem = $c->member;
                    $agent = $c->agent ?? ($mem ? $mem->agent : null);
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-weight: 600;">{{ $mem ? $mem->membership_no : 'N/A' }}</td>
                    <td><strong>{{ $c->member_name }}</strong></td>
                    <td>{{ $mem ? $mem->father_spouse_name : '-' }}</td>
                    <td>{{ $mem ? $mem->mobile : '-' }}</td>
                    <td>{{ $c->age_slab ?: ($c->member_age . ' वर्ष') }}</td>
                    <td>{{ $agent ? $agent->name : 'HQ Direct' }}</td>
                    <td class="text-right font-monospace">₹{{ number_format($c->contribution_amount, 2) }}</td>
                    <td class="text-center">
                        @if($isPaid)
                            <span class="badge-paid">✓ Paid</span>
                        @else
                            <span class="badge-pending">⏳ Due</span>
                        @endif
                    </td>
                    <td class="text-right font-monospace">
                        @if($isPaid)
                            <strong style="color: #166534;">₹{{ number_format($c->contribution_amount, 2) }}</strong>
                        @else
                            <span style="color: #94a3b8;">₹0.00</span>
                        @endif
                    </td>
                    <td style="font-family: monospace; font-size: 11px;">{{ $c->receipt_no ?: '-' }}</td>
                    <td>{{ $c->payment_date ? $c->payment_date->format('d/m/Y') : '-' }}</td>
                    <td></td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="text-center" style="padding: 20px;">कोई रिकॉर्ड नहीं मिला</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: 700;">
                    <td colspan="7" class="text-right">कुल योग (Grand Total):</td>
                    <td class="text-right">₹{{ number_format($stats['total_expected'], 2) }}</td>
                    <td class="text-center">{{ $stats['paid_count'] }} Paid</td>
                    <td class="text-right" style="color: #166534;">₹{{ number_format($stats['total_collected'], 2) }}</td>
                    <td colspan="3">बकाया: ₹{{ number_format($stats['total_pending'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures -->
        <div class="sign-area">
            <div class="sign-block">तैयारकर्ता (Operator)</div>
            <div class="sign-block">कोषाध्यक्ष (Treasurer)</div>
            <div class="sign-block">सचिव / प्रधान (President)</div>
        </div>
    </div>
</body>
</html>
