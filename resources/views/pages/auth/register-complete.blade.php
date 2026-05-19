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

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ session('verify_email') }}">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div style="position: relative">
                        <input type="password" name="password" id="password">
                        <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <div style="position: relative">
                        <input type="password" name="password_confirmation" id="password_confirmation">
                        <span class="toggle-password" onclick="togglePassword('password_confirmation', this)">👁️</span>
                    </div>
                </div>

                <button type="submit" class="btn-register active">Complete registration</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();

            let btn = this.querySelector('.btn-register');
            btn.disabled = true;
            btn.innerText = 'Signing Up...';

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

        function togglePassword(inputId, iconElement) {
            const passwordInput = document.getElementById(inputId);

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                iconElement.innerText = "🙈";
            } else {
                passwordInput.type = "password";
                iconElement.innerText = "👁️";
            }
        }
    </script>
@endpush
