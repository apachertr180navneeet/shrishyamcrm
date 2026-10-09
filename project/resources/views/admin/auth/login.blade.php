@extends('admin.layouts.login_layout') 

@section('style')
<style>
    .authentication-wrapper.authentication-basic .authentication-inner {
        max-width: 440px;
    }
    .login-card {
        border-radius: 18px;
        box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(226, 232, 240, 0.85);
        border: none;
        overflow: hidden;
        background: #ffffff;
    }
    .role-switcher {
        display: flex;
        background: #f1f5f9;
        border-radius: 12px;
        padding: 4px;
        border: 1px solid #e2e8f0;
        gap: 4px;
    }
    .role-tab {
        flex: 1;
        padding: 10px 14px;
        font-size: 0.92rem;
        font-weight: 700;
        border: none;
        border-radius: 9px;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .role-tab.active-admin {
        background: #4f46e5;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }
    .role-tab.active-agent {
        background: #059669;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
    }
    .role-tab:hover:not(.active-admin):not(.active-agent) {
        color: #1e293b;
        background: #e2e8f0;
    }
    .login-input-group .form-control {
        border-top-right-radius: 8px !important;
        border-bottom-right-radius: 8px !important;
    }
    .login-input-group .input-group-text {
        border-top-left-radius: 8px !important;
        border-bottom-left-radius: 8px !important;
    }
    .btn-submit-login {
        height: 48px;
        font-size: 1rem;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease-in-out;
        border: none;
    }
    .btn-submit-login.btn-admin {
        background: #4f46e5;
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
    }
    .btn-submit-login.btn-admin:hover {
        background: #4338ca;
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.45);
        color: #ffffff;
    }
    .btn-submit-login.btn-agent {
        background: #059669;
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35);
    }
    .btn-submit-login.btn-agent:hover {
        background: #047857;
        box-shadow: 0 8px 20px rgba(5, 150, 105, 0.45);
        color: #ffffff;
    }
    .input-group-text.cursor-pointer:hover {
        background-color: #f8fafc;
    }
</style>
@endsection

@section('content') 

