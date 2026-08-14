@extends('admin.layouts.app')

@section('style')
<style>
    .kpi-card-custom {
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(15, 23, 42, 0.08);
    }
    .kpi-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .kpi-icon-blue { background: #EFF6FF; color: #2563EB; }
    .kpi-icon-green { background: #DCFCE7; color: #16A34A; }
    .kpi-icon-amber { background: #FEF3C7; color: #D97706; }
    .kpi-icon-purple { background: #F3E8FF; color: #9333EA; }
    .kpi-icon-orange { background: #FFEDD5; color: #EA580C; }

    .chart-container-card {
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04);
    }
    .btn-quick-action {
        font-weight: 500;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .badge-subtext {
        font-size: 0.78rem;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Page Title Header & Quick Actions -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h3 class="fw-bold text-heading mb-0" style="font-family: 'Hind', sans-serif;">प्रशासनिक डैशबोर्ड</h3>
                        <span class="badge bg-label-primary px-3 py-1 fs-6">Shri Shyam Welfare Society ERP</span>
                    </div>
                    <p class="text-muted mb-0">Real-time administration, collection statistics, event billing and payout pool summary</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-primary btn-quick-action">
                        <i class="fas fa-calculator"></i> Event Billing
                    </button>
                    <button type="button" class="btn btn-warning btn-quick-action text-dark">
                        <i class="fas fa-cash-register"></i> Partial Payment
                    </button>
                    <button type="button" class="btn btn-dark btn-quick-action">
                        <i class="fas fa-hand-holding-usd text-warning"></i> Disburse Payout
                    </button>
                    <button type="button" class="btn btn-success btn-quick-action">
                        <i class="fab fa-whatsapp"></i> WhatsApp Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 8 KPI Metric Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Members -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-12">
            <div class="card kpi-card-custom p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Total Members</span>
                        <h3 class="fw-bold text-heading my-1">52</h3>
                        <span class="text-success badge-subtext"><i class="fas fa-arrow-up me-1"></i> +12% from last month</span>
                    </div>
                    <div class="kpi-icon-box kpi-icon-blue">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Active Members -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-12">
            <div class="card kpi-card-custom p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Active Members</span>
                        <h3 class="fw-bold text-heading my-1">46</h3>
                        <span class="text-success badge-subtext"><i class="fas fa-check-circle me-1"></i> 88% Active Ratio</span>
                    </div>
                    <div class="kpi-icon-box kpi-icon-green">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Inactive Members -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-12">
            <div class="card kpi-card-custom p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Inactive Members</span>
                        <h3 class="fw-bold text-heading my-1">3</h3>
                        <span class="text-danger badge-subtext"><i class="fas fa-user-clock me-1"></i> Requires follow-up</span>
                    </div>
                    <div class="kpi-icon-box kpi-icon-amber">
                        <i class="fas fa-user-minus"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Total Agents -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-12">
            <div class="card kpi-card-custom p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Total Agents</span>
                        <h3 class="fw-bold text-heading my-1">10</h3>
                        <span class="text-primary badge-subtext"><i class="fas fa-building me-1"></i> Across 4 Districts</span>
                    </div>
                    <div class="kpi-icon-box kpi-icon-purple">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Today's Collection -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-12">
            <div class="card kpi-card-custom p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Today's Collection</span>
                        <h3 class="fw-bold text-success my-1">₹42,500</h3>
                        <span class="text-success badge-subtext"><i class="fas fa-coins me-1"></i> 14 Receipts today</span>
                    </div>
                    <div class="kpi-icon-box kpi-icon-green">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. This Month Collection -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-12">
            <div class="card kpi-card-custom p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Month Collection</span>
                        <h3 class="fw-bold text-primary my-1">₹3,00,500</h3>
                        <span class="text-success badge-subtext"><i class="fas fa-chart-line me-1"></i> Target 92% achieved</span>
                    </div>
                    <div class="kpi-icon-box kpi-icon-blue">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Pending Payments -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-12">
            <div class="card kpi-card-custom p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Pending Payments</span>
                        <h3 class="fw-bold text-danger my-1">₹1,44,000</h3>
                        <span class="text-warning badge-subtext"><i class="fas fa-exclamation-triangle me-1"></i> 13 Members overdue</span>
                    </div>
                    <div class="kpi-icon-box kpi-icon-orange">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. Total Events -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-12">
            <div class="card kpi-card-custom p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Total Events</span>
                        <h3 class="fw-bold text-heading my-1">5</h3>
                        <span class="text-success badge-subtext"><i class="fas fa-calendar-check me-1"></i> 1 Event upcoming</span>
                    </div>
                    <div class="kpi-icon-box kpi-icon-amber">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Monthly Collection & New Member Registrations -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8 col-12">
            <div class="card chart-container-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-chart-line text-primary me-2"></i> Monthly Collection Trend (Last 12 Months)
                    </h5>
                    <span class="badge bg-label-success">₹42.2L Total</span>
                </div>
                <div class="card-body pt-3" style="min-height: 320px; position: relative;">
                    <canvas id="chartMonthlyCollection" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="card chart-container-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-user-plus text-warning me-2"></i> New Registrations
                    </h5>
                    <span class="badge bg-label-warning">2026 Trend</span>
                </div>
                <div class="card-body pt-3" style="min-height: 320px; position: relative;">
                    <canvas id="chartNewMembers" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: Scheme Distribution & Top Agent Collections -->
    <div class="row g-3 mb-4">
        <div class="col-lg-5 col-12">
            <div class="card chart-container-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-chart-pie text-info me-2"></i> Scheme Distribution
                    </h5>
                    <small class="text-muted">Total Enrolments</small>
                </div>
                <div class="card-body pt-3 d-flex align-items-center justify-content-center" style="min-height: 280px; position: relative;">
                    <canvas id="chartSchemeDistribution" height="260"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-7 col-12">
            <div class="card chart-container-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-medal text-primary me-2"></i> Top Agent Collections
                    </h5>
                    <span class="badge bg-label-primary">Rankings</span>
                </div>
                <div class="card-body pt-3" style="min-height: 280px; position: relative;">
                    <canvas id="chartAgentCollections" height="260"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Collections & Transactions Table -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-receipt text-primary me-2"></i> Recent Society Collections & Receipts
                        </h5>
                        <small class="text-muted">Latest payment transactions recorded in the system</small>
                    </div>
                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary">View All Receipts</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt No</th>
                                <th>Member Name</th>
                                <th>Scheme</th>
                                <th>Agent</th>
                                <th>Amount</th>
                                <th>Payment Mode</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong class="text-primary">REC-2026-5001</strong></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-xs bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user small"></i>
                                        </div>
                                        <span>राधेश्याम शर्मा</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-primary">बुजुर्ग सम्मान योजना</span></td>
                                <td>रामेश्वर लाल (AGT-001)</td>
                                <td><strong class="text-success">₹1,100</strong></td>
                                <td><span class="badge bg-label-success">UPI / GPay</span></td>
                                <td>{{ date('d M Y') }}</td>
                                <td><span class="badge bg-success">Verified</span></td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-outline-secondary" title="Print Receipt"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-xs btn-outline-success" title="Send WhatsApp"><i class="fab fa-whatsapp"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong class="text-primary">REC-2026-5002</strong></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-xs bg-label-warning rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user small"></i>
                                        </div>
                                        <span>मुरारी लाल अग्रवाल</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-warning">विवाह योजना</span></td>
                                <td>सुरेश कुमार (AGT-002)</td>
                                <td><strong class="text-success">₹2,500</strong></td>
                                <td><span class="badge bg-label-info">Cash</span></td>
                                <td>{{ date('d M Y') }}</td>
                                <td><span class="badge bg-success">Verified</span></td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-outline-secondary" title="Print Receipt"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-xs btn-outline-success" title="Send WhatsApp"><i class="fab fa-whatsapp"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong class="text-primary">REC-2026-5003</strong></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-xs bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user small"></i>
                                        </div>
                                        <span>कैलाश चंद्र जांगिड़</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-primary">बुजुर्ग सम्मान योजना</span></td>
                                <td>दिनेश पारीक (AGT-003)</td>
                                <td><strong class="text-success">₹1,500</strong></td>
                                <td><span class="badge bg-label-secondary">Bank Transfer</span></td>
                                <td>{{ date('d M Y', strtotime('-1 day')) }}</td>
                                <td><span class="badge bg-success">Verified</span></td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-outline-secondary" title="Print Receipt"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-xs btn-outline-success" title="Send WhatsApp"><i class="fab fa-whatsapp"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong class="text-primary">REC-2026-5004</strong></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-xs bg-label-warning rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user small"></i>
                                        </div>
                                        <span>विष्णु दत्त जोशी</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-warning">विवाह योजना</span></td>
                                <td>महेश शर्मा (AGT-004)</td>
                                <td><strong class="text-success">₹2,000</strong></td>
                                <td><span class="badge bg-label-success">UPI / PhonePe</span></td>
                                <td>{{ date('d M Y', strtotime('-1 day')) }}</td>
                                <td><span class="badge bg-success">Verified</span></td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-outline-secondary" title="Print Receipt"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-xs btn-outline-success" title="Send WhatsApp"><i class="fab fa-whatsapp"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Monthly Collection Trend Area Chart
    const ctxMonthly = document.getElementById('chartMonthlyCollection');
    if (ctxMonthly) {
        new Chart(ctxMonthly, {
            type: 'line',
            data: {
                labels: ['Sep 25', 'Oct 25', 'Nov 25', 'Dec 25', 'Jan 26', 'Feb 26', 'Mar 26', 'Apr 26', 'May 26', 'Jun 26', 'Jul 26', 'Aug 26'],
                datasets: [{
                    label: 'Collection (₹)',
                    data: [185000, 210000, 245000, 190000, 280000, 310000, 340000, 290000, 360000, 325000, 385000, 420000],
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#1E3A8A',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ' Collection: ₹' + ctx.raw.toLocaleString('en-IN'); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) { return '₹' + (val / 1000) + 'k'; }
                        }
                    }
                }
            }
        });
    }

    // 2. New Members Bar Chart
    const ctxNew = document.getElementById('chartNewMembers');
    if (ctxNew) {
        new Chart(ctxNew, {
            type: 'bar',
            data: {
                labels: ['Mar 26', 'Apr 26', 'May 26', 'Jun 26', 'Jul 26', 'Aug 26'],
                datasets: [{
                    label: 'New Registrations',
                    data: [4, 6, 8, 5, 11, 14],
                    backgroundColor: '#D97706',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 2 } } }
            }
        });
    }

    // 3. Scheme Distribution Doughnut Chart
    const ctxScheme = document.getElementById('chartSchemeDistribution');
    if (ctxScheme) {
        new Chart(ctxScheme, {
            type: 'doughnut',
            data: {
                labels: ['बुजुर्ग सम्मान योजना', 'विवाह योजना'],
                datasets: [{
                    data: [32, 20],
                    backgroundColor: ['#1E3A8A', '#EA580C'],
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // 4. Top Agent Collections Bar Chart
    const ctxAgent = document.getElementById('chartAgentCollections');
    if (ctxAgent) {
        new Chart(ctxAgent, {
            type: 'bar',
            data: {
                labels: ['रामेश्वर लाल', 'सुरेश कुमार', 'दिनेश पारीक', 'महेश शर्मा', 'विकास सोनी'],
                datasets: [{
                    label: 'Collection (₹)',
                    data: [82500, 68000, 54500, 48000, 39500],
                    backgroundColor: '#2563EB',
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ' ₹' + ctx.raw.toLocaleString('en-IN'); }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection