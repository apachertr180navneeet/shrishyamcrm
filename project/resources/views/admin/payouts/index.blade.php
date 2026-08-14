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
                        <th>Event Reference</th>
                        <th>Beneficiary Name</th>
                        <th>Disbursed Amount</th>
                        <th>Payment Mode / UTR</th>
                        <th>Payout Date</th>
                        <th>Approved By</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts as $p)
                    <tr>
                        <td><strong class="text-primary">{{ $p->payout_no }}</strong></td>
                        <td>
                            <strong>{{ $p->event ? $p->event->event_code : 'General' }}</strong>
                            <small class="d-block text-muted">{{ $p->event ? $p->event->title : '' }}</small>
                        </td>
                        <td>
                            <strong class="text-heading">{{ $p->beneficiary_name }}</strong>
                            <small class="d-block text-muted">{{ $p->relation }}</small>
                        </td>
                        <td><strong class="text-success fs-5">₹{{ number_format($p->amount) }}</strong></td>
                        <td>
                            <span class="badge bg-label-info">{{ $p->payment_mode }}</span>
                            @if($p->transaction_ref)
                                <small class="d-block text-muted">{{ $p->transaction_ref }}</small>
                            @endif
                        </td>
                        <td>{{ $p->payout_date ? $p->payout_date->format('d M Y') : '' }}</td>
                        <td><small>{{ $p->approved_by ?? 'Super Admin' }}</small></td>
                        <td><span class="badge bg-success">{{ $p->status }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No beneficiary payouts recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Disburse Payout Modal -->
<div class="modal fade" id="disbursePayoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.payouts.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Disburse Welfare Assistance Payout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Event / Case <span class="text-danger">*</span></label>
                        <select name="event_id" class="form-select" required>
                            @foreach($events as $ev)
                            <option value="{{ $ev->id }}">{{ $ev->event_code }} - {{ $ev->title }} (Target: ₹{{ number_format($ev->target_amount) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Beneficiary Name <span class="text-danger">*</span></label>
                            <input type="text" name="beneficiary_name" class="form-control" placeholder="e.g. Radheshyam Sharma" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Relation</label>
                            <input type="text" name="relation" class="form-control" placeholder="e.g. Father & Bride">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Payout Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control fw-bold text-success" value="51000" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Payout Date <span class="text-danger">*</span></label>
                            <input type="date" name="payout_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="Bank Transfer">Bank Transfer (NEFT/RTGS)</option>
                                <option value="Cheque">Cheque</option>
                                <option value="UPI">UPI Transfer</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">UTR / Cheque No</label>
                            <input type="text" name="transaction_ref" class="form-control" placeholder="e.g. NEFT-SBIN-8912">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Sanctioned assistance grant..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Disburse Payout</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
