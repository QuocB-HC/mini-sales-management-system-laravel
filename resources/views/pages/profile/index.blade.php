@extends('layouts.user')

@section('title', 'My Profile')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/profile.css') }}">
@endpush

@section('content')
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=128' }}"
                        alt="User Avatar">
                    
                </div>
                <h2>{{ $user->name }}</h2>
                <span class="badge">Member since {{ $user->created_at->format('M Y') }}</span>
            </div>

            <div class="profile-body">
                <div class="info-group">
                    <label><i class="fa-regular fa-envelope"></i> Email</label>
                    <p>{{ $user->email }}</p>
                </div>

                <div class="info-group">
                    <label><i class="fa-regular fa-user"></i> Full Name</label>
                    <p>{{ $user->name }}</p>
                </div>

                <div class="info-group">
                    <label><i class="fa-solid fa-phone"></i> Phone Number</label>
                    <p>{{ $user->phone ?? 'Chưa cập nhật số điện thoại' }}</p>
                </div>

                <div class="info-group">
                    <label><i class="fa-solid fa-location-dot"></i> Address</label>
                    <p>{{ $user->address ?? 'Chưa có địa chỉ giao hàng' }}</p>
                </div>

                <div class="profile-actions">
                    <a href="{{ route('profile.edit') }}" class="btn-edit">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>
@endsection
