@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">लाभार्थी सहायता वितरण (Beneficiary Payouts Manager)</h4>
                    <p class="text-muted mb-0">Total Grants Disbursed: <strong class="text-success fs-5">₹{{ number_format($totalDisbursed) }}</strong></p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#disbursePayoutModal">
                    <i class="fas fa-hand-holding-usd me-1"></i> Disburse Beneficiary Payout
                </button>
            </div>
        </div>
    </div>

    <!-- Payouts Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Payout No</th>
                        <th>Event / Purpose</th>
                        <th>Beneficiary Name</th>
                        <th>Associated Member</th>
                        <th>Disbursed Amount</th>
                        <th>Payment Mode / UTR</th>
                        <th>Payout Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts as $p)
                    <tr>
                        <td><span class="badge bg-label-primary fs-6">{{ $p->payout_no }}</span></td>
                        <td>
                            <strong>{{ $p->event ? $p->event->event_code : ($p->payout_type ?? 'General Assistance') }}</strong>
                            <small class="d-block text-muted">{{ $p->event ? $p->event->title : '' }}</small>
                        </td>
                        <td>
                            <strong class="text-heading">{{ $p->beneficiary_name }}</strong>
                            <small class="d-block text-muted">{{ $p->relation ?: 'Beneficiary' }}</small>
                        </td>
                        <td>
                            @if($p->member)
                                <strong>{{ $p->member->full_name }}</strong>
                                <small class="d-block text-muted">{{ $p->member->membership_no }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><strong class="text-success fs-5">₹{{ number_format($p->amount) }}</strong></td>
                        <td>
                            <span class="badge bg-label-info">{{ $p->payment_mode }}</span>
                            @if($p->transaction_ref)
                                <small class="d-block text-muted">{{ $p->transaction_ref }}</small>
                            @endif
                        </td>
                        <td>{{ $p->payout_date ? $p->payout_date->format('d M Y') : '-' }}</td>
                        <td>
                            <span class="badge {{ $p->status === 'Disbursed' ? 'bg-success' : ($p->status === 'Approved' ? 'bg-info' : 'bg-warning') }}">
                                {{ $p->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <div class="avatar avatar-xl bg-label-primary mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                <i class="fas fa-hand-holding-usd fs-3"></i>
                            </div>
                            <h6 class="fw-bold mb-1">No Beneficiary Payouts Recorded</h6>
                            <p class="text-muted mb-3">Record and disburse welfare grants and marriage assistance payouts.</p>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#disbursePayoutModal">
                                <i class="fas fa-plus me-1"></i> Disburse First Payout
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Disburse Payout Modal -->
<div class="modal fade" id="disbursePayoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.payouts.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-hand-holding-usd text-success me-2"></i> Disburse Welfare Assistance Payout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Auto Payout Code (वितरण संख्या)</label>
                            <input type="text" class="form-control bg-light fw-bold text-primary" value="{{ $nextPayoutNo }}" readonly>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Assistance Type (सहायता प्रकार) <span class="text-danger">*</span></label>
                            <select name="payout_type" class="form-select" required>
                                <option value="Marriage Assistance">Marriage Assistance (कन्यादान / विवाह सहायता)</option>
                                <option value="Medical Aid">Medical Aid (चिकित्सा सहायता)</option>
                                <option value="Death Relief Grant">Death Relief Grant (मृत्यु उपरांत सहायता)</option>
                                <option value="Education Grant">Education Grant (शिक्षा सहायता)</option>
                                <option value="General Welfare">General Welfare Grant (सामान्य समाज सेवा सहायता)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Select Event / Case (विवाह कार्यक्रम)</label>
                            <select name="event_id" class="form-select">
                                <option value="">-- General Welfare Assistance (No Event) --</option>
                                @foreach($events as $ev)
                                <option value="{{ $ev->id }}">{{ $ev->event_code }} - {{ $ev->title }} (Target: ₹{{ number_format($ev->target_amount) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Associated Member (संबंधित सदस्य)</label>
                            <select name="member_id" class="form-select">
                                <option value="">-- Direct / Non-Member Beneficiary --</option>
                                @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->membership_no }} - {{ $m->full_name }} ({{ $m->district }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Beneficiary Full Name (लाभार्थी का नाम) <span class="text-danger">*</span></label>
                            <input type="text" name="beneficiary_name" class="form-control" placeholder="e.g. Radheshyam Sharma" required>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Relation with Bride / Member (संबंध)</label>
                            <input type="text" name="relation" class="form-control" placeholder="e.g. Father & Bride">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Payout Amount (वितरण राशि ₹) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control fw-bold text-success fs-5" value="51000" min="1" step="100" required>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Payout Date (वितरण दिनांक) <span class="text-danger">*</span></label>
                            <input type="date" name="payout_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Payment Mode (भुगतान माध्यम) <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="Bank Transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                                <option value="Cheque">Cheque (बैंक चेक)</option>
                                <option value="UPI">UPI Transfer (GooglePay / PhonePe / Paytm)</option>
                                <option value="Cash">Cash (नकद भुगतान)</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">UTR / Cheque / Transaction Reference</label>
                            <input type="text" name="transaction_ref" class="form-control" placeholder="e.g. NEFT-SBIN-891234">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">Remarks & Notes (टिप्पणी)</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Sanctioned beneficiary assistance grant..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i> Disburse Payout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
