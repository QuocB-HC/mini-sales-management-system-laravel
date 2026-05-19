<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SendOTPRequest;
use App\Http\Requests\Auth\SendVerificationCodeRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Requests\Auth\VerifyOTPRequest;
use App\Mail\ResetPasswordMail;
use App\Mail\VerifyCodeMail;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('pages.auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->is_banned) {
                Auth::logout();

                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been banned. Please contact support.',
                ], 401)->onlyInput('email');
            }

            $redirectUrl = Auth::user()->isAdmin()
                        ? route('admin.dashboard')
                        : route('home');

            return response()->json([
                'success' => true,
                'message' => 'Login successful! Redirecting...',
                'redirect' => $redirectUrl,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password.',
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logout successful!');
    }

    public function showRegister()
    {
        return view('pages.auth.register');
    }

    public function sendVerificationCode(SendVerificationCodeRequest $request)
    {
        // Create random 6 numbers code
        $code = rand(100000, 999999);

        // Save into Session (lasts for 5 minutes)
        session([
            'verify_code' => $code,
            'verify_email' => $request->email,
            'verify_code_expires_at' => now()->addMinutes(5),
        ]);

        // Send verify code mail
        try {
            Mail::to($request->email)->send(new VerifyCodeMail($code));
        } catch (\Exception $e) {
            \Log::error('Mail send error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Code sent successfully!',
        ], 200);
    }

    public function showCompleteRegister()
    {
        return view('pages.auth.register-complete');
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        $sessionCode = session('verify_code');
        $sessionEmail = session('verify_email');
        $expiresAt = session('verify_code_expires_at');

        if (! $expiresAt || now()->gt($expiresAt)) {
            session()->forget(['verify_code', 'verify_email', 'verify_code_expires_at']);

            return response()->json([
                'success' => false,
                'message' => 'The verification code has expired. Please resend a new code!',
            ], 400);
        }

        if ($request->verify_email_code != $sessionCode || $request->email != $sessionEmail) {
            return response()->json([
                'success' => false,
                'message' => 'The verification code or email is incorrect.',
            ], 400);
        }

        session()->forget(['verify_code']);

        return response()->json([
            'success' => true,
            'message' => 'Verification successful!',
            'redirect' => route('register.complete'),
        ], 200);
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful!',
            'redirect' => route('home'),
        ], 200);
    }

    public function showForgetPassword()
    {
        return view('pages.auth.forget-password');
    }

    public function sendOtp(SendOTPRequest $request)
    {
        // Create random 6 numbers code
        $code = rand(100000, 999999);

        // Save into Session (lasts for 5 minutes)
        session([
            'verify_code' => $code,
            'verify_email' => $request->email,
            'verify_code_expires_at' => now()->addMinutes(5),
        ]);

        // Send verify code mail
        try {
            Mail::to($request->email)->send(new ResetPasswordMail($code));
        } catch (\Exception $e) {
            \Log::error('Mail send error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send otp code. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Code sent successfully!',
        ], 200);
    }

    public function verifyOtp(VerifyOTPRequest $request)
    {
        $sessionCode = session('verify_code');
        $sessionEmail = session('verify_email');
        $expiresAt = session('verify_code_expires_at');

        if (! $expiresAt || now()->gt($expiresAt)) {
            session()->forget(['verify_code', 'verify_email', 'verify_code_expires_at']);

            return response()->json([
                'success' => false,
                'message' => 'The verification code has expired. Please request a new one.',
            ], 400);
        }

        if ($request->otp != $sessionCode || $request->email != $sessionEmail) {
            return response()->json([
                'success' => false,
                'message' => 'The verification code is incorrect.',
            ], 400);
        }

        session()->forget(['verify_code', 'verify_code_expires_at']);
        session(['otp_verified' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Verification successful!',
        ], 200);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $email = session('verify_email');
        $otpVerified = session('otp_verified');

        if (! $otpVerified || ! $email) {
            return response()->json([
                'success' => false,
                'message' => 'The session has expired or has not been validated. Please try again from the beginning.',
            ], 403);
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            session()->forget(['otp_verified', 'verify_email', 'verify_code_expires_at']);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!',
                'redirect' => route('login'),
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not found.',
        ], 404);
    }
}
