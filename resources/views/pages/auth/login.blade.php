@extends('layouts.user', ['hideHeaderFooter' => true])

@section('title', 'Login')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
@endpush

@section('content')
    <div class="login-container">
        <div class="login-box">
            <h2>Welcome Back</h2>
            <p>Please enter your details to sign in</p>

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div style="position: relative">
                        <input type="password" name="password" id="password">
                        <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
                    </div>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <div class="login-footer">
                <p>Don't have an account? <a href={{ route('register') }}>Create one</a></p>
            </div>

            <div class="login-footer">
                <p>Forget password? <a href={{ route('forget.password') }}>Reset password</a></p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();

            let btn = this.querySelector('.btn-login');
            btn.disabled = true;
            btn.innerText = 'Signing in...';

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
                        throw new Error(result.message || "Login failed");
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
                    btn.innerText = 'Sign In';
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
