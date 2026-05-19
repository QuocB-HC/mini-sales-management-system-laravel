@extends('layouts.user', ['hideHeaderFooter' => true])

@section('title', 'Register')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/register.css') }}">
@endpush

@section('content')
    <div class="register-container">
        <div class="register-box">
            <h2>Create Account</h2>
            <p>Join our store today</p>

            <form action="{{ route('verify.email') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-with-button">
                        <input type="email" name="email" value="{{ old('email') }}">
                        <button type="button" class="send-code-btn" id="btnSendCode">Send code</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Code</label>
                    <div class="otp-container">
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                    </div>
                    <input type="hidden" name="verify_email_code" id="final_otp">
                </div>

                <button type="submit" class="btn-register active">Sign Up</button>
            </form>

            <div class="register-footer">
                <p>Already have an account? <a href="{{ route('login') }}">Sign In</a></p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('btnSendCode').addEventListener('click', function() {
            let btn = this;
            let emailInput = document.querySelector('input[name="email"]');
            let email = emailInput.value;

            btn.disabled = true;
            let originalText = btn.innerText;
            btn.innerText = 'Sending...';

            fetch("{{ route('send.code') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        email: email
                    })
                })
                .then(async response => {
                    const result = await response.json();
                    if (!response.ok) {
                        throw new Error(result.message || "Failed to send code");
                    }
                    return result;
                })
                .then(data => {
                    showToast("success", data.message);

                    let seconds = 60;
                    let timer = setInterval(function() {
                        seconds--;
                        btn.innerText = `Wait ${seconds}s`;

                        if (seconds <= 0) {
                            clearInterval(timer);
                            btn.disabled = false;
                            btn.innerText = 'Resend code';
                        }
                    }, 1000);
                })
                .catch(error => {
                    showToast("error", error.message);

                    btn.disabled = false;
                    btn.innerText = 'Send code';
                });
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();

            let btn = this.querySelector('.btn-register');
            btn.disabled = true;
            btn.innerText = 'Signing up...';

            let formData = new FormData(this);
            let data = Object.fromEntries(formData.entries());

            fetch(this.action, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                })
                .then(async response => {
                    const result = await response.json();
                    if (!response.ok) {
                        throw new Error(result.message || "Something went wrong");
                    }
                    return result;
                })
                .then(result => {
                    handleAjaxResponse(result);

                    if (result.redirect) {
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 1000);
                    }
                })
                .catch(error => {
                    showToast("error", error.message);
                    btn.disabled = false;
                    btn.innerText = 'Sign Up';
                });
        });

        const inputs = document.querySelectorAll('.otp-input');
        const finalInput = document.getElementById('final_otp');

        inputs.forEach((input, index) => {
            // 1. Enter number
            input.addEventListener('input', (e) => {
                if (e.target.value.length > 0 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateFinalCode();
            });

            // 2. Enter Backspace to delete
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            // 3. Handle situations where the user pastes entire codes
            input.addEventListener('paste', (e) => {
                const data = e.clipboardData.getData('text').slice(0, 6);
                if (data.length === 6) {
                    data.split('').forEach((char, i) => {
                        inputs[i].value = char;
                    });
                    updateFinalCode();
                }
            });
        });

        function updateFinalCode() {
            let code = "";
            inputs.forEach(input => code += input.value);
            finalInput.value = code; // Assign code to a hidden input to submit the form
        }
    </script>
@endpush
