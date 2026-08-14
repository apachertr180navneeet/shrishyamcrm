@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1" style="color: #1B365D;"><i class="fas fa-cogs me-2"></i>Society Master Settings</h4>
                <p class="text-muted mb-0">Configure official society metadata, header titles, signatories, and prefixes</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            <div class="row g-4">
                <!-- Basic Information -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="card-title fw-bold mb-0" style="color: #1B365D;"><i class="fas fa-building me-2 text-primary"></i>Society Identity</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Society Name (English)</label>
                                <input type="text" name="society_name" class="form-control" value="{{ $settings['society_name'] ?? 'Shri Shyam Welfare Society' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Society Name (Hindi)</label>
                                <input type="text" name="society_name_hindi" class="form-control" value="{{ $settings['society_name_hindi'] ?? 'श्री श्याम वेलफेयर सोसायटी लोहीकी' }}">
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Registration No</label>
                                    <input type="text" name="reg_no" class="form-control" value="{{ $settings['reg_no'] ?? 'HR/019/2021/04582' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">SAN Prefix</label>
                                    <input type="text" name="san_prefix" class="form-control" value="{{ $settings['san_prefix'] ?? 'SAN-LOH' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Official Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ $settings['address'] ?? 'Main Bazar, Lohki, District Mahendragarh, Haryana - 123001' }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Helpline Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $settings['phone'] ?? '+91 98290 12345' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Official Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ $settings['email'] ?? 'info@shrishyamwelfare.org' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Signatories & Preferences -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="card-title fw-bold mb-0" style="color: #1B365D;"><i class="fas fa-signature me-2 text-warning"></i>Signatories & Automation</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">President / अध्यक्ष Name</label>
                                <input type="text" name="president_name" class="form-control" value="{{ $settings['president_name'] ?? 'Shri R. K. Sharma' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">General Secretary / महासचिव Name</label>
                                <input type="text" name="secretary_name" class="form-control" value="{{ $settings['secretary_name'] ?? 'Shri Mahesh Garg' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Treasurer / कोषाध्यक्ष Name</label>
                                <input type="text" name="treasurer_name" class="form-control" value="{{ $settings['treasurer_name'] ?? 'Shri Rameshwar Lal' }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Default Event Rate (₹)</label>
                                    <input type="number" name="default_event_rate" class="form-control" value="{{ $settings['default_event_rate'] ?? '200' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Default Commission (%)</label>
                                    <input type="number" name="default_commission" class="form-control" value="{{ $settings['default_commission'] ?? '5' }}">
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary px-4 py-2" style="background: #1B365D; border-color: #1B365D;">
                                    <i class="fas fa-save me-1"></i> Save Society Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
