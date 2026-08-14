@extends('admin.layouts.app')

@section('style')
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h4 class="fw-bold mb-1">
                                Welcome back, {{ Auth::user()->full_name ?? (Auth::user()->first_name ?? 'Admin') }}! 👋
                            </h4>
                            <p class="text-muted mb-0">Here's a quick overview of your dashboard.</p>
                        </div>
                        <div>
                            <span class="badge bg-label-primary px-3 py-2 fs-6">
                                <i class="bx bx-shield-quarter me-1"></i> {{ ucfirst(Auth::user()->role ?? 'Admin') }} Panel
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Blank Content Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="min-height: 400px;">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-5">
                    <div class="avatar avatar-xl mb-3 bg-label-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="bx bx-grid-alt fs-2 text-primary"></i>
                    </div>
                    <h5 class="fw-semibold text-heading mb-2">Dashboard Ready</h5>
                    <p class="text-muted max-w-md mb-0" style="max-width: 450px;">
                        Your admin dashboard is configured and ready. Start adding widgets, charts, statistics, and tables here.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@endsection