<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-4">
        <div class="card login-card">
            <div class="card-body p-4 p-sm-4">
                
                <!-- Logo & Brand Header -->
                <div class="text-center mb-3">
                    <img src="{{ asset('assets/logo.svg') }}" alt="श्री श्याम वेलफेयर सोसायटी" style="width: 60px; height: 60px; object-fit: contain;" class="mb-2 drop-shadow">
                    <h4 class="mb-1 fw-bold" style="color: #1e3a8a; letter-spacing: -0.3px;">श्री श्याम वेलफेयर सोसायटी</h4>
                    <p class="text-muted small mb-0">एडमिन एवं कार्यकर्ता पोर्टल लॉगिन (ERP Login)</p>
                </div>

                <!-- Role Switcher: Admin (Email) vs Agent (Mobile) -->
                <div class="role-switcher mb-3">
                    <button type="button" class="role-tab active-admin" id="btn-tab-admin" onclick="setLoginType('admin')">
                        <i class="bx bx-shield-quarter me-1"></i> एडमिन (Admin)
                    </button>
                    <button type="button" class="role-tab" id="btn-tab-agent" onclick="setLoginType('agent')">
                        <i class="bx bx-mobile-alt me-1"></i> कार्यकर्ता (Agent)
                    </button>
                </div>

                <!-- Alert Notifications -->
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible py-2 px-3 mb-3 small d-flex align-items-center" role="alert">
                        <i class="bx bx-error-circle fs-5 me-2 flex-shrink-0"></i>
                        <div class="flex-grow-1">{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible py-2 px-3 mb-3 small d-flex align-items-center" role="alert">
                        <i class="bx bx-error-circle fs-5 me-2 flex-shrink-0"></i>
                        <div class="flex-grow-1">{{ $errors->first() }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('admin.login.post') }}" id="loginForm" method="POST">
                    @csrf
                    <!-- Hidden field to indicate login type -->
                    <input type="hidden" name="login_type" id="login_type" value="{{ old('login_type', request('type', 'admin')) }}" />

                    <!-- Primary Identifier Input (Email for Admin, Mobile for Agent) -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="login_input" class="form-label fw-bold mb-0">
                                <i class="bx bx-envelope text-primary me-1" id="login_label_icon"></i>
                                <span id="login_label_text">ईमेल पता (Email Address)</span>
                            </label>
                            <span class="badge bg-label-primary rounded-pill small" id="login_badge">Email Required</span>
                        </div>
                        <div class="input-group input-group-merge login-input-group">
                            <span class="input-group-text bg-light text-muted" id="login_addon">
                                <i class="bx bx-at fs-5" id="login_addon_icon"></i>
                            </span>
                            <input
                                type="email"
                                class="form-control form-control-lg fw-semibold"
                                id="login_input"
                                name="email"
                                placeholder="admin@shrishyam.org"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                            />
                        </div>
                        <small class="text-muted d-block mt-1" id="login_hint">
                            <i class="bx bx-info-circle me-1 text-primary" id="login_hint_icon"></i>
                            <span id="login_hint_text">एडमिन / व्यवस्थापक अपने पंजीकृत ईमेल पते से लॉगिन करें।</span>
                        </small>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3 form-password-toggle">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold mb-0" for="password">
                                <i class="bx bx-lock-alt text-primary me-1" id="password_label_icon"></i> पासवर्ड (Password)
                            </label>
                            <a href="{{ route('admin.forget.password.get') }}" class="text-decoration-none small fw-semibold" id="forgotPasswordLink" style="color: #4f46e5;">
                                Forgot Password?
                            </a>
                        </div>
                        <div class="input-group input-group-merge login-input-group">
                            <span class="input-group-text bg-light text-muted">
                                <i class="bx bx-key fs-5"></i>
                            </span>
                            <input
                                type="password"
                                id="password"
                                class="form-control form-control-lg"
                                name="password"
                                placeholder="············"
                                aria-describedby="password"
                                required
                                autocomplete="current-password"
                            />
                            <span class="input-group-text cursor-pointer" id="passwordToggleBtn">
                                <i class="bx bx-hide fs-5"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-3 d-flex align-items-center justify-content-between">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                            <label class="form-check-label small text-muted" for="remember-me"> मुझे याद रखें (Remember Me) </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mb-3">
                        <button id="btn_submit" class="btn btn-submit-login btn-admin w-100 fw-bold" type="submit">
                            <i class="bx bx-log-in fs-5" id="btn_submit_icon"></i>
                            <span id="btn_submit_text">एडमिन लॉगिन करें (Admin Sign In)</span>
                        </button>
                    </div>
                </form>

                <!-- Footer Helpline -->
                <div class="text-center mt-3 pt-3 border-top">
                    <small class="text-muted d-inline-flex align-items-center gap-1">
                        <i class="bx bx-headphone text-primary"></i>
                        <span>सहायता हेल्पलाइन:</span>
                        <a href="tel:9664090906" class="fw-bold text-dark text-decoration-none">9664090906</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    function setLoginType(type) {
        const btnAdmin = document.getElementById('btn-tab-admin');
        const btnAgent = document.getElementById('btn-tab-agent');
        const hiddenType = document.getElementById('login_type');

        const labelIcon = document.getElementById('login_label_icon');
        const labelText = document.getElementById('login_label_text');
        const loginBadge = document.getElementById('login_badge');
        const loginAddon = document.getElementById('login_addon');
        const loginInput = document.getElementById('login_input');
        const hintIcon = document.getElementById('login_hint_icon');
        const hintText = document.getElementById('login_hint_text');

        const btnSubmit = document.getElementById('btn_submit');
        const btnSubmitText = document.getElementById('btn_submit_text');
        const forgotLink = document.getElementById('forgotPasswordLink');
        const passIcon = document.getElementById('password_label_icon');

        if (type === 'agent') {
            // Tab styling
            btnAdmin.className = 'role-tab';
            btnAgent.className = 'role-tab active-agent';
            hiddenType.value = 'agent';

            // Input header
            labelIcon.className = 'bx bx-mobile-alt text-success me-1';
            labelText.innerText = 'मोबाइल नंबर (Mobile Number)';
            loginBadge.className = 'badge bg-label-success rounded-pill small';
            loginBadge.innerText = '10-Digit Mobile';

            // Addon icon
            loginAddon.innerHTML = '<span class="fw-bold text-success small">+91</span>';

            // Input field
            loginInput.type = 'tel';
            loginInput.placeholder = '10-अंकीय मोबाइल नंबर (e.g. 9829012345)';
            loginInput.setAttribute('maxlength', '10');
            loginInput.setAttribute('pattern', '[0-9]{10}');
            loginInput.setAttribute('autocomplete', 'tel');

            // Hint
            hintIcon.className = 'bx bx-shield-quarter me-1 text-success';
            hintText.innerText = 'कार्यकर्ता अपने पंजीकृत 10-अंकीय मोबाइल नंबर व पासवर्ड से सीधे लॉगिन करें।';

            // Password icon & forgot link
            passIcon.className = 'bx bx-lock-alt text-success me-1';
            forgotLink.style.color = '#059669';

            // Submit Button
            btnSubmit.className = 'btn btn-submit-login btn-agent w-100 fw-bold';
            btnSubmitText.innerText = 'कार्यकर्ता लॉगिन करें (Agent Sign In)';
        } else {
            // Admin default
            btnAdmin.className = 'role-tab active-admin';
            btnAgent.className = 'role-tab';
            hiddenType.value = 'admin';

            // Input header
            labelIcon.className = 'bx bx-envelope text-primary me-1';
            labelText.innerText = 'ईमेल पता (Email Address)';
            loginBadge.className = 'badge bg-label-primary rounded-pill small';
            loginBadge.innerText = 'Email Required';

            // Addon icon
            loginAddon.innerHTML = '<i class="bx bx-at fs-5"></i>';

            // Input field
            loginInput.type = 'email';
            loginInput.placeholder = 'admin@shrishyam.org';
            loginInput.removeAttribute('maxlength');
            loginInput.removeAttribute('pattern');
            loginInput.setAttribute('autocomplete', 'username');

            // Hint
            hintIcon.className = 'bx bx-info-circle me-1 text-primary';
            hintText.innerText = 'एडमिन / व्यवस्थापक अपने पंजीकृत ईमेल पते से लॉगिन करें।';

            // Password icon & forgot link
            passIcon.className = 'bx bx-lock-alt text-primary me-1';
            forgotLink.style.color = '#4f46e5';

            // Submit Button
            btnSubmit.className = 'btn btn-submit-login btn-admin w-100 fw-bold';
            btnSubmitText.innerText = 'एडमिन लॉगिन करें (Admin Sign In)';
        }

        loginInput.focus();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const requestedType = urlParams.get('type');
        const initialType = "{{ old('login_type') }}" || requestedType || 'admin';

        // Auto-switch to agent if previous input was 10-digits
        const oldVal = "{{ old('email') }}";
        if (oldVal && /^\d{10}$/.test(oldVal.trim())) {
            setLoginType('agent');
        } else if (initialType === 'agent') {
            setLoginType('agent');
        } else {
            setLoginType('admin');
        }

        // Restrict Agent mobile input to numbers only
        const loginInput = document.getElementById('login_input');
        loginInput.addEventListener('input', function() {
            const currentType = document.getElementById('login_type').value;
            if (currentType === 'agent') {
                this.value = this.value.replace(/[^0-9]/g, '');
            }
        });

        // Toggle password visibility
        const toggleBtn = document.getElementById('passwordToggleBtn');
        const passwordInput = document.getElementById('password');
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    if (icon) {
                        icon.classList.remove('bx-hide');
                        icon.classList.add('bx-show');
                    }
                } else {
                    passwordInput.type = 'password';
                    if (icon) {
                        icon.classList.remove('bx-show');
                        icon.classList.add('bx-hide');
                    }
                }
            });
        }
    });
</script>
@endsection
