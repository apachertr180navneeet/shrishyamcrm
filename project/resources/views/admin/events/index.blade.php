@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Hind', sans-serif;">विवाह सहायता कार्यक्रम (Marriage Events & Support Pool)</h4>
                    <p class="text-muted mb-0">Manage girl child marriage welfare grants (₹51,000 assistance), event collections, and member contribution billing.</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventBillingModal">
                        <i class="fas fa-calculator me-1"></i> Bill Members for Event
                    </button>
                    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addEventModal">
                        <i class="fas fa-plus me-1"></i> Create Marriage Event
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Events Cards Grid -->
    <div class="row g-4">
        @foreach($events as $event)
        <div class="col-lg-6 col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between bg-light">
                    <div>
                        <span class="badge bg-label-primary mb-1">{{ $event->event_code }}</span>
                        <h5 class="card-title mb-0 fw-bold">{{ $event->title }}</h5>
                    </div>
                    <span class="badge {{ $event->status == 'Completed' ? 'bg-success' : ($event->status == 'Active' ? 'bg-primary' : 'bg-warning') }}">
                        {{ $event->status }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Girl / Bride Name</small>
                            <strong class="fs-6 text-primary">{{ $event->girl_name }}</strong>
                        </div>
                        <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Father / Guardian</small>
                            <strong class="fs-6">{{ $event->father_name ?? ($event->member ? $event->member->full_name : 'N/A') }}</strong>
                        </div>
                        <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Marriage Date</small>
                            <strong><i class="fas fa-calendar-alt text-danger me-1"></i> {{ $event->event_date ? $event->event_date->format('d M Y') : '' }}</strong>
                        </div>
                        <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Assistance Grant Pool</small>
                            <strong class="fs-5 text-success">₹{{ number_format($event->target_amount) }}</strong>
                        </div>
                    </div>

                    <div class="bg-lighter p-3 rounded mb-3">
                        <small class="text-muted d-block mb-1">Venue / विवाह स्थल</small>
                        <span class="text-dark"><i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $event->venue ?? 'Shri Shyam Dharamshala, Lohki' }}</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                        <small class="text-muted">{{ $event->description }}</small>
                        @if($event->status != 'Completed')
                        <a href="{{ route('admin.payouts.index') }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-hand-holding-usd me-1"></i> Disburse Grant
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.events.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create Marriage Welfare Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Event Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Kumari Pooja Marriage Assistance" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Girl's Name <span class="text-danger">*</span></label>
                            <input type="text" name="girl_name" class="form-control" placeholder="e.g. Kumari Pooja Sharma" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Father / Member</label>
                            <input type="text" name="father_name" class="form-control" placeholder="e.g. Radheshyam Sharma">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Event Date <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Grant Target (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="target_amount" class="form-control" value="51000" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Venue (स्थल)</label>
                        <input type="text" name="venue" class="form-control" placeholder="e.g. Shri Shyam Dharamshala, Lohki">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Event notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Event Billing Modal -->
<div class="modal fade" id="eventBillingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.events.billing') }}" method="POST">
                @csrf
                <div class="modal-header" style="background: #1B365D; color: #fff;">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-calculator me-2"></i>Consolidated Monthly Event Billing</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Automatically posts consolidated event charges to member financial ledgers with duplicate protection.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Billing Month <span class="text-danger">*</span></label>
                        <input type="month" name="billing_month" class="form-control" value="{{ date('Y-m') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Applicable Event (Optional)</label>
                        <select name="event_id" class="form-select">
                            <option value="">-- Consolidated Pool / All Events --</option>
                            @foreach($events as $ev)
                            <option value="{{ $ev->id }}">{{ $ev->event_code }} - {{ $ev->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Target Scheme (Optional)</label>
                        <select name="scheme_id" class="form-select">
                            <option value="">-- All Active Members Across Schemes --</option>
                            @foreach($schemes as $sch)
                            <option value="{{ $sch->id }}">{{ $sch->name_hindi }} ({{ $sch->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Number of Events</label>
                            <input type="number" name="events_count" class="form-control" value="1" min="1" required id="billingEventsCount" oninput="updateTotalCharge()">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Rate per Event (₹)</label>
                            <input type="number" name="rate_per_event" class="form-control" value="200" min="1" required id="billingRatePerEvent" oninput="updateTotalCharge()">
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded border text-center">
                        <small class="text-muted d-block">Total Debit Per Member</small>
                        <h4 class="text-primary fw-bold mb-0" id="totalDebitPerMember">₹200.00</h4>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: #1B365D;">Process Consolidated Billing</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateTotalCharge() {
    const count = parseFloat(document.getElementById('billingEventsCount').value) || 0;
    const rate = parseFloat(document.getElementById('billingRatePerEvent').value) || 0;
    const total = count * rate;
    document.getElementById('totalDebitPerMember').innerText = '₹' + total.toFixed(2);
}
</script>
@endsection
