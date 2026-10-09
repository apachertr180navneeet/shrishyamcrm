@extends('admin.layouts.login_layout') 
@section('style')
<style>
    .role-switcher-container {
        background: #f1f3f6;
        border: 1px solid #e2e8f0;
        border-radius: 50rem;
        padding: 4px;
        display: flex;
        gap: 4px;
    }
    .role-pill-btn {
        flex: 1;
        border-radius: 50rem;
        padding: 0.55rem 0.75rem;
        font-weight: 700;
        font-size: 0.90rem;
        transition: all 0.25s ease-in-out;
        border: none;
        color: #64748b;
        background: transparent;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .role-pill-btn.active-admin {
        background: #696cff !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(105, 108, 255, 0.4);
    }
    .role-pill-btn.active-agent {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
    }
    .role-pill-btn:hover:not(.active-admin):not(.active-agent) {
        background: #e2e8f0;
        color: #1e293b;
    }
    .login-card {
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid #edf2f7;
    }
    .input-group-text.cursor-pointer:hover {
        background-color: #f8fafc;
    }
</style>
@endsection

@section('content') 

<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
        <!-- Register -->
        <div class="card login-card">
            <div class="card-body p-4">
                <!-- Logo -->
                <div class="app-brand justify-content-center mb-3">
                    <a href="{{ route('admin.login') }}" class="app-brand-link gap-2">
                        <span class="app-brand-logo demo">
                            <svg
                                width="25"
                                viewBox="0 0 25 42"
                                version="1.1"
                                xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink"
                                >
                                <defs>
                                    <path
                                        d="M13.7918663,0.358365126 L3.39788168,7.44174259 C0.566865006,9.69408886 -0.379795268,12.4788597 0.557900856,15.7960551 C0.68998853,16.2305145 1.09562888,17.7872135 3.12357076,19.2293357 C3.8146334,19.7207684 5.32369333,20.3834223 7.65075054,21.2172976 L7.59773219,21.2525164 L2.63468769,24.5493413 C0.445452254,26.3002124 0.0884951797,28.5083815 1.56381646,31.1738486 C2.83770406,32.8170431 5.20850219,33.2640127 7.09180128,32.5391577 C8.347334,32.0559211 11.4559176,30.0011079 16.4175519,26.3747182 C18.0338572,24.4997857 18.6973423,22.4544883 18.4080071,20.2388261 C17.963753,17.5346866 16.1776345,15.5799961 13.0496516,14.3747546 L10.9194936,13.4715819 L18.6192054,7.984237 L13.7918663,0.358365126 Z"
                                        id="path-1"
                                        ></path>
                                    <path
                                        d="M5.47320593,6.00457225 C4.05321814,8.216144 4.36334763,10.0722806 6.40359441,11.5729822 C8.61520715,12.571656 10.0999176,13.2171421 10.8577257,13.5094407 L15.5088241,14.433041 L18.6192054,7.984237 C15.5364148,3.11535317 13.9273018,0.573395879 13.7918663,0.358365126 C13.5790555,0.511491653 10.8061687,2.3935607 5.47320593,6.00457225 Z"
                                        id="path-3"
                                        ></path>
                                    <path
                                        d="M7.50063644,21.2294429 L12.3234468,23.3159332 C14.1688022,24.7579751 14.397098,26.4880487 13.008334,28.506154 C11.6195701,30.5242593 10.3099883,31.790241 9.07958868,32.3040991 C5.78142938,33.4346997 4.13234973,34 4.13234973,34 C4.13234973,34 2.75489982,33.0538207 2.37032616e-14,31.1614621 C-0.55822714,27.8186216 -0.55822714,26.0572515 -4.05231404e-15,25.8773518 C0.83734071,25.6075023 2.77988457,22.8248993 3.3049379,22.52991 C3.65497346,22.3332504 5.05353963,21.8997614 7.50063644,21.2294429 Z"
                                        id="path-4"
                                        ></path>
                                    <path
                                        d="M20.6,7.13333333 L25.6,13.8 C26.2627417,14.6836556 26.0836556,15.9372583 25.2,16.6 C24.8538077,16.8596443 24.4327404,17 24,17 L14,17 C12.8954305,17 12,16.1045695 12,15 C12,14.5672596 12.1403557,14.1461923 12.4,13.8 L17.4,7.13333333 C18.0627417,6.24967773 19.3163444,6.07059163 20.2,6.73333333 C20.3516113,6.84704183 20.4862915,6.981722 20.6,7.13333333 Z"
                                        id="path-5"
                                        ></path>
                                </defs>
                                <g id="g-app-brand" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="Brand-Logo" transform="translate(-27.000000, -15.000000)">
                                        <g id="Icon" transform="translate(27.000000, 15.000000)">
                                            <g id="Mask" transform="translate(0.000000, 8.000000)">
                                                <mask id="mask-2" fill="white">
                                                    <use xlink:href="#path-1"></use>
                                                </mask>
                                                <use fill="#696cff" xlink:href="#path-1"></use>
                                                <g id="Path-3" mask="url(#mask-2)">
                                                    <use fill="#696cff" xlink:href="#path-3"></use>
                                                    <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3"></use>
                                                </g>
                                                <g id="Path-4" mask="url(#mask-2)">
                                                    <use fill="#696cff" xlink:href="#path-4"></use>
                                                    <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-4"></use>
                                                </g>
                                            </g>
                                            <g
                                                id="Triangle"
                                                transform="translate(19.000000, 11.000000) rotate(-300.000000) translate(-19.000000, -11.000000) "
                                                >
                                                <use fill="#696cff" xlink:href="#path-5"></use>
                                                <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-5"></use>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </span>
                        <span class="app-brand-text demo text-body fw-bolder">{{ config('app.name') }}</span>
                    </a>
                </div>

                <div class="text-center mb-3">
                    <h4 class="mb-1 fw-bold text-primary">श्री श्याम वेलफेयर सोसायटी</h4>
                    <p class="text-muted small mb-0">पोर्टल लॉगिन प्रणाली (Portal Login)</p>
                </div>

                <!-- Role Switcher: Admin (Email) vs Agent (Mobile) -->
                <div class="role-switcher-container mb-3">
                    <button type="button" class="role-pill-btn active-admin" id="btn-tab-admin" onclick="setLoginType('admin')">
                        <i class="fas fa-user-shield me-1"></i> एडमिन (Admin)
                    </button>
                    <button type="button" class="role-pill-btn" id="btn-tab-agent" onclick="setLoginType('agent')">
                        <i class="fas fa-mobile-alt me-1"></i> कार्यकर्ता (Agent)
                    </button>
                </div>

                <!-- Role Info Alert Badge -->
                <div id="role-info-badge" class="alert alert-primary py-2 px-3 mb-3 d-flex align-items-center small" role="alert">
                    <i class="fas fa-info-circle fs-5 me-2" id="role-info-icon"></i>
                    <div>
                        <span id="role-info-title" class="fw-bold d-block">एडमिन लॉगिन (Email Required)</span>
                        <span id="role-info-desc" class="text-muted">एडमिन अपने पंजीकृत <strong>ईमेल (Email)</strong> से लॉगिन करें।</span>
                    </div>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible py-2 px-3 mb-3 small" role="alert">
                        <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible py-2 px-3 mb-3 small" role="alert">
                        <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('admin.login.post') }}" id="loginForm" class="mb-2" method="POST">
                    @csrf
                    <!-- Hidden field to indicate login type -->
                    <input type="hidden" name="login_type" id="login_type" value="{{ old('login_type', request('type', 'admin')) }}" />

                    <!-- Primary Identifier Input (Email for Admin, Mobile for Agent) -->
                    <div class="mb-3">
                        <label for="login_input" class="form-label fw-bold" id="login_label">
                            <i class="fas fa-envelope text-primary me-1" id="login_label_icon"></i>
                            <span id="login_label_text">ईमेल पता (Email Address)</span>
                        </label>
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
                        <small class="text-muted d-block mt-1" id="login_hint">
                            <i class="fas fa-shield-alt text-primary me-1" id="login_hint_icon"></i>
                            <span id="login_hint_text">एडमिन / व्यवस्थापक अपने पंजीकृत ईमेल पते व पासवर्ड से लॉगिन करें।</span>
                        </small>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3 form-password-toggle">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold mb-0" for="password">
                                <i class="fas fa-lock text-primary me-1"></i> पासवर्ड (Password)
                            </label>
                            <a href="{{ route('admin.forget.password.get') }}" id="forgotPasswordLink">
                                <small>Forgot Password?</small>
                            </a>
                        </div>
                        <div class="input-group input-group-merge">
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
                            <span class="input-group-text cursor-pointer" id="passwordToggleBtn"><i class="bx bx-hide"></i></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                            <label class="form-check-label small" for="remember-me"> मुझे याद रखें (Remember Me) </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mb-3">
                        <button id="btn_submit" class="btn btn-primary btn-lg d-grid w-100 fw-bold shadow-sm" type="submit">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            <span id="btn_submit_text">एडमिन लॉगिन करें (Admin Sign In)</span>
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3 pt-2 border-top">
                    <small class="text-muted">
                        <i class="fas fa-headset me-1 text-primary"></i> सहायता हेल्पलाइन: <span class="fw-semibold">9664090906</span>
                    </small>
                </div>
            </div>
        </div>
        <!-- /Register -->
    </div>
