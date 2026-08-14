@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">सोसायटी रसीदें (Official Society Receipts)</h4>
                    <p class="text-muted mb-0">Browse and print official society financial receipts with SAN verification codes and QR stamps.</p>
                </div>
                <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Payment Entry
                </a>
            </div>
        </div>
    </div>

    <!-- Receipts Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt No</th>
                        <th>SAN Code</th>
                        <th>Member Name</th>
                        <th>Scheme</th>
                        <th>Amount</th>
                        <th>Mode</th>
                        <th>Date</th>
                        <th class="text-center">Print / View</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $p)
                    <tr>
                        <td><strong class="text-primary">{{ $p->receipt_no }}</strong></td>
                        <td><span class="badge bg-label-secondary">{{ $p->san_code ?? 'SAN-LOH-001' }}</span></td>
                        <td>
                            <strong>{{ $p->member ? $p->member->full_name : 'N/A' }}</strong>
                            <small class="d-block text-muted">{{ $p->member ? $p->member->membership_no : '' }}</small>
                        </td>
                        <td><span class="badge bg-label-primary">{{ $p->member && $p->member->scheme ? $p->member->scheme->name_hindi : 'N/A' }}</span></td>
                        <td><strong class="text-success fs-6">₹{{ number_format($p->amount) }}</strong></td>
                        <td><span class="badge bg-label-info">{{ $p->payment_mode }}</span></td>
                        <td>{{ $p->payment_date ? $p->payment_date->format('d M Y') : '' }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.payments.receipt', $p->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="fas fa-print me-1"></i> Print Receipt
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
