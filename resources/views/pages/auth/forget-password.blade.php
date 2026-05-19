@extends('layouts.user', ['hideHeaderFooter' => true])

@section('title', 'Forget Password')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/forget-password.css') }}">
@endpush

@section('content')
    <div class="main-container">
        <div class="forgot-password-card">
            <h1 class="title">Forgot Password</h1>

            <div class="stepper">
                <div class="step active" id="st-1">
                    <div class="step-number">1</div>
                    <div class="step-label">Account</div>
                </div>
                <div class="step-line" id="step-line-1"></div>
                <div class="step" id="st-2">
                    <div class="step-number">2</div>
                    <div class="step-label">Security</div>
                </div>
                <div class="step-line" id="step-line-2"></div>
                <div class="step" id="st-3">
                    <div class="step-number">3</div>
                    <div class="step-label">Reset</div>
                </div>
            </div>

            <div id="step-1" class="step-content">
                <p class="instruction">Enter your email address to recover your account</p>
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" id="email" placeholder="example@gmail.com">
                </div>
                <button type="submit" class="btn-submit active" onclick="handleStep1(event)">Continue</button>
            </div>

            <div id="step-2" class="step-content" style="display: none;">
                <p class="instruction">A 6-digit code has been sent to your email</p>
                <div class="otp-container">
                    @for ($i = 0; $i < 6; $i++)
                        <input type="text" class="otp-input" maxlength="1" inputmode="numeric">
                    @endfor
                </div>
                <input type="hidden" name="verify_email_code" id="final_otp">
                <button type="submit" class="btn-submit active" onclick="handleStep2(event)">Verify Code</button>
            </div>

            <div id="step-3" class="step-content" style="display: none;">
                <p class="instruction">Set a strong new password for your account</p>
                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" id="new_password" placeholder="Min. 8 characters">
                    <span class="toggle-password" onclick="togglePassword('new_password', this)">👁️</span>
                </div>
                <div class="input-group">
                    <label>Confirm Password</label>
                    <input type="password" id="confirm_password" placeholder="Repeat password">
                    <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
                </div>
                <button type="submit" class="btn-submit active" onclick="handleStep3(event)">Update Password</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        /**
         * UI TRANSITION BETWEEN STEPS
         * Manages the visibility of content and updates the stepper indicators.
         */
        function goToStep(step) {
            // 1. Hide all step contents
            document.querySelectorAll('.step-content').forEach(el => el.style.display = 'none');

            // 2. Show the target step content
            const targetStep = document.getElementById('step-' + step);
            if (targetStep) targetStep.style.display = 'block';

            // 3. Update Stepper Circle indicators (Active state)
            document.querySelectorAll('.step').forEach((el, idx) => {
                if (idx + 1 <= step) {
                    el.classList.add('active');
                } else {
                    el.classList.remove('active');
                }
            });

            // 4. Update Stepper Connecting Lines (Active state)
            document.querySelectorAll('.step-line').forEach((line, idx) => {
                if (idx + 1 < step) {
                    line.classList.add('active');
                } else {
                    line.classList.remove('active');
                }
            });
        }

        /**
         * GLOBAL AJAX WRAPPER
         * Standardizes fetch requests and error handling for Laravel responses.
         */
        async function sendAjaxRequest(url, data) {
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (!response.ok) {
                // Throws error to be caught by the .catch() block with Laravel validation messages
                throw new Error(result.message || "Something went wrong!");
            }
            return result;
        }

        /**
         * STEP 1: REQUEST OTP VIA EMAIL
         * Validates email input and triggers the OTP sending process.
         */
        async function handleStep1(event) {
            const btn = event.currentTarget;
            const email = document.getElementById('email').value;

            if (!email) {
                return showToast("error", "Please enter your email address.");
            }

            // Set UI to loading state
            btn.disabled = true;
            btn.innerText = 'Sending...';

            try {
                const data = await sendAjaxRequest("{{ route('password.sendOtp') }}", {
                    email
                });
                showToast("success", data.message);

                // Start 60s cooldown timer
                startResendTimer(btn);
                goToStep(2);
            } catch (error) {
                showToast("error", error.message);
                btn.disabled = false;
                btn.innerText = 'Continue';
            }
        }

        /**
         * RESEND CODE COOLDOWN TIMER
         */
        function startResendTimer(btn) {
            let seconds = 60;
            const timer = setInterval(() => {
                seconds--;
                btn.innerText = `Wait ${seconds}s`;
                if (seconds <= 0) {
                    clearInterval(timer);
                    btn.disabled = false;
                    btn.innerText = "Resend Code";
                }
            }, 1000);
        }

        /**
         * STEP 2: VERIFY THE 6-DIGIT OTP
         * Collects OTP digits and verifies them against the server session.
         */
        async function handleStep2(event) {
            const btn = event.currentTarget;
            const email = document.getElementById('email').value;
            let otp = "";
            document.querySelectorAll('.otp-input').forEach(input => otp += input.value);

            if (otp.length < 6) {
                return showToast("error", "Please enter the full 6-digit code.");
            }

            btn.disabled = true;
            btn.innerText = 'Verifying...';

            try {
                const data = await sendAjaxRequest("{{ route('password.verifyOtp') }}", {
                    otp,
                    email
                });
                showToast("success", data.message);
                goToStep(3);
            } catch (error) {
                showToast("error", error.message);
                btn.disabled = false;
                btn.innerText = 'Verify Code';
            }
        }

        /**
         * STEP 3: SUBMIT NEW PASSWORD
         * Validates new password match and updates the user account.
         */
        async function handleStep3(event) {
            const btn = event.currentTarget;
            const password = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;
            const email = document.getElementById('email').value;

            // Basic client-side validation
            if (password.length < 8) {
                return showToast("error", "Password must be at least 8 characters.");
            }

            if (password !== confirm) {
                return showToast("error", "Passwords do not match.");
            }

            btn.disabled = true;
            btn.innerText = 'Updating...';

            try {
                const data = await sendAjaxRequest("{{ route('password.update') }}", {
                    password: password,
                    password_confirmation: confirm,
                    email: email
                });

                showToast("success", data.message);

                // Redirect to login page after a short delay
                setTimeout(() => {
                    window.location.href = "{{ route('login') }}";
                }, 1500);
            } catch (error) {
                showToast("error", error.message);
                btn.disabled = false;
                btn.innerText = 'Update Password';

                // If the error is related to session expiration, reset to step 1
                if (error.message.includes('session') || error.message.includes('expired')) {
                    setTimeout(() => goToStep(1), 2000);
                }
            }
        }

        /**
         * OTP INPUT BEHAVIORS
         * Handles auto-focus, backspace navigation, and pasting entire codes.
         */
        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, index) => {
            // Handle number input and auto-focus next
            input.addEventListener('input', (e) => {
                if (e.target.value.length > 0 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            // Handle backspace navigation and Enter to submit
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                    otpInputs[index - 1].focus();
                }
                if (e.key === 'Enter' && index === otpInputs.length - 1) {
                    const step2Btn = document.querySelector('#step-2 .btn-submit');
                    if (step2Btn) step2Btn.click();
                }
            });

            // Handle pasting 6-digit codes
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                if (pastedData.length > 0) {
                    pastedData.split('').forEach((char, i) => {
                        if (otpInputs[i]) otpInputs[i].value = char;
                    });
                    otpInputs[Math.min(pastedData.length - 1, 5)].focus();
                }
            });
        });

        /**
         * TOGGLE PASSWORD VISIBILITY
         */
        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                iconElement.innerText = "🙈";
            } else {
                input.type = "password";
                iconElement.innerText = "👁️";
            }
        }
    </script>
@endpush