</div>

@endsection

@section('script')
<script>
    function setLoginType(type) {
        const btnAdmin = document.getElementById('btn-tab-admin');
        const btnAgent = document.getElementById('btn-tab-agent');
        const hiddenType = document.getElementById('login_type');

        const roleBadge = document.getElementById('role-info-badge');
        const roleIcon = document.getElementById('role-info-icon');
        const roleTitle = document.getElementById('role-info-title');
        const roleDesc = document.getElementById('role-info-desc');

        const labelIcon = document.getElementById('login_label_icon');
        const labelText = document.getElementById('login_label_text');
        const loginInput = document.getElementById('login_input');
        const hintIcon = document.getElementById('login_hint_icon');
        const hintText = document.getElementById('login_hint_text');

        const btnSubmit = document.getElementById('btn_submit');
        const btnSubmitText = document.getElementById('btn_submit_text');

        if (type === 'agent') {
            // Tab styling
            btnAdmin.className = 'role-pill-btn';
            btnAgent.className = 'role-pill-btn active-agent';
            hiddenType.value = 'agent';

            // Role Badge styling
            roleBadge.className = 'alert alert-success py-2 px-3 mb-3 d-flex align-items-center small';
            roleIcon.className = 'fas fa-mobile-alt fs-5 me-2 text-success';
            roleTitle.innerText = 'कार्यकर्ता लॉगिन (Mobile No. Required)';
            roleTitle.className = 'fw-bold d-block text-success';
            roleDesc.innerHTML = 'कार्यकर्ता अपने पंजीकृत <strong>10-अंकीय मोबाइल नंबर</strong> से लॉगिन करें।';

            // Input field configuration
            labelIcon.className = 'fas fa-mobile-alt text-success me-1';
            labelText.innerText = 'मोबाइल नंबर (Mobile No.)';
            loginInput.type = 'tel';
            loginInput.placeholder = '10-अंकीय मोबाइल नंबर (e.g. 9829012345)';
            loginInput.setAttribute('maxlength', '10');
            loginInput.setAttribute('pattern', '[0-9]{10}');
            loginInput.setAttribute('autocomplete', 'tel');

            // Hint
            hintIcon.className = 'fas fa-shield-alt text-success me-1';
            hintText.innerText = 'कार्यकर्ता अपने पंजीकृत 10-अंकीय मोबाइल नंबर व पासवर्ड से सीधे लॉगिन करें।';

            // Submit Button
            btnSubmit.className = 'btn btn-success btn-lg d-grid w-100 fw-bold shadow-sm';
            btnSubmit.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
            btnSubmit.style.borderColor = '#059669';
            btnSubmitText.innerText = 'कार्यकर्ता लॉगिन करें (Agent Sign In)';
        } else {
            // Admin default
            btnAdmin.className = 'role-pill-btn active-admin';
            btnAgent.className = 'role-pill-btn';
            hiddenType.value = 'admin';

            // Role Badge styling
            roleBadge.className = 'alert alert-primary py-2 px-3 mb-3 d-flex align-items-center small';
            roleIcon.className = 'fas fa-user-shield fs-5 me-2 text-primary';
            roleTitle.innerText = 'एडमिन लॉगिन (Email Required)';
            roleTitle.className = 'fw-bold d-block text-primary';
            roleDesc.innerHTML = 'एडमिन / व्यवस्थापक अपने पंजीकृत <strong>ईमेल (Email ID)</strong> से लॉगिन करें।';

            // Input field configuration
            labelIcon.className = 'fas fa-envelope text-primary me-1';
            labelText.innerText = 'ईमेल पता (Email Address)';
            loginInput.type = 'email';
            loginInput.placeholder = 'admin@shrishyam.org';
            loginInput.removeAttribute('maxlength');
            loginInput.removeAttribute('pattern');
            loginInput.setAttribute('autocomplete', 'username');

            // Hint
            hintIcon.className = 'fas fa-shield-alt text-primary me-1';
            hintText.innerText = 'एडमिन / व्यवस्थापक अपने पंजीकृत ईमेल पते व पासवर्ड से लॉगिन करें।';

            // Submit Button
            btnSubmit.className = 'btn btn-primary btn-lg d-grid w-100 fw-bold shadow-sm';
            btnSubmit.style.background = '';
            btnSubmit.style.borderColor = '';
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
